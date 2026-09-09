<?php

declare(strict_types=1);

namespace App\Services\Music\Scrapers\Drivers;

use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
use App\Services\Music\DTOs\ScrapedChordData;
use App\Services\Music\Scrapers\Contracts\ChordScraperDriverInterface;
use Illuminate\Support\Facades\Http;

class GenericHtmlDriver implements ChordScraperDriverInterface
{
    public function __construct(
        protected ChordToChordProConverter $converter,
        protected ChordTransposerService $transposer,
    ) {}

    public function supportsUrl(string $url): bool
    {
        return true; // Generic fallback
    }

    public function search(string $query): array
    {
        return [];
    }

    public function scrape(string $url): ScrapedChordData
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->timeout(12)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Falha ao carregar a página (HTTP {$response->status()}).");
        }

        return $this->parseHtml($response->body(), $url);
    }

    public function parseHtml(string $html, string $url = ''): ScrapedChordData
    {
        $rawChords = '';

        if (preg_match('/<div[^>]*id=["\']cifra_conteudo["\'][^>]*>(.*?)<\/div>/si', $html, $divMatch)) {
            $rawChords = html_entity_decode(strip_tags($divMatch[1]));
        } elseif (preg_match('/<pre[^>]*>(.*?)<\/pre>/si', $html, $preMatch)) {
            $rawChords = html_entity_decode(strip_tags($preMatch[1]));
        }

        $title = 'Música Importada';
        $artist = 'Artista';

        if (preg_match('/<title>(.*?)<\/title>/si', $html, $m)) {
            $parts = explode('-', html_entity_decode(trim($m[1])));
            if (count($parts) >= 2) {
                $title = trim($parts[0]);
                $artist = trim($parts[1]);
            }
        }

        $key = 'C';
        if (preg_match('/id=["\']key["\'][^>]*>.*?<button[^>]*aria-label=["\'](?:Diminuir tom|Aumentar tom)["\'][^>]*>.*?<p[^>]*>([A-G][#b♭♯]?(?:m|maj|min)?)/si', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        } elseif (preg_match('/id=["\']key["\'][^>]*>.*?<p[^>]*>([A-G][#b♭♯]?(?:m|maj|min)?)(?:<\/p>|\s|<)/si', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        } elseif (preg_match('/"(?:keyShape|key|tom)":\s*"([A-G][#b♭♯]?(?:m|maj|min)?)"/i', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        } elseif (preg_match('/(?:id=["\']cifra_tom["\']|class=["\'][^"\']*tom[^"\']*["\'])[^>]*>(.*?)<\/(?:span|div|a|button)>/si', $html, $tomContainer)) {
            $cleaned = strip_tags($tomContainer[1]);
            if (preg_match('/([A-G][#b♭♯]?(?:m|maj|min)?)/', $cleaned, $tomMatch)) {
                $key = $this->transposer->normalizeKey($tomMatch[1]);
            }
        } elseif (preg_match('/Tom:\s*(?:<[^>]+>)*\s*([A-G][#b♭♯]?(?:m|maj|min)?)/si', $html, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        } elseif (preg_match('/Tom:\s*([A-G][#b♭♯]?(?:m|maj|min)?)/i', $rawChords, $tomMatch)) {
            $key = $this->transposer->normalizeKey($tomMatch[1]);
        }

        $bpm = null;
        if (preg_match('/(?:\\\\\"bpm\\\\\"|\"bpm\"):\s*(\d+)/i', $html, $bpmMatch)) {
            $parsedBpm = (int) $bpmMatch[1];
            if ($parsedBpm >= 30 && $parsedBpm <= 300) {
                $bpm = $parsedBpm;
            }
        } elseif (preg_match('/(?:bpm|tempo|andamento):\s*(\d+)/i', $html.' '.$rawChords, $bpmMatch)) {
            $parsedBpm = (int) $bpmMatch[1];
            if ($parsedBpm >= 30 && $parsedBpm <= 300) {
                $bpm = $parsedBpm;
            }
        }

        $timeSignature = null;
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

        if (! $timeSignature && preg_match('/(?:compasso|fórmula de compasso|meter|time signature):\s*(\d+\/\d+)/i', $html.' '.$rawChords, $tsMatch)) {
            $candidate = trim($tsMatch[1]);
            if (in_array($candidate, ['4/4', '3/4', '2/4', '6/8', '12/8'], true)) {
                $timeSignature = $candidate;
            }
        }

        $chordPro = $this->converter->convert($rawChords);

        return new ScrapedChordData(
            title: $title,
            artist: $artist,
            originalKey: $key,
            rawChords: $rawChords,
            chordProContent: $chordPro,
            bpm: $bpm,
            timeSignature: $timeSignature,
            sourceUrl: $url ?: null,
        );
    }
}
