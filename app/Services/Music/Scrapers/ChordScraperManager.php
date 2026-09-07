<?php

declare(strict_types=1);

namespace App\Services\Music\Scrapers;

use App\Services\Music\DTOs\ScrapedChordData;
use App\Services\Music\Scrapers\Contracts\ChordScraperDriverInterface;
use App\Services\Music\Scrapers\Drivers\CifraClubDriver;
use App\Services\Music\Scrapers\Drivers\GenericHtmlDriver;
use InvalidArgumentException;

class ChordScraperManager
{
    /**
     * @var list<ChordScraperDriverInterface>
     */
    protected array $drivers = [];

    public function __construct(
        CifraClubDriver $cifraClubDriver,
        GenericHtmlDriver $genericDriver,
    ) {
        $this->drivers = [
            $cifraClubDriver,
            $genericDriver,
        ];
    }

    /**
     * Register a new custom driver.
     */
    public function registerDriver(ChordScraperDriverInterface $driver): self
    {
        array_unshift($this->drivers, $driver);

        return $this;
    }

    /**
     * Search songs across registered drivers.
     *
     * @return list<array{title: string, artist: string, url: string, source: string}>
     */
    public function search(string $query): array
    {
        $trimmed = trim($query);

        if ($trimmed === '') {
            return [];
        }

        // Direct URL query
        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $source = 'Web';
            if (str_contains($trimmed, 'cifraclub.com.br')) {
                $source = 'Cifra Club';
            } elseif (str_contains($trimmed, 'cifras.com.br')) {
                $source = 'Cifras.com.br';
            }

            return [
                [
                    'title' => 'Cifra Direta',
                    'artist' => parse_url($trimmed, PHP_URL_HOST) ?? 'Web',
                    'url' => $trimmed,
                    'source' => $source,
                ],
            ];
        }

        foreach ($this->drivers as $driver) {
            $results = $driver->search($trimmed);
            if (! empty($results)) {
                return $results;
            }
        }

        return [];
    }

    /**
     * Scrape chord content from a URL using the best matching driver.
     */
    public function scrape(string $url): ScrapedChordData
    {
        $this->validateSafeUrl($url);

        $driver = $this->getDriverForUrl($url);

        return $driver->scrape($url);
    }

    /**
     * Parse raw HTML using the best matching driver.
     */
    public function parseHtml(string $html, string $url = ''): ScrapedChordData
    {
        $driver = $this->getDriverForUrl($url);

        return $driver->parseHtml($html, $url);
    }

    /**
     * Get the first driver that supports this URL.
     */
    public function getDriverForUrl(string $url): ChordScraperDriverInterface
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supportsUrl($url)) {
                return $driver;
            }
        }

        return end($this->drivers);
    }

    /**
     * SSRF defense: ensure URL does not resolve to private/loopback/cloud metadata.
     */
    public function validateSafeUrl(string $url): void
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('A URL informada é inválida.');
        }

        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Apenas protocolos HTTP e HTTPS são suportados.');
        }

        if ($host === '' || $host === 'localhost') {
            throw new InvalidArgumentException('Host inválido para importação.');
        }

        $ip = gethostbyname($host);

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $isNotPublic = ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

            if ($isNotPublic || str_starts_with($ip, '127.') || $ip === '::1' || str_starts_with($ip, '169.254.')) {
                throw new InvalidArgumentException('Acesso a endereços de redes internas ou privadas não é permitido.');
            }
        }
    }
}
