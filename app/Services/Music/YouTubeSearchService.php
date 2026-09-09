<?php

declare(strict_types=1);

namespace App\Services\Music;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class YouTubeSearchService
{
    public function __construct(
        protected ?string $apiKey = null,
    ) {
        $this->apiKey ??= config('services.youtube.key');
    }

    /**
     * Search for a YouTube video URL given song title and artist.
     * Caches successful searches for 30 days to optimize API quota.
     */
    public function searchVideoUrl(string $title, ?string $artist = null): ?string
    {
        $cleanTitle = trim($title);
        $cleanArtist = trim($artist ?? '');

        if ($cleanTitle === '') {
            return null;
        }

        $query = $cleanArtist !== '' ? "{$cleanArtist} - {$cleanTitle}" : $cleanTitle;
        $cacheKey = 'youtube_search:'.md5(mb_strtolower($query));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($query): ?string {
            if (empty($this->apiKey)) {
                return null;
            }

            try {
                $response = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'maxResults' => 1,
                    'key' => $this->apiKey,
                ]);

                if (! $response->successful()) {
                    Log::warning('YouTube API search request failed', [
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);

                    return null;
                }

                $items = $response->json('items') ?? [];
                if (empty($items)) {
                    return null;
                }

                $videoId = $items[0]['id']['videoId'] ?? null;
                if (! $videoId) {
                    return null;
                }

                return "https://www.youtube.com/watch?v={$videoId}";
            } catch (Throwable $e) {
                Log::warning('YouTube API search exception: '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Generate fallback YouTube search query URL if no direct video found.
     */
    public function getSearchQueryUrl(string $title, ?string $artist = null): string
    {
        $cleanTitle = trim($title);
        $cleanArtist = trim($artist ?? '');
        $query = $cleanArtist !== '' ? "{$cleanArtist} {$cleanTitle}" : $cleanTitle;

        return 'https://www.youtube.com/results?search_query='.urlencode($query);
    }
}
