<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use App\Models\EventRoster;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('team.name')
                    ->label('Equipe')
                    ->placeholder('Geral / Sem equipe')
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Data e Horário')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Event::STATUS_OPTIONS[$state] ?? $state ?? '-')
                    ->color(fn (?string $state): string => Event::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),

                TextColumn::make('roster_summary')
                    ->label('Escala')
                    ->badge()
                    ->color('info')
                    ->state(function (Event $record): string {
                        $total = $record->rosters()->count();
                        $confirmed = $record->rosters()->where('status', EventRoster::STATUS_CONFIRMED)->count();

                        return "{$confirmed}/{$total} confirmados";
                    }),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Event::STATUS_OPTIONS),

                Filter::make('starts_at')
                    ->label('Data do Evento')
                    ->form([
                        DatePicker::make('starts_from')->label('De'),
                        DatePicker::make('starts_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['starts_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('starts_at', '>=', $date),
                            )
                            ->when(
                                $data['starts_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('starts_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('stageView')
                    ->label('Modo Palco')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('warning')
                    ->url(fn (Event $record): string => route('events.stage', [
                        'organization' => Filament::getTenant(),
                        'event' => $record,
                    ]))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
