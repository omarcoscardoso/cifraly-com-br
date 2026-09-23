<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\ChordScraperService;
use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChordScraperServiceTest extends TestCase
{
    private ChordScraperService $scraper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scraper = new ChordScraperService(
            new ChordToChordProConverter(new ChordTransposerService),
            new ChordTransposerService
        );
    }

    public function test_parses_cifraclub_html_correctly(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Lugar Secreto - Gabriela Rocha - Cifra Club</title>
</head>
<body>
    <span id="cifra_tom">Tom: <a>C</a></span>
    <pre>
[Intro] F  Am  G

C             G
Tu és tudo o que eu mais quero
    </pre>
</body>
</html>
HTML;

        $result = $this->scraper->parseHtmlContent($html, 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/');

        $this->assertSame('Lugar Secreto', $result['title']);
        $this->assertSame('Gabriela Rocha', $result['artist']);
        $this->assertSame('C', $result['original_key']);
        $this->assertStringContainsString('[Intro] F  Am  G', $result['chordpro_content']);
        $this->assertStringContainsString("C             G\nTu és tudo o que eu mais quero", $result['chordpro_content']);
    }

    public function test_parses_cifraclub_modern_bento_layout_with_minor_key(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Lugar Secreto - Gabriela Rocha - Cifra Club</title>
</head>
<body>
    <div class="bentoCardContent" id="key">
        <p>Tom</p>
        <button aria-label="Diminuir tom"></button>
        <span><p>Am</p></span>
        <button aria-label="Aumentar tom"></button>
    </div>
    <script>
        self.__next_f.push([1,"\"timeSignature\":[\"1\",\"x\",\"x\",\"x\",\"2\",\"x\",\"x\",\"x\",\"3\",\"x\",\"x\",\"x\",\"4\",\"x\",\"x\",\"x\"],\"bpm\":70"]);
    </script>
    <pre>
[Intro] Am  F  C  G

Am            F
Tu és tudo o que eu mais quero
    </pre>
</body>
</html>
HTML;

        $result = $this->scraper->parseHtmlContent($html, 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/');

        $this->assertSame('Lugar Secreto', $result['title']);
        $this->assertSame('Gabriela Rocha', $result['artist']);
        $this->assertSame('Am', $result['original_key']);
        $this->assertSame(70, $result['bpm']);
        $this->assertSame('4/4', $result['time_signature']);
        $this->assertStringContainsString('[Intro] Am  F  C  G', $result['chordpro_content']);
    }

    public function test_parses_cifraclub_with_capo_fret(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Oceanos - Ana Nóbrega - Cifra Club</title>
</head>
<body>
    <span id="cifra_tom">Tom: <a>D</a></span>
    <span id="cifra_capo">Capotraste na <b>2ª</b> casa</span>
    <script>
        self.__next_f.push([1,"\"capo\":2,\"bpm\":64,\"timeSignature\":[\"1\",\"x\",\"x\",\"x\",\"2\",\"x\",\"x\",\"x\",\"3\",\"x\",\"x\",\"x\",\"4\",\"x\",\"x\",\"x\"]"]);
    </script>
    <pre>
[Intro] Bm  A/C#  D  A  G

Bm            A/C#
Tua voz me chama sobre as águas
    </pre>
</body>
</html>
HTML;

        $result = $this->scraper->parseHtmlContent($html, 'https://www.cifraclub.com.br/ana-nobrega/oceanos/');

        $this->assertSame('Oceanos', $result['title']);
        $this->assertSame('Ana Nóbrega', $result['artist']);
        $this->assertSame('D', $result['original_key']);
        $this->assertSame(64, $result['bpm']);
        $this->assertSame('4/4', $result['time_signature']);
        $this->assertSame(2, $result['capo_fret']);
    }

    public function test_parses_cifras_html_correctly(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Porque Ele Vive - Harpa Cristã - Cifras.com.br</title>
</head>
<body>
    <span class="tom">Tom: G</span>
    <div id="cifra_conteudo">
[Intro] G  C  G  D

G             C
Deus enviou Seu Filho amado
    </div>
</body>
</html>
HTML;

        $result = $this->scraper->parseHtmlContent($html, 'https://www.cifras.com.br/harpa-crista/porque-ele-vive/');

        $this->assertSame('Porque Ele Vive', $result['title']);
        $this->assertSame('Harpa Cristã', $result['artist']);
        $this->assertSame('G', $result['original_key']);
        $this->assertStringContainsString('[Intro] G  C  G  D', $result['chordpro_content']);
        $this->assertStringContainsString("G             C\nDeus enviou Seu Filho amado", $result['chordpro_content']);
    }

    public function test_import_from_url_fetches_and_extracts_data(): void
    {
        Http::fake([
            'https://www.cifraclub.com.br/aline-barros/consagracao/*' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Consagração - Aline Barros - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>D</a></span>
    <pre>
D             A
Ao Rei dos reis consagro
    </pre>
</body>
</html>
HTML, 200),
        ]);

        $result = $this->scraper->importFromUrl('https://www.cifraclub.com.br/aline-barros/consagracao/');

        $this->assertSame('Consagração', $result['title']);
        $this->assertSame('Aline Barros', $result['artist']);
        $this->assertSame('D', $result['original_key']);
        $this->assertStringContainsString("D             A\nAo Rei dos reis consagro", $result['chordpro_content']);
    }

    public function test_search_song_finds_candidates(): void
    {
        Http::fake([
            'https://solr.sscdn.co/cifraclub/ac/*' => Http::response([
                'response' => [
                    'docs' => [
                        [
                            't' => '2',
                            'art' => 'Gabriela Rocha',
                            'dns' => 'gabriela-rocha',
                            'txt' => 'Lugar Secreto',
                            'url' => 'lugar-secreto',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $results = $this->scraper->searchSong('gabriela rocha lugar secreto');

        $this->assertNotEmpty($results);
        $this->assertSame('Lugar Secreto', $results[0]['title']);
        $this->assertSame('Gabriela Rocha', $results[0]['artist']);
        $this->assertSame('https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/', $results[0]['url']);
        $this->assertSame('Cifra Club', $results[0]['source']);
    }

    public function test_search_song_returns_direct_url_when_query_is_url(): void
    {
        $directUrl = 'https://www.cifraclub.com.br/morada/e-tudo-sobre-voce/';
        $results = $this->scraper->searchSong($directUrl);

        $this->assertCount(1, $results);
        $this->assertSame('Cifra Direta', $results[0]['title']);
        $this->assertSame($directUrl, $results[0]['url']);
        $this->assertSame('Cifra Club', $results[0]['source']);
    }

    public function test_search_song_falls_back_to_candidate_slugs_when_solr_fails(): void
    {
        Http::fake([
            'https://solr.sscdn.co/cifraclub/ac/*' => Http::response(null, 500),
            'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/' => Http::response(<<<'HTML'
<title>Lugar Secreto - Gabriela Rocha - Cifra Club</title>
HTML, 200),
        ]);

        $results = $this->scraper->searchSong('gabriela rocha lugar secreto');

        $this->assertNotEmpty($results);
        $this->assertSame('Lugar Secreto', $results[0]['title']);
        $this->assertSame('Gabriela Rocha', $results[0]['artist']);
        $this->assertSame('Cifra Club', $results[0]['source']);
    }

    public function test_import_from_url_rejects_unsafe_ssrf_urls(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->scraper->importFromUrl('http://localhost/admin');
    }

    public function test_import_from_url_rejects_loopback_ip(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->scraper->importFromUrl('http://127.0.0.1:8000/secret');
    }

    public function test_import_from_url_rejects_metadata_ip(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->scraper->importFromUrl('http://169.254.169.254/latest/meta-data');
    }

    public function test_import_from_url_sends_waf_bypass_headers_and_options(): void
    {
        Http::fake([
            'https://www.cifraclub.com.br/test-artist/test-song/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Test Song - Test Artist - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>C</a></span>
    <pre>C G</pre>
</body>
</html>
HTML, 200),
        ]);

        $this->scraper->importFromUrl('https://www.cifraclub.com.br/test-artist/test-song/');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://www.cifraclub.com.br/test-artist/test-song/'
                && $request->hasHeader('sec-ch-ua')
                && $request->hasHeader('upgrade-insecure-requests')
                && $request->hasHeader('Accept-Encoding')
                && str_contains($request->header('User-Agent')[0], 'Chrome/133');
        });
    }

    public function test_can_fallback_to_edge_reader_when_direct_scraping_fails_with_403(): void
    {
        Http::fake([
            'https://r.jina.ai/*' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Nada Mais - Florianópolis House Of Prayer - Cifra Club</title></head>
<body>
    <div class="ebNp"><div class="IERZz"><span>Tom<!-- -->: </span> <button type="button" class="eVroG" data-anchor="--chord-tone">Em</button></div></div>
    <p>68 bpm</p>
    <pre>
[Intro] Em7  G  D  D4

[Primeira Parte]

                   Em7
Envolto em Tua presença
G                D
  Aos Teus pés é onde eu quero estar
    </pre>
</body>
</html>
HTML, 200),
            'https://www.cifraclub.com.br/florianopolis-house-of-prayer/nada-mais/' => Http::response('Access Denied', 403),
        ]);

        $result = $this->scraper->importFromUrl('https://www.cifraclub.com.br/florianopolis-house-of-prayer/nada-mais/');

        $this->assertSame('Nada Mais', $result['title']);
        $this->assertSame('Florianópolis House Of Prayer', $result['artist']);
        $this->assertSame('Em', $result['original_key']);
        $this->assertSame(68, $result['bpm']);
        $this->assertStringContainsString('Envolto em Tua presença', $result['chordpro_content']);
    }
}
