<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Default musical and technical roles.
     *
     * @var list<array{name: string, category: string, description: string}>
     */
    public const DEFAULT_ROLES = [
        // Músicos / Instrumentistas
        [
            'name' => 'Violão',
            'category' => Role::CATEGORY_MUSICIAN,
            'description' => 'Violão acústico / base harmônica',
        ],
        [
            'name' => 'Guitarra',
            'category' => Role::CATEGORY_MUSICIAN,
            'description' => 'Guitarra base / solo',
        ],
        [
            'name' => 'Baixo',
            'category' => Role::CATEGORY_MUSICIAN,
            'description' => 'Contrabaixo elétrico / acústico',
        ],
        [
            'name' => 'Bateria / Percussão',
            'category' => Role::CATEGORY_MUSICIAN,
            'description' => 'Bateria e instrumentos de percussão',
        ],
        [
            'name' => 'Teclado / Piano',
            'category' => Role::CATEGORY_MUSICIAN,
            'description' => 'Teclado, sintetizador e piano',
        ],
        [
            'name' => 'Saxofone / Metais',
            'category' => Role::CATEGORY_MUSICIAN,
            'description' => 'Instrumentos de sopro e metais',
        ],

        // Vocais
        [
            'name' => 'Voz Principal',
            'category' => Role::CATEGORY_VOCAL,
            'description' => 'Cantor / solista principal',
        ],
        [
            'name' => 'Backing Vocal',
            'category' => Role::CATEGORY_VOCAL,
            'description' => 'Vozes de apoio e harmonização vocal',
        ],
        [
            'name' => 'Ministro de Louvor',
            'category' => Role::CATEGORY_VOCAL,
            'description' => 'Direção do momento de louvor e adoração',
        ],

        // Equipe Técnica
        [
            'name' => 'Mesa de Som (FOH)',
            'category' => Role::CATEGORY_TECHNICIAN,
            'description' => 'Operador de áudio e mixagem',
        ],
        [
            'name' => 'Projeção / Transmissão',
            'category' => Role::CATEGORY_TECHNICIAN,
            'description' => 'Operador de projeção de letras e transmissão ao vivo',
        ],
        [
            'name' => 'Iluminação',
            'category' => Role::CATEGORY_TECHNICIAN,
            'description' => 'Operador de luz e ambientação visual',
        ],
        [
            'name' => 'Roadie / Palco',
            'category' => Role::CATEGORY_TECHNICIAN,
            'description' => 'Assistência de palco, cabos e instrumentos',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Organization::all() as $organization) {
            self::seedForOrganization($organization);
        }
    }

    /**
     * Seed default roles for a given organization without creating duplicates.
     */
    public static function seedForOrganization(Organization $organization): void
    {
        foreach (self::DEFAULT_ROLES as $roleData) {
            Role::withoutGlobalScopes()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'name' => $roleData['name'],
                ],
                [
                    'category' => $roleData['category'],
                    'description' => $roleData['description'],
                ]
            );
        }
    }
}
