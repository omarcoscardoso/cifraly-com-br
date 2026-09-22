<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Schemas;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Songs\SongResource;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Role;
use App\Models\Song;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === 'create'
            || ($schema->getLivewire() instanceof CreateEvent);

        if ($isCreate) {
            return $schema
                ->components([
                    Tabs::make('EventCreateTabs')
                        ->tabs([
                            Tab::make('Dados do Evento')
                                ->icon(Heroicon::OutlinedCalendarDays)
                                ->schema(static::getEventDetailsSchema()),

                            Tab::make('Setlist do Repertório')
                                ->icon(Heroicon::OutlinedMusicalNote)
                                ->schema([
                                    Callout::make('Repertório do Evento')
                                        ->description('Adicione as músicas que serão tocadas neste evento. Você pode ajustar o tom e observações do arranjo agora ou refinar após criar o evento.')
                                        ->info(),

                                    Repeater::make('eventSongs')
                                        ->relationship('eventSongs')
                                        ->defaultItems(0)
                                        ->label('Músicas no Repertório')
                                        ->itemLabel(function (array $state): ?string {
                                            if (empty($state['song_id'])) {
                                                return null;
                                            }

                                            return Song::find($state['song_id'])?->title;
                                        })
                                        ->schema([
                                            Grid::make(['default' => 1, 'md' => 3])->schema([
                                                Select::make('song_id')
                                                    ->label('Música')
                                                    ->placeholder('Selecione uma música...')
                                                    ->options(function (): array {
                                                        $tenant = Filament::getTenant();

                                                        if (! $tenant) {
                                                            return [];
                                                        }

                                                        return Song::where('organization_id', $tenant->id)->pluck('title', 'id')->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set): void {
                                                        if (! $state) {
                                                            $set('target_key', 'C');

                                                            return;
                                                        }

                                                        $song = Song::find($state);

                                                        if ($song?->original_key) {
                                                            $set('target_key', $song->original_key);
                                                        }
                                                    }),

                                                Select::make('target_key')
                                                    ->label('Tom no Evento')
                                                    ->options(SongResource::KEY_OPTIONS)
                                                    ->default('C')
                                                    ->required(),

                                                TextInput::make('arrangement_notes')
                                                    ->label('Arranjo / Observações')
                                                    ->placeholder('Ex: Tom original, versão acústica...')
                                                    ->maxLength(255),
                                            ]),
                                        ])
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                            $data['organization_id'] = Filament::getTenant()?->id;

                                            return $data;
                                        })
                                        ->orderColumn('order_index')
                                        ->reorderable()
                                        ->addActionLabel('Adicionar Música ao Repertório'),
                                ]),

                            Tab::make('Escala de Membros')
                                ->icon(Heroicon::OutlinedUserGroup)
                                ->schema([
                                    Callout::make('Escala de Membros')
                                        ->description('Se você selecionou uma Equipe na aba "Dados do Evento", todos os seus membros serão escalados automaticamente com suas funções padrão. Se desejar, você também pode adicionar voluntários extras abaixo.')
                                        ->info(),

                                    Repeater::make('rosters')
                                        ->relationship('rosters')
                                        ->defaultItems(0)
                                        ->label('Membros Escalados')
                                        ->itemLabel(function (array $state): ?string {
                                            if (empty($state['user_id'])) {
                                                return null;
                                            }

                                            return User::find($state['user_id'])?->name;
                                        })
                                        ->schema([
                                            Grid::make(['default' => 1, 'md' => 3])->schema([
                                                Select::make('user_id')
                                                    ->label('Membro / Voluntário')
                                                    ->placeholder('Selecione o voluntário...')
                                                    ->options(function (): array {
                                                        $tenant = Filament::getTenant();

                                                        if (! $tenant) {
                                                            return [];
                                                        }

                                                        return $tenant->users()->pluck('name', 'users.id')->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('role_id')
                                                    ->label('Função / Instrumento')
                                                    ->placeholder('Selecione a função...')
                                                    ->options(function (): array {
                                                        $tenantId = Filament::getTenant()?->id;

                                                        if (! $tenantId) {
                                                            return [];
                                                        }

                                                        return Role::where('organization_id', $tenantId)->pluck('name', 'id')->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('status')
                                                    ->label('Status')
                                                    ->options(EventRoster::STATUS_OPTIONS)
                                                    ->default(EventRoster::STATUS_PENDING)
                                                    ->required(),
                                            ]),
                                        ])
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                            $data['organization_id'] = Filament::getTenant()?->id;

                                            if (empty($data['confirmation_token'])) {
                                                $data['confirmation_token'] = Str::random(40);
                                            }

                                            return $data;
                                        })
                                        ->addActionLabel('Adicionar Voluntário à Escala'),
                                ]),
                        ]),
                ]);
        }

        return $schema->components(static::getEventDetailsSchema());
    }

    /**
     * @return array<Component>
     */
    public static function getEventDetailsSchema(): array
    {
        return [
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
        ];
    }
}
