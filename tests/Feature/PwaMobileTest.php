<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_webmanifest_exists_and_is_valid_json(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);

        $content = file_get_contents($manifestPath);
        $this->assertIsString($content);

        $json = json_decode($content, true);
        $this->assertIsArray($json);
        $this->assertSame('Cifraly', $json['short_name']);
        $this->assertSame('/app', $json['start_url']);
        $this->assertSame('standalone', $json['display']);
        $this->assertSame('#0f172a', $json['theme_color']);
        $this->assertSame('#0f172a', $json['background_color']);
    }

    public function test_manifest_has_all_required_icon_sizes_and_purposes(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $json = json_decode(file_get_contents($manifestPath), true);

        $icons = $json['icons'] ?? [];
        $this->assertNotEmpty($icons);

        $sizes = array_column($icons, 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);
        $this->assertContains('180x180', $sizes);

        $purposes = array_column($icons, 'purpose');
        $this->assertContains('maskable', $purposes);
    }

    public function test_all_pwa_icons_and_apple_touch_icon_exist_on_filesystem(): void
    {
        $this->assertFileExists(public_path('icons/icon.svg'));
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertFileExists(public_path('icons/icon-192x192.png'));
        $this->assertFileExists(public_path('icons/icon-512x512.png'));
        $this->assertFileExists(public_path('icons/icon-maskable-192x192.png'));
        $this->assertFileExists(public_path('icons/icon-maskable-512x512.png'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));
    }

    public function test_service_worker_file_exists_and_contains_pwa_architecture(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $swContent = file_get_contents($swPath);
        $this->assertIsString($swContent);

        // Core SW Lifecycle
        $this->assertStringContainsString("addEventListener('install'", $swContent);
        $this->assertStringContainsString("addEventListener('activate'", $swContent);
        $this->assertStringContainsString("addEventListener('fetch'", $swContent);

        // Offline fallback
        $this->assertStringContainsString('/offline.html', $swContent);

        // Background Sync capability
        $this->assertStringContainsString("addEventListener('sync'", $swContent);

        // Push Notifications capability
        $this->assertStringContainsString("addEventListener('push'", $swContent);
        $this->assertStringContainsString("addEventListener('notificationclick'", $swContent);
    }

    public function test_offline_fallback_page_renders_with_cifraly_branding(): void
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertIsString($content);
        $this->assertStringContainsString('Você está offline', $content);
        $this->assertStringContainsString('Cifraly', $content);
        $this->assertStringContainsString('Tentar Novamente', $content);
    }

    public function test_home_page_includes_pwa_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('/manifest.webmanifest', false);
        $response->assertSee('apple-mobile-web-app-capable', false);
        $response->assertSee('viewport-fit=cover', false);
    }

    public function test_app_login_page_renders_pwa_meta_tags_via_filament_hook(): void
    {
        $response = $this->get('/app/login');

        $response->assertStatus(200);
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('/manifest.webmanifest', false);
        $response->assertSee('apple-mobile-web-app-title', false);
        $response->assertSee('sw.js', false);
    }

    public function test_stage_view_includes_pwa_meta_tags_and_safe_area_viewport(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['slug' => 'igreja-pwa']);
        $user->organizations()->attach($org);

        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title' => 'Culto Teste PWA',
        ]);

        $response = $this->actingAs($user)->get("/app/igreja-pwa/events/{$event->id}/stage");

        $response->assertStatus(200);
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('/manifest.webmanifest', false);
        $response->assertSee('viewport-fit=cover', false);
    }

    public function test_official_brand_svg_assets_exist_and_are_valid(): void
    {
        $logoPath = public_path('images/logo.svg');
        $iconPath = public_path('icons/icon.svg');
        $faviconPath = public_path('favicon.svg');

        $this->assertFileExists($logoPath);
        $this->assertFileExists($iconPath);
        $this->assertFileExists($faviconPath);

        $logoSvg = (string) file_get_contents($logoPath);
        $this->assertStringContainsString('cifralyPickGrad', $logoSvg);
        $this->assertStringContainsString('Cifra', $logoSvg);
        $this->assertStringContainsString('Ly', $logoSvg);
        $this->assertStringContainsString('SETLIST &amp; ESCALAS', $logoSvg);

        $iconSvg = (string) file_get_contents($iconPath);
        $this->assertStringContainsString('cifralyPickGrad', $iconSvg);
    }

    public function test_dark_mode_is_default_on_welcome_and_renders_brand_logo(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('class="dark', false);
        $response->assertSee('bg-slate-950', false);
        $response->assertSee('brandPickGrad', false);
        $response->assertSee('SETLIST &amp; ESCALAS', false);
    }

    public function test_filament_app_login_renders_cifraly_brand_logo_and_dark_mode(): void
    {
        $response = $this->get('/app/login');

        $response->assertStatus(200);
        $response->assertSee('brandPickGrad', false);
        $response->assertSee('SETLIST &amp; ESCALAS', false);
    }
}
