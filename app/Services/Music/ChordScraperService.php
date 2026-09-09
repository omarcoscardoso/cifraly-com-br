<?php

declare(strict_types=1);

namespace App\Services\Music;

use App\Services\Music\Scrapers\ChordScraperManager;
use App\Services\Music\Scrapers\Drivers\CifraClubDriver;
use App\Services\Music\Scrapers\Drivers\GenericHtmlDriver;

class ChordScraperService
{
    protected ChordScraperManager $manager;

    public function __construct(
        ?ChordToChordProConverter $converter = null,
        ?ChordTransposerService $transposer = null,
        ?ChordScraperManager $manager = null
    ) {
        $converter ??= new ChordToChordProConverter($transposer ?? new ChordTransposerService);
        $transposer ??= new ChordTransposerService;

        $this->manager = $manager ?? new ChordScraperManager(
            new CifraClubDriver($converter, $transposer),
            new GenericHtmlDriver($converter, $transposer),
        );
    }

    /**
     * Search for songs on chord websites and return a list of matching results.
     *
     * @return list<array{title: string, artist: string, url: string, source: string}>
     */
    public function searchSong(string $query): array
    {
        return $this->manager->search($query);
    }

    /**
     * Download and extract chord content from a URL.
     *
     * @return array{title: string, artist: string, original_key: string, raw_chords: string, chordpro_content: string, bpm: ?int, time_signature: ?string, source_url: ?string}
     */
    public function importFromUrl(string $url): array
    {
        return $this->manager->scrape($url)->toArray();
    }

    /**
     * Parse HTML and extract metadata and chords.
     *
     * @return array{title: string, artist: string, original_key: string, raw_chords: string, chordpro_content: string, bpm: ?int, time_signature: ?string, source_url: ?string}
     */
    public function parseHtmlContent(string $html, string $url = ''): array
    {
        return $this->manager->parseHtml($html, $url)->toArray();
    }

    /**
     * Get underlying driver manager.
     */
    public function getManager(): ChordScraperManager
    {
        return $this->manager;
    }
}
