<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\RelationManagers;

use App\Actions\Events\AddTeamToEventRosterAction;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RostersRelationManager extends RelationManager
{
    protected static string $relationship = 'rosters';

    protected static ?string $title = 'Escala de Membros';

    protected static ?string $modelLabel = 'Membro Escalado';

    protected static ?string $pluralModelLabel = 'Escala de Membros';

    public function table(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('role.name')
                    ->label('Função / Instrumento')
                    ->badge()
                    ->color(fn (EventRoster $record): string => $record->role ? (Role::CATEGORY_COLORS[$record->role->category] ?? 'primary') : 'gray')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => EventRoster::STATUS_OPTIONS[$state] ?? $state ?? '-')
                    ->color(fn (?string $state): string => EventRoster::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),

                TextColumn::make('responded_at')
                    ->label('Respondido em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Aguardando resposta')
                    ->sortable(),

                TextColumn::make('decline_reason')
                    ->label('Motivo da Recusa')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('addTeam')
                    ->label('Escalar Equipe Completa')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->color('primary')
                    ->modalHeading('Escalar Membros de uma Equipe')
                    ->modalDescription('Selecione uma equipe cadastrada para adicionar automaticamente todos os seus membros com suas respectivas funções padrão.')
                    ->modalSubmitActionLabel('Escalar Todos')
                    ->form([
                        Select::make('team_id')
                            ->label('Equipe')
                            ->options(function (): array {
                                $tenantId = Filament::getTenant()?->id;

                                if (! $tenantId) {
                                    return [];
                                }

                                return Team::where('organization_id', $tenantId)->pluck('name', 'id')->toArray();
                            })
                            ->default(fn (RelationManager $livewire): ?int => $livewire->getOwnerRecord()->team_id)
                            ->searchable()
                            ->required(),

                        Select::make('fallback_role_id')
                            ->label('Função Alternativa (caso o membro não tenha função na equipe)')
                            ->options(function (): array {
                                $tenantId = Filament::getTenant()?->id;

                                if (! $tenantId) {
                                    return [];
                                }

                                return Role::where('organization_id', $tenantId)->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->nullable()
                            ->placeholder('Selecione uma função ou manter padrão da organização'),

                        Toggle::make('skip_existing')
                            ->label('Pular membros que já estão na escala')
                            ->default(true)
                            ->helperText('Evita duplicar voluntários que já foram escalados para este evento.'),
                    ])
                    ->action(function (array $data, RelationManager $livewire, AddTeamToEventRosterAction $rosterAction): void {
                        /** @var Event $event */
                        $event = $livewire->getOwnerRecord();
                        $team = Team::find($data['team_id']);

                        if (! $team || $team->teamMembers()->count() === 0) {
                            Notification::make()
                                ->title('A equipe selecionada não possui membros cadastrados.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $result = $rosterAction->execute(
                            event: $event,
                            team: $team,
                            fallbackRoleId: $data['fallback_role_id'] ?? null,
                            skipExisting: $data['skip_existing'] ?? true,
                        );

                        Notification::make()
                            ->title("{$result['added']} membro(s) da equipe '{$team->name}' adicionados à escala!")
                            ->body($result['skipped'] > 0 ? "({$result['skipped']} membro(s) já estavam escalados e não foram duplicados)" : null)
                            ->success()
                            ->send();
                    }),

                CreateAction::make()
                    ->label('Adicionar Membro')
                    ->modalHeading('Escalar Voluntário / Músico')
                    ->form([
                        Select::make('user_id')
                            ->label('Membro / Voluntário')
                            ->options(function (RelationManager $livewire): array {
                                $tenant = Filament::getTenant();

                                if (! $tenant) {
                                    return [];
                                }

                                /** @var Event $event */
                                $event = $livewire->getOwnerRecord();
                                $teamId = $event->team_id;

                                $users = $tenant->users()->get();

                                if ($teamId) {
                                    $teamUserIds = TeamMember::where('team_id', $teamId)->pluck('user_id')->toArray();
                                    $users = $users->sortByDesc(fn (User $user): bool => in_array($user->id, $teamUserIds, true));
                                }

                                return $users->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, RelationManager $livewire): void {
                                if (! $state) {
                                    return;
                                }

                                /** @var Event $event */
                                $event = $livewire->getOwnerRecord();

                                if ($event->team_id) {
                                    $teamMember = TeamMember::where('team_id', $event->team_id)
                                        ->where('user_id', $state)
                                        ->first();

                                    if ($teamMember?->default_role_id) {
                                        $set('role_id', $teamMember->default_role_id);
                                    }
                                }
                            })
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                                    $tenant = Filament::getTenant();

                                    if (! $tenant || ! $tenant->users()->where('users.id', $value)->exists()) {
                                        $fail('O usuário selecionado não pertence à organização atual.');
                                    }
                                },
                            ]),

                        Select::make('role_id')
                            ->label('Função / Instrumento')
                            ->options(function (): array {
                                $tenantId = Filament::getTenant()?->id;

                                if (! $tenantId) {
                                    return [];
                                }

                                return Role::where('organization_id', $tenantId)->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                                    $tenantId = Filament::getTenant()?->id;

                                    if (! Role::where('organization_id', $tenantId)->where('id', $value)->exists()) {
                                        $fail('A função selecionada não pertence à organização atual.');
                                    }
                                },
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->options(EventRoster::STATUS_OPTIONS)
                            ->default(EventRoster::STATUS_PENDING)
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['organization_id'] = Filament::getTenant()?->id;

                        if (empty($data['confirmation_token'])) {
                            $data['confirmation_token'] = Str::random(40);
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('Link WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->modalHeading('Convite para Escala (WhatsApp)')
                    ->modalWidth('md')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->form(function (EventRoster $record): array {
                        $user = $record->user;
                        $event = $record->event;
                        $dateFormatted = $event?->starts_at ? $event->starts_at->format('d/m/Y \à\s H:i') : 'Data a definir';
                        $confirmationUrl = route('roster.confirm', ['token' => $record->confirmation_token]);
                        $message = "Olá {$user?->name}, você foi escalado para {$event?->title} no dia {$dateFormatted}. Confirme sua presença aqui: {$confirmationUrl}";

                        $rawPhone = preg_replace('/\D+/', '', (string) ($user?->phone ?? ''));
                        $cleanPhone = $rawPhone;
                        if (strlen($rawPhone) === 10 || strlen($rawPhone) === 11) {
                            $cleanPhone = '55'.$rawPhone;
                        }

                        $whatsappUrl = $cleanPhone !== ''
                            ? "https://wa.me/{$cleanPhone}?text=".urlencode($message)
                            : 'https://wa.me/?text='.urlencode($message);

                        return [
                            TextInput::make('whatsapp_url')
                                ->label('Link do WhatsApp')
                                ->default($whatsappUrl)
                                ->readOnly()
                                ->helperText('Copie o link acima ou clique em "Abrir no WhatsApp".'),

                            Textarea::make('message_preview')
                                ->label('Mensagem Formatada')
                                ->default($message)
                                ->rows(4)
                                ->readOnly(),
                        ];
                    })
                    ->extraModalFooterActions([
                        Action::make('openWhatsapp')
                            ->label('Abrir no WhatsApp')
                            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                            ->color('success')
                            ->url(function (EventRoster $record): string {
                                $user = $record->user;
                                $event = $record->event;
                                $dateFormatted = $event?->starts_at ? $event->starts_at->format('d/m/Y \à\s H:i') : 'Data a definir';
                                $confirmationUrl = route('roster.confirm', ['token' => $record->confirmation_token]);
                                $message = "Olá {$user?->name}, você foi escalado para {$event?->title} no dia {$dateFormatted}. Confirme sua presença aqui: {$confirmationUrl}";

                                $rawPhone = preg_replace('/\D+/', '', (string) ($user?->phone ?? ''));
                                $cleanPhone = $rawPhone;
                                if (strlen($rawPhone) === 10 || strlen($rawPhone) === 11) {
                                    $cleanPhone = '55'.$rawPhone;
                                }

                                return $cleanPhone !== ''
                                    ? "https://wa.me/{$cleanPhone}?text=".urlencode($message)
                                    : 'https://wa.me/?text='.urlencode($message);
                            }, shouldOpenInNewTab: true),
                    ]),

                EditAction::make()
                    ->label('Editar')
                    ->form([
                        Select::make('role_id')
                            ->label('Função / Instrumento')
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
                            ->required(),

                        Textarea::make('decline_reason')
                            ->label('Motivo da Recusa')
                            ->rows(2)
                            ->nullable(),
                    ]),

                DeleteAction::make()
                    ->label('Remover'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
