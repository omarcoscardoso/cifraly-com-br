<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
use App\Services\Music\DTOs\ScrapedChordData;
use App\Services\Music\Scrapers\ChordScraperManager;
use App\Services\Music\Scrapers\Contracts\ChordScraperDriverInterface;
use App\Services\Music\Scrapers\Drivers\CifraClubDriver;
use App\Services\Music\Scrapers\Drivers\GenericHtmlDriver;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChordScraperManagerTest extends TestCase
{
    private ChordScraperManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $transposer = new ChordTransposerService;
        $converter = new ChordToChordProConverter($transposer);

        $this->manager = new ChordScraperManager(
            new CifraClubDriver($converter, $transposer),
            new GenericHtmlDriver($converter, $transposer)
        );
    }

    public function test_selects_cifraclub_driver_for_cifraclub_url(): void
    {
        $driver = $this->manager->getDriverForUrl('https://www.cifraclub.com.br/aline-barros/ressuscita-me/');
        $this->assertInstanceOf(CifraClubDriver::class, $driver);
    }

    public function test_selects_generic_driver_for_other_url(): void
    {
        $driver = $this->manager->getDriverForUrl('https://www.cifras.com.br/artistas/musica/');
        $this->assertInstanceOf(GenericHtmlDriver::class, $driver);
    }

    public function test_can_register_custom_driver_dynamically_ocp(): void
    {
        $mockDriver = new class implements ChordScraperDriverInterface
        {
            public function supportsUrl(string $url): bool
            {
                return str_contains($url, 'custom-chord-site.com');
            }

            public function search(string $query): array
            {
                return [];
            }

            public function scrape(string $url): ScrapedChordData
            {
                return new ScrapedChordData('Mock Title', 'Mock Artist', 'G', 'G C D', 'G C D');
            }

            public function parseHtml(string $html, string $url = ''): ScrapedChordData
            {
                return new ScrapedChordData('Mock Title', 'Mock Artist', 'G', 'G C D', 'G C D');
            }
        };

        $this->manager->registerDriver($mockDriver);

        $driver = $this->manager->getDriverForUrl('https://custom-chord-site.com/song-1');
        $this->assertSame($mockDriver, $driver);

        $scraped = $this->manager->scrape('https://custom-chord-site.com/song-1');
        $this->assertSame('Mock Title', $scraped->title);
        $this->assertSame('Mock Artist', $scraped->artist);
        $this->assertSame('G', $scraped->originalKey);
    }

    public function test_search_caches_results_for_one_hour(): void
    {
        Cache::flush();

        $callCount = 0;
        $mockDriver = new class($callCount) implements ChordScraperDriverInterface
        {
            public function __construct(public int &$callCount) {}

            public function supportsUrl(string $url): bool
            {
                return true;
            }

            public function search(string $query): array
            {
                $this->callCount++;

                return [
                    [
                        'title' => 'Search Result',
                        'artist' => 'Artist',
                        'url' => 'https://example.com/chords',
                        'source' => 'Mock',
                    ],
                ];
            }

            public function scrape(string $url): ScrapedChordData
            {
                return new ScrapedChordData('Title', 'Artist', 'C', 'C', 'C');
            }

            public function parseHtml(string $html, string $url = ''): ScrapedChordData
            {
                return new ScrapedChordData('Title', 'Artist', 'C', 'C', 'C');
            }
        };

        $this->manager->registerDriver($mockDriver);

        // First call executes driver search
        $results1 = $this->manager->search('Galhos Secos');
        $this->assertCount(1, $results1);
        $this->assertSame(1, $callCount);

        // Second call with same query should hit cache
        $results2 = $this->manager->search('Galhos Secos');
        $this->assertCount(1, $results2);
        $this->assertSame(1, $callCount, 'Search results should be retrieved from cache without re-querying driver');
    }

    public function test_search_with_direct_url_returns_immediate_result_without_driver(): void
    {
        $results = $this->manager->search('https://www.cifraclub.com.br/aline-barros/consagracao/');

        $this->assertCount(1, $results);
        $this->assertSame('Cifra Direta', $results[0]['title']);
        $this->assertSame('Cifra Club', $results[0]['source']);
    }
}
