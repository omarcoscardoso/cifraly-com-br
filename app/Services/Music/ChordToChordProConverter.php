<?php

declare(strict_types=1);

namespace App\Services\Music;

class ChordToChordProConverter
{
    public function __construct(
        protected ChordTransposerService $transposer = new ChordTransposerService
    ) {}

    /**
     * Convert chord text to 2-line format (chords on top of lyrics).
     */
    public function convert(string $rawText): string
    {
        return $this->toTwoLine($rawText);
    }

    /**
     * Convert any chord text format into clean 2-line chords-above-lyrics format.
     */
    public function toTwoLine(string $text): string
    {
        if (str_contains($text, '[') && str_contains($text, ']')) {
            return $this->chordProToTwoLine($text);
        }

        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
        $cleaned = array_map('rtrim', $lines);

        return implode("\n", $cleaned);
    }

    /**
     * Convert inline ChordPro notation into 2-line chords-above-lyrics format.
     */
    public function chordProToTwoLine(string $chordProText): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $chordProText);
        $lines = explode("\n", $normalized);
        $output = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $output[] = '';

                continue;
            }

            // Standalone section tag (e.g. [Intro], [Refrão], [Verso 1])
            if (preg_match('/^\[(intro|verso|verse|refrão|refrao|chorus|ponte|bridge|solo|final|outro|tag|interlúdio|interlude)[\s\d]*\]$/i', $trimmed)) {
                $output[] = $trimmed;

                continue;
            }

            // Standalone chord-only line (e.g. [C]   [G]   [Am]   [F])
            if (preg_match('/^(\s*\[[^\]]+\]\s*)+$/', $trimmed)) {
                $unbracketed = (string) preg_replace_callback('/\[([^\]]+)\]/', function (array $m): string {
                    return $this->transposer->isValidChord($m[1]) ? $m[1] : $m[0];
                }, $line);

                if ($this->isChordLine($unbracketed)) {
                    $output[] = rtrim($unbracketed);

                    continue;
                }
            }

            // Section tag with content (e.g. [Intro] [F] [Am] [G] or [Refrão] [C]Quão grande)
            if (preg_match('/^(\[(?:intro|verso|verse|refrão|refrao|chorus|ponte|bridge|solo|final|outro|tag|interlúdio|interlude)[\s\d]*\])\s*(.+)$/i', $trimmed, $headerMatch)) {
                $header = $headerMatch[1];
                $rest = $headerMatch[2];

                // If rest is just chords (e.g. [F]  [Am]  [G])
                $unbracketed = (string) preg_replace_callback('/\[([^\]]+)\]/', function (array $m): string {
                    return $this->transposer->isValidChord($m[1]) ? $m[1] : $m[0];
                }, $rest);

                if ($this->isChordLine($unbracketed)) {
                    $output[] = $header.' '.$unbracketed;

                    continue;
                }

                $output[] = $header;
                $renderedRest = $this->renderLineToTwoLine($rest);
                if ($renderedRest !== '') {
                    $output[] = $renderedRest;
                }

                continue;
            }

            // Standard line with or without inline chords
            $output[] = $this->renderLineToTwoLine($line);
        }

        return implode("\n", $output);
    }

    /**
     * Render a single inline ChordPro line into 2-line format (chords line + lyrics line).
     */
    public function renderLineToTwoLine(string $line): string
    {
        if (! str_contains($line, '[') || ! str_contains($line, ']')) {
            return $line;
        }

        $tokens = preg_split('/(\[[^\]]+\])/', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];

        $hasChords = false;
        foreach ($tokens as $token) {
            if (str_starts_with($token, '[') && str_ends_with($token, ']')) {
                $inside = substr($token, 1, -1);
                if ($this->transposer->isValidChord($inside)) {
                    $hasChords = true;

                    break;
                }
            }
        }

        if (! $hasChords) {
            return $line;
        }

        $chordLine = '';
        $lyricLine = '';

        foreach ($tokens as $token) {
            if (str_starts_with($token, '[') && str_ends_with($token, ']')) {
                $inside = substr($token, 1, -1);

                if ($this->transposer->isValidChord($inside)) {
                    $targetCol = mb_strlen($lyricLine);
                    $currentCol = mb_strlen($chordLine);

                    if ($currentCol < $targetCol) {
                        $chordLine .= str_repeat(' ', $targetCol - $currentCol);
                    } elseif ($currentCol > $targetCol) {
                        $chordLine .= ' ';
                        $newCol = mb_strlen($chordLine);
                        $lyricLine .= str_repeat(' ', $newCol - $targetCol);
                    }

                    $chordLine .= $inside;

                    continue;
                }
            }

            $lyricLine .= $token;
        }

        if (trim($lyricLine) === '') {
            return rtrim($chordLine);
        }

        return rtrim($chordLine)."\n".rtrim($lyricLine);
    }

    /**
     * Convert standard 2-line chord/lyrics sheet to inline ChordPro format.
     */
    public function twoLineToChordPro(string $twoLineText): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $twoLineText);
        $lines = explode("\n", $text);

        $output = [];
        $i = 0;
        $total = count($lines);

        while ($i < $total) {
            $currentLine = $lines[$i];

            if (trim($currentLine) === '') {
                $output[] = '';
                $i++;

                continue;
            }

            if ($this->isChordLine($currentLine)) {
                $nextLine = $lines[$i + 1] ?? null;

                if ($nextLine !== null && ! $this->isChordLine($nextLine) && trim($nextLine) !== '') {
                    $merged = $this->mergeChordAndLyricLines($currentLine, $nextLine);
                    $output[] = $merged;
                    $i += 2;

                    continue;
                }

                $output[] = $this->wrapStandaloneChordLine($currentLine);
                $i++;

                continue;
            }

            $output[] = $currentLine;
            $i++;
        }

        return implode("\n", $output);
    }

    /**
     * Alias for twoLineToChordPro.
     */
    public function toChordPro(string $text): string
    {
        return $this->twoLineToChordPro($text);
    }

    /**
     * Check if a line consists primarily of musical chords.
     */
    public function isChordLine(string $line): bool
    {
        return $this->transposer->isChordLine($line);
    }

    /**
     * Merge a chord line with its corresponding lyrics line based on column offsets.
     */
    public function mergeChordAndLyricLines(string $chordLine, string $lyricLine): string
    {
        preg_match_all('/\S+/', $chordLine, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return $lyricLine;
        }

        $chords = array_reverse($matches[0]);
        $result = $lyricLine;

        foreach ($chords as [$chord, $byteOffset]) {
            if (! $this->transposer->isValidChord($chord)) {
                continue;
            }

            $charOffset = mb_strlen(substr($chordLine, 0, $byteOffset));

            if ($charOffset >= mb_strlen($result)) {
                $result = str_pad($result, $charOffset, ' ')."[{$chord}]";
            } else {
                $before = mb_substr($result, 0, $charOffset);
                $after = mb_substr($result, $charOffset);
                $result = $before."[{$chord}]".$after;
            }
        }

        return $result;
    }

    /**
     * Wrap all chords on a standalone progression line in brackets.
     */
    public function wrapStandaloneChordLine(string $chordLine): string
    {
        return (string) preg_replace_callback('/\S+/', function (array $match): string {
            $token = $match[0];

            if ($this->transposer->isValidChord($token)) {
                return "[{$token}]";
            }

            return $token;
        }, $chordLine);
    }
}
