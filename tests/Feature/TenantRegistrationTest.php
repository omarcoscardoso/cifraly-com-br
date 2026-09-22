<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Tenancy\RegisterOrganization;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_without_tenant_is_redirected_to_registration(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/app');

        $response->assertRedirect('/app/new');

        $registrationPageResponse = $this->actingAs($user)->get('/app/new');
        $registrationPageResponse->assertStatus(200);
        $registrationPageResponse->assertSee('Cadastrar Organização');
    }

    public function test_user_can_register_organization_and_is_attached_to_it(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::actingAs($user)
            ->test(RegisterOrganization::class)
            ->fillForm([
                'action_type' => 'create',
                'name' => 'Comunidade da Fé',
                'slug' => 'comunidade-da-fe',
            ])
            ->call('register')
            ->assertHasNoFormErrors()
            ->assertRedirect('/app/comunidade-da-fe');

        $organization = Organization::where('slug', 'comunidade-da-fe')->first();
        $this->assertNotNull($organization);
        $this->assertSame('Comunidade da Fé', $organization->name);
        $this->assertTrue($user->fresh()->organizations->contains($organization));
    }
}
