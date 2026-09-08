<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Schemas;

use App\Models\Organization;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Organização')
                    ->description('Informações cadastrais e identificação da organização / igreja')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nome da Organização')
                                ->placeholder('Ex: Primeira Igreja Batista Central')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                                    if ($operation === 'create' && filled($state)) {
                                        $set('slug', Str::slug($state));
                                    }
                                }),

                            TextInput::make('slug')
                                ->label('Identificador (Slug)')
                                ->placeholder('Ex: pib-central')
                                ->required()
                                ->maxLength(255)
                                ->unique(Organization::class, 'slug', ignoreRecord: true)
                                ->helperText('Identificador exclusivo na URL do sistema (ex: /app/pib-central/...).'),
                        ]),
                    ]),
            ]);
    }
}
