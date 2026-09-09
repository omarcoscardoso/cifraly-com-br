<?php

declare(strict_types=1);

namespace App\Services\Music\Scrapers\Drivers;

use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
use App\Services\Music\DTOs\ScrapedChordData;
use App\Services\Music\Scrapers\Contracts\ChordScraperDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CifraClubDriver implements ChordScraperDriverInterface
{
    public function __construct(
        protected ChordToChordProConverter $converter,
        protected ChordTransposerService $transposer,
    ) {}

    public function supportsUrl(string $url): bool
    {
        return str_contains(strtolower($url), 'cifraclub.com.br');
    }

    public function search(string $query): array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return [];
        }

        $results = [];

        // 1. Cifra Club Solr API
        try {
            $solrUrl = 'https://solr.sscdn.co/cifraclub/ac/?q='.urlencode($trimmed);
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(5)
                ->get($solrUrl);

            if ($response->successful()) {
                $docs = $response->json('response.docs') ?? [];

                foreach ($docs as $doc) {
                    $artistSlug = (string) ($doc['dns'] ?? '');
                    $songSlug = (string) ($doc['url'] ?? '');
                    $songTitle = (string) ($doc['txt'] ?? '');
                    $artistName = (string) ($doc['art'] ?? '');

                    if ($artistSlug !== '' && $songSlug !== '' && $songTitle !== '') {
                        $canonicalUrl = "https://www.cifraclub.com.br/{$artistSlug}/{$songSlug}/";

                        if (! isset($results[$canonicalUrl])) {
                            $results[$canonicalUrl] = [
                                'title' => $songTitle,
                                'artist' => $artistName ?: ucwords(str_replace('-', ' ', $artistSlug)),
                                'url' => $canonicalUrl,
                                'source' => 'Cifra Club',
                            ];
                        }

                        if (count($results) >= 10) {
                            break;
                        }
                    }
                }
            }
        } catch (Throwable) {
            // Silently fallback to candidate matching
        }

        // 2. Fallback candidate slug matching
        if (count($results) < 5) {
            $words = preg_split('/\s+/', strtolower(Str::ascii($trimmed)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if (count($words) >= 2) {
                $candidatePairs = [];

                for ($i = 1; $i < count($words); $i++) {
                    $artistSlug = implode('-', array_slice($words, 0, $i));
                    $songSlug = implode('-', array_slice($words, $i));
                    $candidatePairs[] = [$artistSlug, $songSlug];

                    $songSlugRev = implode('-', array_slice($words, 0, $i));
                    $artistSlugRev = implode('-', array_slice($words, $i));
                    $candidatePairs[] = [$artistSlugRev, $songSlugRev];
                }

                foreach ($candidatePairs as [$artist, $song]) {
                    $testUrl = "https://www.cifraclub.com.br/{$artist}/{$song}/";

                    if (isset($results[$testUrl])) {
                        continue;
                    }

                    try {
                        $response = Http::withHeaders($this->getHeaders())
                            ->timeout(3)
                            ->get($testUrl);

                        if ($response->successful()) {
                            $html = $response->body();
                            $metadata = $this->extractMetadata($html, $testUrl);

                            $results[$testUrl] = [
                                'title' => $metadata['title'] ?: ucwords(str_replace('-', ' ', $song)),
                                'artist' => $metadata['artist'] ?: ucwords(str_replace('-', ' ', $artist)),
                                'url' => $testUrl,
                                'source' => 'Cifra Club',
                            ];

                            if (count($results) >= 10) {
                                break;
                            }
                        }
                    } catch (Throwable) {
                        // Ignore
                    }
                }
            }
        }

        return array_values($results);
    }

    public function scrape(string $url): ScrapedChordData
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(12)
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Falha ao carregar cifra do Cifra Club (HTTP {$response->status()}).");
        }

        return $this->parseHtml($response->body(), $url);
    }

    public function parseHtml(string $html, string $url = ''): ScrapedChordData
    {
        $metadata = $this->extractMetadata($html, $url);
        $rawChords = '';

        if (preg_match('/<pre[^>]*>(.*?)<\/pre>/si', $html, $preMatch)) {
            $rawChords = html_entity_decode(strip_tags($preMatch[1]));
        }

        $key = $metadata['original_key'];
        if (blank($key)) {
            $key = $this->detectKeyFromChords($rawChords);
        }

        $chordPro = $this->converter->convert($rawChords);

        $capoFret = $metadata['capo_fret'] ?? null;
        if ($capoFret === null && preg_match('/(?:capotraste|capo)\s*(?:na|:)?\s*(\d+)[ªº°a]?\s*(?:casa)?/i', $rawChords, $capoMatch)) {
            $parsedCapo = (int) $capoMatch[1];
            if ($parsedCapo >= 1 && $parsedCapo <= 12) {
                $capoFret = $parsedCapo;
            }
        }

        return new ScrapedChordData(
            title: $metadata['title'] ?: 'Música Sem Título',
            artist: $metadata['artist'] ?: 'Artista Desconhecido',
            originalKey: $key ?: 'C',
            rawChords: $rawChords,
            chordProContent: $chordPro,
            bpm: $metadata['bpm'] ?? null,
            timeSignature: $metadata['time_signature'] ?? null,
            capoFret: $capoFret,
            sourceUrl: $url ?: null,
        );
    }

    /**
     * @return array{title: string, artist: string, original_key: string, bpm: ?int, time_signature: ?string, capo_fret: ?int}
     */
    protected function extractMetadata(string $html, string $url = ''): array
    {
        $title = '';
        $artist = '';
        $key = '';
        $bpm = null;
        $timeSignature = null;

        if (preg_match('/<title>(.*?)<\/title>/si', $html, $titleMatch)) {
            $titleTag = html_entity_decode(trim($titleMatch[1]));
            $parts = explode('-', $titleTag);
            if (count($parts) >= 2) {
                $title = trim($parts[0]);
                $artist = trim($parts[1]);
            }
        }

        if (blank($title) && preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $html, $h1Match)) {
            $title = html_entity_decode(trim(strip_tags($h1Match[1])));
        }

        if (blank($artist) && preg_match('/<h2[^>]*>(.*?)<\/h2>/si', $html, $h2Match)) {
            $artist = html_entity_decode(trim(strip_tags($h2Match[1])));
        }

        // 1. Layout Bento (novo) do Cifra Club: elemento com id="key"
        if (preg_match('/id=["\']key["\'][^>]*>.*?<button[^>]*aria-label=["\'](?:Diminuir tom|Aumentar tom)["\'][^>]*>.*?<p[^>]*>([A-G][#b♭♯]?(?:m|maj|min)?)/si', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        } elseif (preg_match('/id=["\']key["\'][^>]*>.*?<p[^>]*>([A-G][#b♭♯]?(?:m|maj|min)?)(?:<\/p>|\s|<)/si', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        }
        // 2. Dados embutidos em JSON/scripts SSR do Next.js
        elseif (preg_match('/"(?:keyShape|key|tom)":\s*"([A-G][#b♭♯]?(?:m|maj|min)?)"/i', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        }
        // 3. Layout clássico / alternativo do Cifra Club
        elseif (preg_match('/(?:id=["\']cifra_tom["\']|class=["\'][^"\']*tom[^"\']*["\'])[^>]*>(.*?)<\/(?:span|div|a|button)>/si', $html, $tomContainer)) {
            $cleaned = strip_tags($tomContainer[1]);
            if (preg_match('/([A-G][#b♭♯]?(?:m|maj|min)?)/', $cleaned, $tomMatch)) {
                $key = $this->transposer->normalizeKey($tomMatch[1]);
            }
        } elseif (preg_match('/Tom:\s*(?:<[^>]+>)*\s*([A-G][#b♭♯]?(?:m|maj|min)?)/si', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        }

        // 4. BPM (Andamento)
        if (preg_match('/(?:\\\\\"bpm\\\\\"|\"bpm\"):\s*(\d+)/i', $html, $bpmMatch)) {
            $parsedBpm = (int) $bpmMatch[1];
            if ($parsedBpm >= 30 && $parsedBpm <= 300) {
                $bpm = $parsedBpm;
            }
        } elseif (preg_match('/(?:bpm|tempo|andamento):\s*(\d+)/i', $html, $bpmMatch)) {
            $parsedBpm = (int) $bpmMatch[1];
            if ($parsedBpm >= 30 && $parsedBpm <= 300) {
                $bpm = $parsedBpm;
            }
        }

        // 5. Fórmula de Compasso (Time Signature)
        if (preg_match('/(?:\\\\\"timeSignature\\\\\"|\"timeSignature\"):\s*\[([^\]]+)\]/i', $html, $tsMatch)) {
            preg_match_all('/[0-9]+/', $tsMatch[1], $beats);
            if (! empty($beats[0])) {
                $maxBeat = max(array_map('intval', $beats[0]));
                $timeSignature = match ($maxBeat) {
                    4 => '4/4',
                    3 => '3/4',
                    2 => '2/4',
                    6 => '6/8',
                    12 => '12/8',
                    default => null,
                };
            }
        }

        if (! $timeSignature && preg_match('/(?:compasso|fórmula de compasso|meter|time signature):\s*(\d+\/\d+)/i', $html, $tsMatch)) {
            $candidate = trim($tsMatch[1]);
            if (in_array($candidate, ['4/4', '3/4', '2/4', '6/8', '12/8'], true)) {
                $timeSignature = $candidate;
            }
        }

        // 6. Capotraste
        $capoFret = null;
        if (preg_match('/(?:\\\\\"capo\\\\\"|\"capo\"):\s*(\d+)/i', $html, $capoMatch)) {
            $parsedCapo = (int) $capoMatch[1];
            if ($parsedCapo >= 1 && $parsedCapo <= 12) {
                $capoFret = $parsedCapo;
            }
        } elseif (preg_match('/(?:id=["\']cifra_capo["\']|id=["\']capo["\'])[^>]*>.*?(\d+)[ªº°a]?\s*casa/si', $html, $capoMatch)) {
            $parsedCapo = (int) $capoMatch[1];
            if ($parsedCapo >= 1 && $parsedCapo <= 12) {
                $capoFret = $parsedCapo;
            }
        } elseif (preg_match('/(?:capotraste|capo)\s*(?:na|:)?\s*(\d+)[ªº°a]?\s*(?:casa)?/i', $html, $capoMatch)) {
            $parsedCapo = (int) $capoMatch[1];
            if ($parsedCapo >= 1 && $parsedCapo <= 12) {
                $capoFret = $parsedCapo;
            }
        }

        return [
            'title' => $title,
            'artist' => $artist,
            'original_key' => $key,
            'bpm' => $bpm,
            'time_signature' => $timeSignature,
            'capo_fret' => $capoFret,
        ];
    }

    protected function detectKeyFromChords(string $rawChords): string
    {
        if (preg_match('/Tom:\s*([A-G][#b♭♯]?(?:m|maj|min)?)/i', $rawChords, $match)) {
            return $this->transposer->normalizeKey($match[1]);
        }

        if (preg_match_all('/\b([A-G][#b♭♯]?(?:m|maj|min)?)(?:\/|\b)/', $rawChords, $matches)) {
            foreach ($matches[1] as $candidate) {
                if ($this->transposer->isValidChord($candidate)) {
                    return $this->transposer->normalizeKey($candidate);
                }
            }
        }

        return 'C';
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ];
    }
}
