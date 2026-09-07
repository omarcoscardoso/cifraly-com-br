<?php

declare(strict_types=1);

namespace App\Services\Music\DTOs;

final readonly class ScrapedChordData
{
    public function __construct(
        public string $title,
        public string $artist,
        public string $originalKey,
        public string $rawChords,
        public string $chordProContent,
        public ?int $bpm = null,
        public ?string $timeSignature = null,
        public ?string $sourceUrl = null,
    ) {}

    /**
     * @return array{title: string, artist: string, original_key: string, raw_chords: string, chordpro_content: string, bpm: ?int, time_signature: ?string, source_url: ?string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'artist' => $this->artist,
            'original_key' => $this->originalKey,
            'raw_chords' => $this->rawChords,
            'chordpro_content' => $this->chordProContent,
            'bpm' => $this->bpm,
            'time_signature' => $this->timeSignature,
            'source_url' => $this->sourceUrl,
        ];
    }
}
