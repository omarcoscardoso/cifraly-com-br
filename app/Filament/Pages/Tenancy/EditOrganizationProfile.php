<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tenancy;

use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class EditOrganizationProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Configurações da Organização';
    }

    public static function canView(Model $tenant): bool
    {
        return auth()->user()?->isOrgAdmin($tenant instanceof Organization ? $tenant : null) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Organização')
                    ->description('Dados de identificação e acesso da organização no Cifraly')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nome da Organização')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->label('Identificador (Slug)')
                                ->required()
                                ->maxLength(255)
                                ->unique(Organization::class, 'slug', ignoreRecord: true),
                        ]),

                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('invite_code')
                                ->label('Código de Convite da Organização')
                                ->disabled()
                                ->dehydrated(false)
                                ->extraInputAttributes(['style' => 'font-family: monospace; font-weight: bold; font-size: 1.1em; letter-spacing: 0.1em;'])
                                ->helperText('Compartilhe este código com músicos e voluntários para entrarem no app.'),

                            TextInput::make('invite_url')
                                ->label('Link de Convite')
                                ->formatStateUsing(function (): string {
                                    /** @var Organization|null $tenant */
                                    $tenant = Filament::getTenant();

                                    return $tenant?->invite_code ? route('organization.join', ['code' => $tenant->invite_code]) : '';
                                })
                                ->disabled()
                                ->dehydrated(false)
                                ->suffixAction(
                                    Action::make('copy_invite_url')
                                        ->icon(Heroicon::OutlinedClipboardDocument)
                                        ->tooltip('Copiar link de convite')
                                        ->action(function (): void {
                                            Notification::make()
                                                ->title('Link copiado com sucesso!')
                                                ->success()
                                                ->send();
                                        })
                                        ->extraAttributes(function (): array {
                                            /** @var Organization|null $tenant */
                                            $tenant = Filament::getTenant();
                                            $url = $tenant?->invite_code ? route('organization.join', ['code' => $tenant->invite_code]) : '';

                                            return [
                                                'x-on:click' => "navigator.clipboard.writeText('{$url}')",
                                            ];
                                        })
                                )
                                ->helperText('Envie este link direto para novos usuários entrarem com 1 clique.'),
                        ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copyInviteLink')
                ->label('Copiar Link de Convite')
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->color('primary')
                ->action(function (): void {
                    Notification::make()
                        ->title('Link copiado com sucesso!')
                        ->body('O link de convite foi copiado para a área de transferência.')
                        ->success()
                        ->send();
                })
                ->extraAttributes(function (): array {
                    /** @var Organization|null $tenant */
                    $tenant = Filament::getTenant();
                    $url = $tenant?->invite_code ? route('organization.join', ['code' => $tenant->invite_code]) : '';

                    return [
                        'x-on:click' => "navigator.clipboard.writeText('{$url}')",
                    ];
                })
                ->visible(fn (): bool => auth()->user()?->isOrgAdmin() ?? false),

            Action::make('regenerateInviteCode')
                ->label('Regenerar Código')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Gerar Novo Código de Convite')
                ->modalDescription('Tem certeza que deseja gerar um novo código de convite? O código e link anteriores deixarão de funcionar imediatamente.')
                ->action(function (): void {
                    /** @var Organization $tenant */
                    $tenant = Filament::getTenant();
                    $newCode = $tenant->regenerateInviteCode();

                    $this->fillForm();

                    Notification::make()
                        ->title('Código de Convite atualizado!')
                        ->body("O novo código da organização é: {$newCode}")
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => auth()->user()?->isOrgAdmin() ?? false),
        ];
    }
}
