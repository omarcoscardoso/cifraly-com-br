<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\Song;
use App\Tenancy\TenancyContext;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyContextTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenancyContext::clear();
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_tenancy_context_scopes_models_without_filament(): void
    {
        Filament::setTenant(null); // Ensure Filament has no tenant

        $org1 = Organization::factory()->create(['name' => 'Org 1']);
        $org2 = Organization::factory()->create(['name' => 'Org 2']);

        $song1 = Song::factory()->create(['organization_id' => $org1->id, 'title' => 'Música Org 1']);
        $song2 = Song::factory()->create(['organization_id' => $org2->id, 'title' => 'Música Org 2']);

        // Outside context: sees all
        $this->assertCount(2, Song::all());

        // Inside Org 1 context: sees only Org 1
        TenancyContext::run($org1, function () use ($song1, $song2) {
            $songs = Song::all();
            $this->assertCount(1, $songs);
            $this->assertTrue($songs->contains($song1));
            $this->assertFalse($songs->contains($song2));
        });

        // Outside context again: restored
        $this->assertNull(TenancyContext::get());
        $this->assertCount(2, Song::all());
    }

    public function test_tenancy_context_automatically_assigns_organization_id_on_create(): void
    {
        Filament::setTenant(null);

        $org = Organization::factory()->create();

        TenancyContext::run($org, function () use ($org) {
            $song = Song::create([
                'title' => 'Música Sem Org Explícita',
                'original_key' => 'C',
            ]);

            $this->assertSame($org->id, $song->organization_id);
        });
    }
}
