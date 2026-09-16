<?php

declare(strict_types=1);

namespace App\Services\Music;

use Illuminate\Support\HtmlString;
use Throwable;

class StageChordFormatterService
{
    public function __construct(
        protected ChordTransposerService $transposer,
        protected ?ChordToChordProConverter $converter = null,
    ) {
        $this->converter ??= new ChordToChordProConverter($this->transposer);
    }

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
     * Suporta tanto o formato ChordPro [C] quanto a conversão transparente de 2 linhas tradicionais.
     */
    public function formatForStageHtml(string $text): HtmlString
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = str_replace(["\xc2\xa0", "\u{00A0}"], ' ', $normalized);
        $normalized = str_replace("\t", '    ', $normalized);

        // Converte deterministicamente linhas de 2 linhas tradicionais para ChordPro antes da renderização em blocos
        $chordProText = $this->converter->toChordPro($normalized);

        $lines = explode("\n", $chordProText);
        $htmlLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $htmlLines[] = '<div class="stage-empty-line h-4"></div>';

                continue;
            }

            // Refrão / Chorus: destaque em altar-amber e target para salto rápido
            if (preg_match('/^\[(refrão|refrao|chorus)[^\]]*\]/i', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-chorus stage-chorus-target">'.e($trimmed).'</div>';

                continue;
            }

            // Outras seções estruturais (Intro, Verso, Ponte, etc.)
            if (preg_match('/^\[(intro|verso|verse|ponte|bridge|solo|final|outro|tag|interlúdio|interlude)[^\]]*\]/i', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-badge">'.e($trimmed).'</div>';

                continue;
            }

            // Standalone section bracket e.g. [Parte 1]
            if (preg_match('/^\[[^\]]+\]$/', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-tag">'.e($trimmed).'</div>';

                continue;
            }

            // Linha instrumental / puramente de acordes (ex: [Intro] F  Gm7  Bb  C ou [C] [G] [Am] [F])
            $unbracketed = (string) preg_replace_callback('/\[([^\]]+)\]/', function (array $m): string {
                return $this->transposer->isValidChord($m[1]) ? $m[1] : $m[0];
            }, $line);

            if ($this->transposer->isChordLine($unbracketed)) {
                $formatted = preg_replace_callback('/\S+/', function (array $m): string {
                    $token = $m[0];
                    if ($this->transposer->isValidChord(htmlspecialchars_decode($token))) {
                        return '<span class="stage-chord text-amber-400 font-bold">'.e($token).'</span>';
                    }

                    return '<span class="stage-chord-token text-amber-200">'.e($token).'</span>';
                }, $unbracketed);

                $htmlLines[] = '<div class="stage-chord-line leading-tight font-bold whitespace-pre font-mono my-1" style="white-space: pre;">'.$formatted.'</div>';

                continue;
            }

            // Linha contendo acordes inline entre colchetes [Acorde]Letra
            if (str_contains($line, '[') && str_contains($line, ']')) {
                $renderedLine = $this->renderInlineChordProLine($line);
                if ($renderedLine !== null) {
                    $htmlLines[] = $renderedLine;

                    continue;
                }
            }

            // Linha de letra pura (sem acordes)
            $htmlLines[] = '<div class="stage-lyric-line text-slate-200 leading-snug whitespace-pre mb-2" style="white-space: pre;">'.e($line).'</div>';
        }

        return new HtmlString(implode("\n", $htmlLines));
    }

    /**
     * Renderiza uma linha com acordes inline ChordPro em blocos verticais perfeitamente alinhados com cada sílaba/palavra.
     */
    protected function renderInlineChordProLine(string $line): ?string
    {
        $tokens = preg_split('/(\[[^\]]+\])/', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];

        $hasValidChord = false;
        foreach ($tokens as $token) {
            if (str_starts_with($token, '[') && str_ends_with($token, ']')) {
                $inside = substr($token, 1, -1);
                if ($this->transposer->isValidChord($inside)) {
                    $hasValidChord = true;

                    break;
                }
            }
        }

        if (! $hasValidChord) {
            return null;
        }

        $pairs = [];
        $currentChord = null;

        foreach ($tokens as $token) {
            if (str_starts_with($token, '[') && str_ends_with($token, ']')) {
                $inside = substr($token, 1, -1);
                if ($this->transposer->isValidChord($inside)) {
                    if ($currentChord !== null) {
                        $pairs[] = ['chord' => $currentChord, 'lyric' => ''];
                    }
                    $currentChord = $inside;

                    continue;
                }
            }

            // Separa palavras preservando espaços para permitir quebra de linha natural
            preg_match_all('/\S+\s*/u', $token, $wordMatches);
            if (! empty($wordMatches[0])) {
                foreach ($wordMatches[0] as $idx => $word) {
                    $pairs[] = [
                        'chord' => ($idx === 0) ? $currentChord : null,
                        'lyric' => $word,
                    ];
                }
                $currentChord = null;
            } else {
                $pairs[] = ['chord' => $currentChord, 'lyric' => $token];
                $currentChord = null;
            }
        }

        if ($currentChord !== null) {
            $pairs[] = ['chord' => $currentChord, 'lyric' => ''];
        }

        $pairsHtml = [];
        foreach ($pairs as $pair) {
            $chord = $pair['chord'];
            $lyric = $pair['lyric'];

            if ($chord !== null) {
                $chordSpan = '<span class="stage-chord text-amber-400 font-bold leading-none select-none font-mono text-[0.85em] pb-1">'.e($chord).'</span>';
            } else {
                $chordSpan = '<span class="stage-chord-placeholder leading-none font-mono text-[0.85em] select-none opacity-0 pb-1" aria-hidden="true">&nbsp;</span>';
            }

            $lyricText = ($lyric === '') ? '&nbsp;' : e($lyric);
            $lyricSpan = '<span class="stage-lyric text-slate-100 leading-snug whitespace-pre">'.$lyricText.'</span>';

            $pairsHtml[] = '<span class="stage-chord-pair inline-flex flex-col justify-end align-bottom">'.$chordSpan.$lyricSpan.'</span>';
        }

        return '<div class="stage-lyric-chord-line flex flex-wrap items-end my-1 leading-normal">'.implode('', $pairsHtml).'</div>';
    }
}
