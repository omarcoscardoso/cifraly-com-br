<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Schemas;

use App\Filament\Resources\Songs\SongResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SongForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Música')
                    ->description('Dados gerais e identificação da música')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Título')
                                ->placeholder('Ex: Graça Maravilhosa')
                                ->required()
                                ->maxLength(200),

                            TextInput::make('artist')
                                ->label('Artista / Banda')
                                ->placeholder('Ex: John Newton')
                                ->maxLength(150),
                        ]),

                        Grid::make(['default' => 2, 'md' => 4])->schema([
                            Select::make('original_key')
                                ->label('Tom Original')
                                ->options(SongResource::KEY_OPTIONS)
                                ->default('C')
                                ->required()
                                ->searchable(),

                            TextInput::make('capo_fret')
                                ->label('Capotraste')
                                ->placeholder('Sem capo')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(12)
                                ->helperText('Casa do braço (1 a 12)'),

                            TextInput::make('bpm')
                                ->label('BPM')
                                ->placeholder('Ex: 120')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(300),

                            Select::make('time_signature')
                                ->label('Fórmula de Compasso')
                                ->options(SongResource::TIME_SIGNATURE_OPTIONS)
                                ->default('4/4')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('spotify_url')
                                ->label('Link do Spotify')
                                ->placeholder('https://open.spotify.com/track/...')
                                ->url()
                                ->maxLength(255),

                            TextInput::make('youtube_url')
                                ->label('Link do YouTube')
                                ->placeholder('https://www.youtube.com/watch?v=...')
                                ->url()
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Cifra Inicial (Versão Padrão)')
                    ->description('Insira a cifra com os acordes sobre a letra (uma linha com os acordes e a linha seguinte com a letra)')
                    ->schema([
                        Textarea::make('chordpro_content')
                            ->label('Conteúdo da Cifra')
                            ->rows(18)
                            ->extraInputAttributes(['class' => 'font-mono text-sm leading-relaxed'])
                            ->placeholder("Exemplo:\n[Intro] G  D  Em  C\n\nG             D\nGraça maravilhosa\nEm            C\nQue salvou a mim\nEm            C\nPerdido eu estava\nD             G\nMas me encontrou"),
                    ]),
            ]);
    }
}
