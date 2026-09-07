<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Music\ChordTransposerService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ChordTransposerServiceTest extends TestCase
{
    private ChordTransposerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ChordTransposerService;
    }

    public function test_transposes_chordpro_text_from_c_to_d(): void
    {
        $input = '[C] Olá [G] mundo';
        $result = $this->service->transpose($input, 'C', 'D');

        $this->assertSame('[D] Olá [A] mundo', $result);
    }

    public function test_transposes_slash_chords_by_one_tone(): void
    {
        $input = '[D/F#] e [C/E]';
        $result = $this->service->transpose($input, 'C', 'D');

        $this->assertSame('[E/G#] e [D/F#]', $result);
    }

    public function test_transposes_with_flats_from_g_to_b_flat(): void
    {
        $input = '[G] Santo! [Em] Digno [C] és Tu [D]';
        $result = $this->service->transpose($input, 'G', 'Bb');

        $this->assertSame('[Bb] Santo! [Gm] Digno [Eb] és Tu [F]', $result);
    }

    public function test_transposes_single_chords_with_various_extensions_and_tensions(): void
    {
        // G + 2 semitones -> A
        $this->assertSame('A', $this->service->transposeChord('G', 2));

        // Am + 2 semitones -> Bm
        $this->assertSame('Bm', $this->service->transposeChord('Am', 2));

        // C#m7 + 2 semitones -> D#m7
        $this->assertSame('D#m7', $this->service->transposeChord('C#m7', 2));

        // Bb/D + 2 semitones -> C/E
        $this->assertSame('C/E', $this->service->transposeChord('Bb/D', 2));

        // F#m7(b5) + 2 semitones -> G#m7(b5)
        $this->assertSame('G#m7(b5)', $this->service->transposeChord('F#m7(b5)', 2));

        // Dsus4 + 2 semitones -> Esus4
        $this->assertSame('Esus4', $this->service->transposeChord('Dsus4', 2));
    }

    public function test_transposes_notes_respecting_enharmonic_scales(): void
    {
        // Sharps scale
        $this->assertSame('C#', $this->service->transposeNote('C', 1, preferFlats: false));
        $this->assertSame('F#', $this->service->transposeNote('E', 2, preferFlats: false));

        // Flats scale
        $this->assertSame('Db', $this->service->transposeNote('C', 1, preferFlats: true));
        $this->assertSame('Eb', $this->service->transposeNote('D', 1, preferFlats: true));
        $this->assertSame('Bb', $this->service->transposeNote('A', 1, preferFlats: true));

        // Enharmonics like B# (which is C) transposed + 2 -> D
        $this->assertSame('D', $this->service->transposeNote('B#', 2));
    }

    public function test_calculates_semitones_between_keys(): void
    {
        $this->assertSame(2, $this->service->calculateSemitones('C', 'D'));
        $this->assertSame(3, $this->service->calculateSemitones('G', 'Bb'));
        $this->assertSame(10, $this->service->calculateSemitones('D', 'C'));
        $this->assertSame(0, $this->service->calculateSemitones('E', 'E'));
        $this->assertSame(2, $this->service->calculateSemitones('Am', 'Bm'));
    }

    public function test_throws_exception_on_invalid_key_when_calculating_semitones(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculateSemitones('H', 'C');
    }

    public function test_preserves_chordpro_directives_and_section_tags(): void
    {
        $input = "{title: Quão Grande É o Meu Deus}\n[Intro]\n[C] [G/B] [Am] [F]\n[Refrão]\n[C] Quão grande é o meu [G] Deus";
        $expected = "{title: Quão Grande É o Meu Deus}\n[Intro]\n[D] [A/C#] [Bm] [G]\n[Refrão]\n[D] Quão grande é o meu [A] Deus";

        $result = $this->service->transpose($input, 'C', 'D');

        $this->assertSame($expected, $result);
    }

    public function test_transposes_two_line_chord_sheet_preserving_column_alignment(): void
    {
        $input = "[Intro] F  Am  G\n\nC             G\nGraça maravilhosa\nAm            F\nQue me alcançou";
        $expected = "[Intro] G  Bm  A\n\nD             A\nGraça maravilhosa\nBm            G\nQue me alcançou";

        $result = $this->service->transpose($input, 'C', 'D');

        $this->assertSame($expected, $result);
    }

    public function test_transposes_two_line_chord_sheet_with_accidental_alignment(): void
    {
        $input = "C             G\nGraça maravilhosa\nAm            F\nQue me alcançou";
        $expected = "C#            G#\nGraça maravilhosa\nA#m           F#\nQue me alcançou";

        $result = $this->service->transpose($input, 'C', 'C#');

        $this->assertSame($expected, $result);
    }

    public function test_validates_chords_correctly(): void
    {
        $this->assertTrue($this->service->isValidChord('C'));
        $this->assertTrue($this->service->isValidChord('Am7'));
        $this->assertTrue($this->service->isValidChord('F#m7(b5)'));
        $this->assertTrue($this->service->isValidChord('D/F#'));
        $this->assertTrue($this->service->isValidChord('C7M(9)'));
        $this->assertTrue($this->service->isValidChord('Dsus4'));
        $this->assertTrue($this->service->isValidChord('Bbadd9'));

        $this->assertFalse($this->service->isValidChord('Intro'));
        $this->assertFalse($this->service->isValidChord('Verse 1'));
        $this->assertFalse($this->service->isValidChord('Chorus'));
        $this->assertFalse($this->service->isValidChord('Ponte'));
        $this->assertFalse($this->service->isValidChord('Solo'));
        $this->assertFalse($this->service->isValidChord(''));
    }
}
