<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Evento')
                    ->description('Informações gerais, data, horário e equipe do evento')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Título do Evento')
                                ->placeholder('Ex: Culto de Domingo - Manhã, Show Acústico')
                                ->required()
                                ->maxLength(150),

                            Select::make('team_id')
                                ->label('Equipe')
                                ->placeholder('Geral / Sem equipe fixa')
                                ->options(function (): array {
                                    $tenant = Filament::getTenant();

                                    if (! $tenant) {
                                        return [];
                                    }

                                    return Team::where('organization_id', $tenant->id)->pluck('name', 'id')->toArray();
                                })
                                ->searchable()
                                ->helperText('Ao selecionar uma equipe, todos os membros serão incluídos automaticamente na escala.')
                                ->nullable(),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('status')
                                ->label('Status')
                                ->options(Event::STATUS_OPTIONS)
                                ->default(Event::STATUS_DRAFT)
                                ->required(),

                            DateTimePicker::make('starts_at')
                                ->label('Data e Horário do Evento')
                                ->displayFormat('d/m/Y H:i')
                                ->native(false)
                                ->seconds(false)
                                ->required(),

                            DateTimePicker::make('rehearsal_at')
                                ->label('Passagem de Som / Ensaio')
                                ->displayFormat('d/m/Y H:i')
                                ->native(false)
                                ->seconds(false)
                                ->nullable(),
                        ]),

                        Textarea::make('notes')
                            ->label('Observações Gerais')
                            ->placeholder('Instruções adicionais para a equipe e voluntários...')
                            ->rows(3)
                            ->nullable(),
                    ]),
            ]);
    }
}
