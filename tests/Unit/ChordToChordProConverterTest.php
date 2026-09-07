<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
use PHPUnit\Framework\TestCase;

class ChordToChordProConverterTest extends TestCase
{
    private ChordToChordProConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new ChordToChordProConverter(new ChordTransposerService);
    }

    public function test_converts_chordpro_to_two_line_chords_above_lyrics(): void
    {
        $input = "[C]Graça maravil[G]hosa\n[Am]Que me alcanç[F]ou";
        $result = $this->converter->chordProToTwoLine($input);

        $expected = "C            G\nGraça maravilhosa\nAm           F\nQue me alcançou";
        $this->assertSame($expected, $result);
    }

    public function test_converts_two_line_chords_and_lyrics_into_chordpro(): void
    {
        $input = "C             G\nGraça maravilhosa\nAm            F\nQue me alcançou";
        $result = $this->converter->twoLineToChordPro($input);

        $this->assertSame("[C]Graça maravilh[G]osa\n[Am]Que me alcanço[F]u", $result);
    }

    public function test_converts_standalone_instrumental_progression_lines(): void
    {
        $input = "[Intro]\nC   G   Am   F";
        $chordpro = $this->converter->twoLineToChordPro($input);

        $this->assertSame("[Intro]\n[C]   [G]   [Am]   [F]", $chordpro);

        $twoLine = $this->converter->chordProToTwoLine($chordpro);
        $this->assertSame("[Intro]\nC   G   Am   F", $twoLine);
    }

    public function test_preserves_section_headers_and_blank_lines(): void
    {
        $input = "[Intro] [C] [G] [Am] [F]\n\n[Refrão]\n[C]Quão grande é [G]o meu Deus";
        $result = $this->converter->chordProToTwoLine($input);

        $expected = "[Intro] C G Am F\n\n[Refrão]\nC             G\nQuão grande é o meu Deus";
        $this->assertSame($expected, $result);
    }

    public function test_handles_complex_chords_with_slash_and_tensions(): void
    {
        $input = "[D/F#]Tu és Santo, [G]Senhor\n[F#m7(b5)]Rei do unive[B7]rso";
        $result = $this->converter->chordProToTwoLine($input);

        $expected = "D/F#         G\nTu és Santo, Senhor\nF#m7(b5)    B7\nRei do universo";
        $this->assertSame($expected, $result);
    }

    public function test_pads_lyrics_when_chord_is_wider_than_words_beneath_it(): void
    {
        $input = '[F#m7(b5)]Rei [B7]do universo';
        $result = $this->converter->chordProToTwoLine($input);

        $expected = "F#m7(b5) B7\nRei      do universo";
        $this->assertSame($expected, $result);
    }

    public function test_to_two_line_normalizes_any_chord_text(): void
    {
        $input = "C             G\nGraça maravilhosa";
        $result = $this->converter->toTwoLine($input);

        $this->assertSame($input, $result);
    }

    public function test_correctly_identifies_chord_lines_vs_lyric_lines(): void
    {
        $this->assertTrue($this->converter->isChordLine('C   G   Am7   F'));
        $this->assertTrue($this->converter->isChordLine('D/F#  Em9  A7(b9)'));
        $this->assertFalse($this->converter->isChordLine('Graça maravilhosa'));
        $this->assertFalse($this->converter->isChordLine('Tom: G'));
        $this->assertFalse($this->converter->isChordLine('[Intro]'));
        $this->assertFalse($this->converter->isChordLine('[Refrão]'));
        $this->assertFalse($this->converter->isChordLine(''));
    }
}
