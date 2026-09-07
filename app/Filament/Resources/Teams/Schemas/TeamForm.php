<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Equipe')
                    ->description('Cadastre e organize as equipes de ministério (ex: Louvor Domingo Manhã, Equipe de Áudio)')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome da Equipe')
                            ->placeholder('Ex: Louvor Domingo Manhã, Equipe de Mídia')
                            ->required()
                            ->maxLength(100),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->placeholder('Observações ou finalidade desta equipe')
                            ->rows(3)
                            ->maxLength(255),
                    ]),
            ]);
    }
}
