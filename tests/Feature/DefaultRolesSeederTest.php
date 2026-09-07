<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultRolesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatically_creates_13_default_roles_when_organization_is_created(): void
    {
        $organization = Organization::create([
            'name' => 'Comunidade da Graça',
            'slug' => 'comunidade-da-graca',
        ]);

        $roles = Role::where('organization_id', $organization->id)->get();

        $this->assertCount(13, $roles);

        // Check musicians
        $musicians = $roles->where('category', Role::CATEGORY_MUSICIAN);
        $this->assertCount(6, $musicians);
        $this->assertTrue($musicians->contains('name', 'Violão'));
        $this->assertTrue($musicians->contains('name', 'Guitarra'));
        $this->assertTrue($musicians->contains('name', 'Baixo'));
        $this->assertTrue($musicians->contains('name', 'Bateria / Percussão'));
        $this->assertTrue($musicians->contains('name', 'Teclado / Piano'));
        $this->assertTrue($musicians->contains('name', 'Saxofone / Metais'));

        // Check vocals
        $vocals = $roles->where('category', Role::CATEGORY_VOCAL);
        $this->assertCount(3, $vocals);
        $this->assertTrue($vocals->contains('name', 'Voz Principal'));
        $this->assertTrue($vocals->contains('name', 'Backing Vocal'));
        $this->assertTrue($vocals->contains('name', 'Ministro de Louvor'));

        // Check technicians
        $technicians = $roles->where('category', Role::CATEGORY_TECHNICIAN);
        $this->assertCount(4, $technicians);
        $this->assertTrue($technicians->contains('name', 'Mesa de Som (FOH)'));
        $this->assertTrue($technicians->contains('name', 'Projeção / Transmissão'));
        $this->assertTrue($technicians->contains('name', 'Iluminação'));
        $this->assertTrue($technicians->contains('name', 'Roadie / Palco'));
    }

    public function test_repeated_execution_does_not_duplicate_roles(): void
    {
        $organization = Organization::create([
            'name' => 'Segunda Igreja',
            'slug' => 'segunda-igreja',
        ]);

        $this->assertCount(13, Role::where('organization_id', $organization->id)->get());

        // Run seeder again explicitly
        DefaultRolesSeeder::seedForOrganization($organization);

        $this->assertCount(13, Role::where('organization_id', $organization->id)->get());
    }
}
