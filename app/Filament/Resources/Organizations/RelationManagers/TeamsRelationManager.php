<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    protected static ?string $title = 'Equipes da Organização';

    protected static ?string $modelLabel = 'Equipe';

    protected static ?string $pluralModelLabel = 'Equipes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome da Equipe')
                    ->placeholder('Ex: Louvor Principal, Ministério Infantil')
                    ->required()
                    ->maxLength(100),

                Textarea::make('description')
                    ->label('Descrição')
                    ->placeholder('Finalidade ou observações da equipe...')
                    ->rows(3)
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Equipe')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Membros')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->placeholder('-')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nova Equipe'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),

                DeleteAction::make()
                    ->label('Excluir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
