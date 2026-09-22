<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Models\Organization;
use App\Models\Song;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SongImportWebActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create(['slug' => 'igreja-central']);
        $this->user->organizations()->attach($this->organization);

        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_can_search_and_import_web_chord_into_create_song_form(): void
    {
        Http::fake([
            'https://www.cifraclub.com.br/aline-barros/consagracao/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Consagração - Aline Barros - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>D</a></span>
    <pre>
D             A
Ao Rei dos reis consagro
    </pre>
</body>
</html>
HTML, 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateSong::class)
            ->mountAction('searchWebChord')
            ->setActionData([
                'search_query' => 'https://www.cifraclub.com.br/aline-barros/consagracao/',
                'selected_url' => 'https://www.cifraclub.com.br/aline-barros/consagracao/',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertFormSet([
                'title' => 'Consagração',
                'artist' => 'Aline Barros',
                'original_key' => 'D',
            ]);
    }

    public function test_can_search_and_import_web_chord_into_edit_song_form(): void
    {
        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Musica Antiga',
            'artist' => 'Artista Antigo',
            'original_key' => 'C',
        ]);

        Http::fake([
            'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Lugar Secreto - Gabriela Rocha - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>G</a></span>
    <pre>
G             D
Tu és tudo o que eu mais quero
    </pre>
</body>
</html>
HTML, 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(EditSong::class, [
                'record' => $song->getRouteKey(),
            ])
            ->mountAction('searchWebChord')
            ->setActionData([
                'search_query' => 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/',
                'selected_url' => 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertFormSet([
                'title' => 'Lugar Secreto',
                'artist' => 'Gabriela Rocha',
                'original_key' => 'G',
            ]);
    }

    public function test_can_search_by_song_name_and_import_into_create_song_form(): void
    {
        Http::fake([
            'https://solr.sscdn.co/cifraclub/ac/*' => Http::response([
                'response' => [
                    'docs' => [
                        [
                            't' => '2',
                            'art' => 'Gabriela Rocha',
                            'dns' => 'gabriela-rocha',
                            'txt' => 'Lugar Secreto',
                            'url' => 'lugar-secreto',
                        ],
                    ],
                ],
            ], 200),
            'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Lugar Secreto - Gabriela Rocha - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>C</a></span>
    <pre>
C             G
Tu és tudo o que eu mais quero
    </pre>
</body>
</html>
HTML, 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateSong::class)
            ->mountAction('searchWebChord')
            ->setActionData([
                'search_query' => 'gabriela rocha lugar secreto',
                'selected_url' => 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertFormSet([
                'title' => 'Lugar Secreto',
                'artist' => 'Gabriela Rocha',
                'original_key' => 'C',
            ]);
    }

    public function test_can_import_when_only_search_query_url_is_provided(): void
    {
        Http::fake([
            'https://www.cifraclub.com.br/aline-barros/consagracao/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Consagração - Aline Barros - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>D</a></span>
    <pre>
D             A
Ao Rei dos reis consagro
    </pre>
</body>
</html>
HTML, 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateSong::class)
            ->mountAction('searchWebChord')
            ->setActionData([
                'search_query' => 'https://www.cifraclub.com.br/aline-barros/consagracao/',
                'selected_url' => null,
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertFormSet([
                'title' => 'Consagração',
                'artist' => 'Aline Barros',
                'original_key' => 'D',
            ]);
    }

    public function test_can_import_minor_key_from_modern_cifraclub_layout_into_create_song_form(): void
    {
        Http::fake([
            'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Lugar Secreto - Gabriela Rocha - Cifra Club</title></head>
<body>
    <div class="bentoCardContent" id="key">
        <p>Tom</p>
        <button aria-label="Diminuir tom"></button>
        <span><p>Am</p></span>
        <button aria-label="Aumentar tom"></button>
    </div>
    <script>
        self.__next_f.push([1,"\"timeSignature\":[\"1\",\"x\",\"x\",\"x\",\"2\",\"x\",\"x\",\"x\",\"3\",\"x\",\"x\",\"x\",\"4\",\"x\",\"x\",\"x\"],\"bpm\":70"]);
    </script>
    <pre>
[Intro] Am  F  C  G

Am            F
Tu és tudo o que eu mais quero
    </pre>
</body>
</html>
HTML, 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateSong::class)
            ->mountAction('searchWebChord')
            ->setActionData([
                'search_query' => 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/',
                'selected_url' => 'https://www.cifraclub.com.br/gabriela-rocha/lugar-secreto/',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertFormSet([
                'title' => 'Lugar Secreto',
                'artist' => 'Gabriela Rocha',
                'original_key' => 'Am',
                'bpm' => 70,
                'time_signature' => '4/4',
            ]);
    }

    public function test_can_import_capo_fret_and_youtube_url_into_create_song_form(): void
    {
        config(['services.youtube.key' => 'test-youtube-api-key']);

        Http::fake([
            'https://www.cifraclub.com.br/ana-nobrega/oceanos/' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Oceanos - Ana Nóbrega - Cifra Club</title></head>
<body>
    <span id="cifra_tom">Tom: <a>D</a></span>
    <span id="cifra_capo">Capotraste na <b>2ª</b> casa</span>
    <script>
        self.__next_f.push([1,"\"capo\":2,\"bpm\":64,\"timeSignature\":[\"1\",\"x\",\"x\",\"x\",\"2\",\"x\",\"x\",\"x\",\"3\",\"x\",\"x\",\"x\",\"4\",\"x\",\"x\",\"x\"]"]);
    </script>
    <pre>
[Intro] Bm  A/C#  D
Bm            A/C#
Tua voz me chama
    </pre>
</body>
</html>
HTML, 200),
            'https://www.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => [
                            'videoId' => 'oceanos123',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateSong::class)
            ->mountAction('searchWebChord')
            ->setActionData([
                'search_query' => 'https://www.cifraclub.com.br/ana-nobrega/oceanos/',
                'selected_url' => 'https://www.cifraclub.com.br/ana-nobrega/oceanos/',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertFormSet([
                'title' => 'Oceanos',
                'artist' => 'Ana Nóbrega',
                'original_key' => 'D',
                'bpm' => 64,
                'time_signature' => '4/4',
                'capo_fret' => 2,
                'youtube_url' => 'https://www.youtube.com/watch?v=oceanos123',
            ]);
    }
}
