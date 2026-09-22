<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Função')
                    ->description('Cadastre as funções e atribuições ministeriais (ex: Violão, Vocal, Técnico de Som)')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nome da Função')
                                ->placeholder('Ex: Guitarra Solo, Câmera')
                                ->required()
                                ->maxLength(80),

                            Select::make('category')
                                ->label('Categoria')
                                ->options(Role::CATEGORY_OPTIONS)
                                ->default(Role::CATEGORY_MUSICIAN)
                                ->required(),
                        ]),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->placeholder('Observações ou responsabilidades desta função')
                            ->rows(3)
                            ->maxLength(255),
                    ]),
            ]);
    }
}
