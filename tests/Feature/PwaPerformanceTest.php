<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_admin_panel_has_spa_mode_enabled_with_stage_exceptions(): void
    {
        $panel = Filament::getPanel('app');

        $this->assertTrue($panel->hasSpaMode(), 'Filament app panel deve ter o modo SPA ativado.');
        $this->assertContains('*/stage*', $panel->getSpaUrlExceptions(), 'As rotas de palco devem ser excluídas da navegação SPA para manter layout independente.');
    }

    public function test_mobile_bottom_nav_renders_wire_navigate_hover_for_prefetching(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['slug' => 'pwa-org']);
        $user->organizations()->attach($org, ['role' => Organization::ROLE_ADMIN]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($org, isQuiet: true);

        $response = $this->get('/app/pwa-org');

        $response->assertSuccessful();
        $response->assertSee('wire:navigate.hover', false);
    }

    public function test_service_worker_precaches_dynamic_vite_manifest_and_does_not_block_livewire_scripts(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = (string) file_get_contents($swPath);

        // Precache do manifesto do Vite
        $this->assertStringContainsString('/build/manifest.json', $content);

        // Regra refinada de Livewire: apenas endpoints mutacionais bloqueados
        $this->assertStringContainsString('/livewire\\/(update|upload-file|preview-file)', $content);
        $this->assertStringNotContainsString('/livewire(\\/|$)/', $content);

        // Versão do cache atualizada
        $this->assertStringContainsString('cifraly-v1.0.7', $content);
    }

    public function test_stage_views_use_request_animation_frame_for_smooth_auto_scroll(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['slug' => 'stage-smooth-org']);
        $user->organizations()->attach($org, ['role' => Organization::ROLE_ADMIN]);

        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title' => 'Evento Teste Smooth Scroll',
        ]);

        $response = $this->actingAs($user)->get("/app/stage-smooth-org/events/{$event->id}/stage");
        $response->assertSuccessful();
        $response->assertSee('requestAnimationFrame', false);
        $response->assertSee('cancelAnimationFrame', false);
        $response->assertSee('animationFrameId', false);
    }

    public function test_altar_metronome_uses_web_worker_for_background_timing(): void
    {
        $view = view('components.altar-metronome')->render();

        $this->assertStringContainsString('initWorker', $view);
        $this->assertStringContainsString('timerWorker', $view);
        $this->assertStringContainsString('new Worker', $view);
        $this->assertStringContainsString('x-data="altarMetronome()"', $view);
        $this->assertStringContainsString('window.altarMetronome', $view);
    }

    public function test_manifest_and_meta_tags_use_official_altar_dark_theme_color(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $json = json_decode((string) file_get_contents($manifestPath), true);

        $this->assertSame('#08080a', $json['theme_color']);
        $this->assertSame('#08080a', $json['background_color']);

        $metaView = view('pwa.meta')->render();
        $this->assertStringContainsString('content="#08080a"', $metaView);
    }

    public function test_manifest_has_explicit_share_target_enctype(): void
    {
        foreach (['manifest.json', 'manifest.webmanifest'] as $filename) {
            $path = public_path($filename);
            $json = json_decode((string) file_get_contents($path), true);
            $this->assertArrayHasKey('share_target', $json);
            $this->assertSame('application/x-www-form-urlencoded', $json['share_target']['enctype'] ?? null);
        }
    }
}
