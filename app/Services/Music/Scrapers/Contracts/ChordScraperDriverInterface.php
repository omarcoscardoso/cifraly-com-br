<?php

declare(strict_types=1);

namespace App\Services\Music\Scrapers\Contracts;

use App\Services\Music\DTOs\ScrapedChordData;

interface ChordScraperDriverInterface
{
    /**
     * Check if this driver supports scraping the given URL.
     */
    public function supportsUrl(string $url): bool;

    /**
     * Search for songs using this provider.
     *
     * @return list<array{title: string, artist: string, url: string, source: string}>
     */
    public function search(string $query): array;

    /**
     * Download and extract chord content from a URL.
     */
    public function scrape(string $url): ScrapedChordData;

    /**
     * Parse raw HTML content from this provider.
     */
    public function parseHtml(string $html, string $url = ''): ScrapedChordData;
}
