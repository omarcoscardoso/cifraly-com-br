<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('name')
                    ->label('Organização')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('Identificador (Slug)')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('invite_code')
                    ->label('Código de Convite')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Membros')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label('Equipes')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('songs_count')
                    ->counts('songs')
                    ->label('Músicas')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('events_count')
                    ->counts('events')
                    ->label('Eventos')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->modalHeading('Excluir Organização')
                    ->modalDescription('Tem certeza que deseja excluir esta organização? Todas as equipes, eventos, músicas e dados vinculados serão permanentemente removidos.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
