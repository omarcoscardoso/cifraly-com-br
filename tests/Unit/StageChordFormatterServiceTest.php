<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\ChordTransposerService;
use App\Services\Music\StageChordFormatterService;
use Tests\TestCase;

class StageChordFormatterServiceTest extends TestCase
{
    private StageChordFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = new StageChordFormatterService(new ChordTransposerService);
    }

    public function test_returns_fallback_message_when_content_is_blank(): void
    {
        $html = $this->formatter->transposeAndFormat(null, 'C', 'C');
        $this->assertStringContainsString('Cifra não cadastrada', $html->toHtml());

        $htmlBlank = $this->formatter->transposeAndFormat('', 'C', 'D');
        $this->assertStringContainsString('Cifra não cadastrada', $htmlBlank->toHtml());
    }

    public function test_transposes_and_formats_chords_for_stage(): void
    {
        $content = "C     G     Am    F\nDeus é bom todo tempo";
        // Transpose from C to D (+2 semitones): C->D, G->A, Am->Bm, F->G
        $html = $this->formatter->transposeAndFormat($content, 'C', 'D');

        $result = $html->toHtml();
        $this->assertStringContainsString('stage-chord', $result);
        $this->assertStringContainsString('D', $result);
        $this->assertStringContainsString('A', $result);
        $this->assertStringContainsString('Bm', $result);
        $this->assertStringContainsString('G', $result);
        $this->assertStringContainsString('Deus é bom todo tempo', $result);
    }

    public function test_formats_structural_sections_correctly(): void
    {
        $content = "[Intro]\n[Refrão]\n[Ponte]\n[Parte 1]\n[Chorus]";
        $html = $this->formatter->formatForStageHtml($content);

        $result = $html->toHtml();
        $this->assertStringContainsString('stage-section-chorus stage-chorus-target', $result);
        $this->assertStringContainsString('stage-section-badge', $result);
        $this->assertStringContainsString('stage-section-tag', $result);
    }

    public function test_escapes_html_and_prevents_xss(): void
    {
        $content = "[Intro <script>alert(1)</script>]\n<img src=x onerror=alert(2)>\nC   G";
        $html = $this->formatter->formatForStageHtml($content);

        $result = $html->toHtml();
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('<img src=x', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(2)&gt;', $result);
    }
}
