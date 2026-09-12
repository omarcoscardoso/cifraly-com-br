<?php

declare(strict_types=1);

namespace App\Services\Music;

use Illuminate\Support\HtmlString;
use Throwable;

class StageChordFormatterService
{
    public function __construct(
        protected ChordTransposerService $transposer,
    ) {}

    /**
     * Transpose (if needed) and format chord content into stage-optimized HTML.
     */
    public function transposeAndFormat(?string $content, string $fromKey, ?string $toKey): HtmlString
    {
        if (blank($content)) {
            return new HtmlString('<p class="text-slate-500 italic p-8">Cifra não cadastrada para esta música.</p>');
        }

        $targetKey = $toKey ?? $fromKey;

        try {
            $transposed = $this->transposer->transpose($content, $fromKey, $targetKey);
        } catch (Throwable) {
            $transposed = $content;
        }

        return $this->formatForStageHtml($transposed);
    }

    /**
     * Convert raw text/chords into stage-ready HTML with section headers, chord spans, and lyric lines.
     */
    public function formatForStageHtml(string $text): HtmlString
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = str_replace("\t", '    ', $normalized);
        $lines = explode("\n", $normalized);
        $htmlLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Refrão / Chorus: destaque em altar-amber e target para salto rápido
            if (preg_match('/^\[(refrão|refrao|chorus)[^\]]*\]/i', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-chorus stage-chorus-target">'.e($line).'</div>';

                continue;
            }

            // Outras seções estruturais (Intro, Verso, Ponte, etc.)
            if (preg_match('/^\[(intro|verso|verse|ponte|bridge|solo|final|outro|tag|interlúdio|interlude)[^\]]*\]/i', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-badge">'.e($line).'</div>';

                continue;
            }

            // Standalone section bracket e.g. [Parte 1]
            if (preg_match('/^\[[^\]]+\]$/', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-tag">'.e($line).'</div>';

                continue;
            }

            // Chord line
            if ($this->transposer->isChordLine($line)) {
                // Highlight chords in amber while preserving spacing
                $escaped = e($line);
                // Wrap whitespace separated tokens that are valid chords in styled spans
                $formatted = preg_replace_callback('/\S+/', function (array $m): string {
                    $token = $m[0];
                    if ($this->transposer->isValidChord(htmlspecialchars_decode($token))) {
                        return '<span class="stage-chord text-amber-400 font-bold">'.$token.'</span>';
                    }

                    return '<span class="stage-chord-token text-amber-200">'.$token.'</span>';
                }, $escaped);

                $htmlLines[] = '<div class="stage-chord-line leading-tight font-bold whitespace-pre" style="white-space: pre;">'.$formatted.'</div>';

                continue;
            }

            // Lyric or other text line
            $htmlLines[] = '<div class="stage-lyric-line text-slate-200 leading-snug whitespace-pre mb-2" style="white-space: pre;">'.e($line).'</div>';
        }

        return new HtmlString(implode("\n", $htmlLines));
    }
}
