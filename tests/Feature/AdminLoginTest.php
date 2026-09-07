<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('fi-sc-form');
        $response->assertSee('/css/filament/filament/app.css');
    }

    public function test_admin_login_page_renders_google_login_button(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Entrar com Google');
        $response->assertSee(route('auth.google.redirect'));
    }

    public function test_filament_css_asset_file_exists(): void
    {
        $this->assertFileExists(public_path('css/filament/filament/app.css'));
    }

    public function test_authenticated_user_with_organization_redirects_to_tenant_dashboard(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['slug' => 'minha-igreja']);
        $user->organizations()->attach($org);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect('/admin/minha-igreja');

        $dashboardResponse = $this->actingAs($user)->get('/admin/minha-igreja');
        $dashboardResponse->assertStatus(200);
    }
}
