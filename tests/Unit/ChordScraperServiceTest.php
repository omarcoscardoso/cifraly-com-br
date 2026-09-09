<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\ChordScraperService;
use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
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
        $this->assertStringContainsString('[Intro] Am  F  C  G', $result['chordpro_content']);
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
}
