<?php

declare(strict_types=1);

namespace App\Services\Music;

use InvalidArgumentException;

class ChordTransposerService
{
    /**
     * Complete chromatic scale using sharps.
     *
     * @var list<string>
     */
    public const SHARP_SCALE = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    /**
     * Complete chromatic scale using flats.
     *
     * @var list<string>
     */
    public const FLAT_SCALE = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

    /**
     * Note to chromatic index mapping (accounting for enharmonic equivalences).
     *
     * @var array<string, int>
     */
    private const NOTE_TO_INDEX = [
        'C' => 0, 'B#' => 0,
        'C#' => 1, 'Db' => 1,
        'D' => 2,
        'D#' => 3, 'Eb' => 3,
        'E' => 4, 'Fb' => 4,
        'F' => 5, 'E#' => 5,
        'F#' => 6, 'Gb' => 6,
        'G' => 7,
        'G#' => 8, 'Ab' => 8,
        'A' => 9,
        'A#' => 10, 'Bb' => 10,
        'B' => 11, 'Cb' => 11,
    ];

    /**
     * Regex pattern for matching the chord structure and its extensions.
     */
    private const CHORD_BODY_REGEX = '/^([A-G][#b♭♯]?)(maj|major|min|minor|m|M|dim|aug|sus|add|alt|[0-9\+\-°ºøΔ#b\(\),])*$/i';

    /**
     * Regex pattern for matching the slash bass note.
     */
    private const BASS_NOTE_REGEX = '/^[A-G][#b♭♯]?$/i';

    /**
     * Runtime cache for transposed songs and chords.
     *
     * @var array<string, string>
     */
    protected array $runtimeCache = [];

    /**
     * Transpose musical text (supporting inline ChordPro [C], 2-line chords above lyrics, and standalone chord lines).
     */
    public function transpose(string $text, string $fromKey, string $toKey): string
    {
        $cacheKey = md5($text).":{$fromKey}:{$toKey}";
        if (isset($this->runtimeCache[$cacheKey])) {
            return $this->runtimeCache[$cacheKey];
        }

        $semitones = $this->calculateSemitones($fromKey, $toKey);
        if ($semitones === 0) {
            return $this->runtimeCache[$cacheKey] = $text;
        }

        $preferFlats = $this->keyPrefersFlats($toKey);

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $normalized);
        $resultLines = [];

        foreach ($lines as $line) {
            // 1. If line contains bracketed tokens (e.g. [C] or [Intro])
            if (str_contains($line, '[') && str_contains($line, ']')) {
                // If line has bracketed chords like [C]Graça [G]maravilhosa
                $transposed = (string) preg_replace_callback('/\[(.*?)\]/', function (array $matches) use ($semitones, $preferFlats): string {
                    $token = $matches[1];

                    if ($this->isValidChord($token)) {
                        return '['.$this->transposeChord($token, $semitones, $preferFlats).']';
                    }

                    return $matches[0];
                }, $line);

                // Also if line has section tag followed by plain chords (e.g. [Intro] F  Am  G)
                $transposed = (string) preg_replace_callback('/^(\[[^\]]+\]\s*)(.+)$/i', function (array $matches) use ($semitones, $preferFlats): string {
                    $header = $matches[1];
                    $rest = $matches[2];

                    if ($this->isChordLine($rest)) {
                        return $header.$this->transposeChordLine($rest, $semitones, $preferFlats);
                    }

                    return $matches[0];
                }, $transposed);

                $resultLines[] = $transposed;

                continue;
            }

            // 2. If line is a plain chord line (2-line chords above lyrics format)
            if ($this->isChordLine($line)) {
                $resultLines[] = $this->transposeChordLine($line, $semitones, $preferFlats);

                continue;
            }

            // 3. Lyric lines or blank lines
            $resultLines[] = $line;
        }

        return implode("\n", $resultLines);
    }

    /**
     * Transpose a line of chords while maintaining column alignment.
     */
    public function transposeChordLine(string $line, int $semitones, bool $preferFlats = false): string
    {
        preg_match_all('/\S+/', $line, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return $line;
        }

        $output = '';

        foreach ($matches[0] as [$chord, $byteOffset]) {
            $charOffset = mb_strlen(substr($line, 0, $byteOffset));
            $transposed = $this->isValidChord($chord)
                ? $this->transposeChord($chord, $semitones, $preferFlats)
                : $chord;

            $currentLen = mb_strlen($output);

            if ($currentLen < $charOffset) {
                $output .= str_repeat(' ', $charOffset - $currentLen);
            } elseif ($currentLen > 0) {
                $output .= ' ';
            }

            $output .= $transposed;
        }

        return $output;
    }

    /**
     * Transpose a single chord by a specified number of semitones.
     */
    public function transposeChord(string $chord, int $semitones, bool $preferFlats = false): string
    {
        $trimmed = trim($chord);

        if (! $this->isValidChord($trimmed)) {
            return $chord;
        }

        $parts = explode('/', $trimmed, 2);
        $mainChord = $parts[0];
        $bassNote = $parts[1] ?? null;

        // Extract root note and extension of the main chord
        preg_match('/^([A-G][#b♭♯]?)(.*)$/i', $mainChord, $mainMatches);
        $mainRoot = $this->normalizeNote($mainMatches[1]);
        $mainExt = $mainMatches[2] ?? '';

        $transposedMainRoot = $this->transposeNote($mainRoot, $semitones, $preferFlats);
        $transposedChord = $transposedMainRoot.$mainExt;

        // If it is an inverted slash chord, transpose the bass note as well
        if ($bassNote !== null) {
            preg_match('/^([A-G][#b♭♯]?)(.*)$/i', $bassNote, $bassMatches);
            if (! empty($bassMatches[1])) {
                $bassRoot = $this->normalizeNote($bassMatches[1]);
                $bassExt = $bassMatches[2] ?? '';
                $transposedBassRoot = $this->transposeNote($bassRoot, $semitones, $preferFlats);
                $transposedChord .= '/'.$transposedBassRoot.$bassExt;
            } else {
                $transposedChord .= '/'.$bassNote;
            }
        }

        return $transposedChord;
    }

    /**
     * Transpose a single musical note by a specified number of semitones.
     */
    public function transposeNote(string $note, int $semitones, bool $preferFlats = false): string
    {
        $normalized = $this->normalizeNote($note);

        if (! array_key_exists($normalized, self::NOTE_TO_INDEX)) {
            return $note;
        }

        $currentIndex = self::NOTE_TO_INDEX[$normalized];
        $newIndex = (($currentIndex + $semitones) % 12 + 12) % 12;

        return $preferFlats ? self::FLAT_SCALE[$newIndex] : self::SHARP_SCALE[$newIndex];
    }

    /**
     * Calculate the semitone distance between two musical keys.
     */
    public function calculateSemitones(string $fromKey, string $toKey): int
    {
        preg_match('/^([A-G][#b♭♯]?)/i', trim($fromKey), $fromMatches);
        preg_match('/^([A-G][#b♭♯]?)/i', trim($toKey), $toMatches);

        if (empty($fromMatches[1]) || empty($toMatches[1])) {
            throw new InvalidArgumentException("Tom musical inválido fornecido: '{$fromKey}' ou '{$toKey}'.");
        }

        $fromRoot = $this->normalizeNote($fromMatches[1]);
        $toRoot = $this->normalizeNote($toMatches[1]);

        if (! array_key_exists($fromRoot, self::NOTE_TO_INDEX) || ! array_key_exists($toRoot, self::NOTE_TO_INDEX)) {
            throw new InvalidArgumentException("Tônica do tom não reconhecida: '{$fromRoot}' ou '{$toRoot}'.");
        }

        $fromIndex = self::NOTE_TO_INDEX[$fromRoot];
        $toIndex = self::NOTE_TO_INDEX[$toRoot];

        return (($toIndex - $fromIndex) % 12 + 12) % 12;
    }

    /**
     * Check whether a given string is a syntactically valid chord.
     */
    public function isValidChord(string $chord): bool
    {
        $trimmed = trim($chord);

        if ($trimmed === '') {
            return false;
        }

        $parts = explode('/', $trimmed, 2);
        $mainChord = $parts[0];
        $bassNote = $parts[1] ?? null;

        if (! preg_match(self::CHORD_BODY_REGEX, $mainChord)) {
            return false;
        }

        if ($bassNote !== null && ! preg_match(self::BASS_NOTE_REGEX, $bassNote)) {
            return false;
        }

        return true;
    }

    /**
     * Normalize a note name to uppercase root with standard '#' or 'b'.
     */
    public function normalizeNote(string $note): string
    {
        $note = trim($note);

        if ($note === '') {
            return '';
        }

        $root = strtoupper($note[0]);
        $accidental = '';

        if (strlen($note) > 1) {
            $char = $note[1];
            if ($char === '#' || $char === '♯') {
                $accidental = '#';
            } elseif ($char === 'b' || $char === 'B' || $char === '♭') {
                $accidental = 'b';
            }
        }

        return $root.$accidental;
    }

    /**
     * Normalize a musical key (e.g. 'Am', 'c#m', 'Db', 'f#') to standard root, accidental and minor indicator.
     */
    public function normalizeKey(string $key): string
    {
        $trimmed = trim($key);

        if ($trimmed === '') {
            return 'C';
        }

        if (! preg_match('/^([A-G])([#b♭♯]?)(m|min|minor)?/i', $trimmed, $matches)) {
            return 'C';
        }

        $root = strtoupper($matches[1]);
        $accidental = '';

        if (! empty($matches[2])) {
            $char = $matches[2];
            if ($char === '#' || $char === '♯') {
                $accidental = '#';
            } elseif ($char === 'b' || $char === 'B' || $char === '♭') {
                $accidental = 'b';
            }
        }

        $mode = (! empty($matches[3]) && in_array(strtolower($matches[3]), ['m', 'min', 'minor'], true)) ? 'm' : '';

        return $root.$accidental.$mode;
    }

    /**
     * Determine whether the target key prefers flats over sharps.
     */
    public function keyPrefersFlats(string $key): bool
    {
        $trimmed = trim($key);

        if (str_contains($trimmed, 'b') || str_contains($trimmed, '♭')) {
            return true;
        }

        return in_array($trimmed, ['F', 'Dm'], true);
    }

    /**
     * Check if a line consists primarily of musical chords.
     */
    public function isChordLine(string $line): bool
    {
        $trimmed = trim($line);

        if ($trimmed === '') {
            return false;
        }

        if (preg_match('/^(tom|capo|afinação|bpm|compasso|intro|refrão|ponte|verso|solo|interlúdio):/i', $trimmed)) {
            return false;
        }

        if (preg_match('/^\[(intro|verso|verse|refrão|refrao|chorus|ponte|bridge|solo|final|outro|tag|interlúdio|interlude|primeira parte|segunda parte)[\s\d]*\]$/i', $trimmed)) {
            return false;
        }

        $tokens = preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (empty($tokens)) {
            return false;
        }

        $chordCount = 0;
        foreach ($tokens as $token) {
            if ($this->isValidChord($token)) {
                $chordCount++;
            }
        }

        return $chordCount > 0 && ($chordCount / count($tokens)) >= 0.7;
    }
}
