<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Schemas;

use App\Filament\Resources\Songs\SongResource;
use App\Services\Music\ChordTransposerService;
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
            ->columns(1)
            ->components([
                Section::make('Informações da Música')
                    ->columnSpanFull()
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
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (?string $state, ?string $old, callable $set, callable $get): void {
                                    if (! $state || ! $old || $state === $old) {
                                        return;
                                    }

                                    $content = $get('chordpro_content');

                                    if (blank($content)) {
                                        return;
                                    }

                                    try {
                                        $transposer = app(ChordTransposerService::class);
                                        $transposed = $transposer->transpose((string) $content, $old, $state);
                                        $set('chordpro_content', $transposed);
                                    } catch (\Throwable) {
                                        // Mantém conteúdo se não for possível transpor
                                    }
                                }),

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
                    ->columnSpanFull()
                    ->description('Insira a cifra com os acordes sobre a letra (uma linha com os acordes e a linha seguinte com a letra)')
                    ->schema([
                        Textarea::make('chordpro_content')
                            ->label('Conteúdo da Cifra')
                            ->rows(22)
                            ->extraInputAttributes([
                                'class' => 'font-mono text-sm leading-relaxed',
                                'style' => 'white-space: pre; overflow-x: auto; font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; tab-size: 4; -moz-tab-size: 4;',
                                'wrap' => 'off',
                                'spellcheck' => 'false',
                            ])
                            ->placeholder("Exemplo:\n[Intro] G  D  Em  C\n\nG             D\nGraça maravilhosa\nEm            C\nQue salvou a mim\nEm            C\nPerdido eu estava\nD             G\nMas me encontrou"),
                    ]),
            ]);
    }
}
