<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\YouTubeSearchService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YouTubeSearchServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_returns_null_when_api_key_is_missing(): void
    {
        $service = new YouTubeSearchService(apiKey: '');

        $url = $service->searchVideoUrl('Lugar Secreto', 'Gabriela Rocha');

        $this->assertNull($url);
    }

    public function test_searches_video_and_returns_valid_youtube_url(): void
    {
        Http::fake([
            'https://www.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => [
                            'videoId' => 'dQw4w9WgXcQ',
                        ],
                        'snippet' => [
                            'title' => 'Gabriela Rocha - Lugar Secreto (Clipe Oficial)',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new YouTubeSearchService(apiKey: 'fake-youtube-key');

        $url = $service->searchVideoUrl('Lugar Secreto', 'Gabriela Rocha');

        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $url);
    }

    public function test_caches_successful_search_and_avoids_duplicate_http_requests(): void
    {
        Http::fake([
            'https://www.googleapis.com/youtube/v3/search*' => Http::sequence()
                ->push([
                    'items' => [
                        [
                            'id' => ['videoId' => 'video123'],
                        ],
                    ],
                ], 200)
                ->push(['items' => []], 500), // Segunda requisição falharia se fosse chamada
        ]);

        $service = new YouTubeSearchService(apiKey: 'fake-youtube-key');

        $firstCall = $service->searchVideoUrl('Oceanos', 'Ana Nóbrega');
        $secondCall = $service->searchVideoUrl('Oceanos', 'Ana Nóbrega');

        $this->assertSame('https://www.youtube.com/watch?v=video123', $firstCall);
        $this->assertSame('https://www.youtube.com/watch?v=video123', $secondCall);

        Http::assertSentCount(1);
    }

    public function test_handles_api_failure_gracefully_returning_null(): void
    {
        Http::fake([
            'https://www.googleapis.com/youtube/v3/search*' => Http::response([
                'error' => [
                    'message' => 'Quota exceeded',
                ],
            ], 403),
        ]);

        $service = new YouTubeSearchService(apiKey: 'fake-youtube-key');

        $url = $service->searchVideoUrl('Música Qualquer', 'Artista');

        $this->assertNull($url);
    }

    public function test_get_search_query_url_generates_valid_url(): void
    {
        $service = new YouTubeSearchService(apiKey: null);

        $url = $service->getSearchQueryUrl('Lugar Secreto', 'Gabriela Rocha');

        $this->assertSame('https://www.youtube.com/results?search_query=Gabriela+Rocha+Lugar+Secreto', $url);
    }
}
