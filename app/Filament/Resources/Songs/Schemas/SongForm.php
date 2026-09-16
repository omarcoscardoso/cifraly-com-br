<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Schemas;

use App\Filament\Resources\Songs\SongResource;
use App\Services\Music\ChordToChordProConverter;
use App\Services\Music\ChordTransposerService;
use App\Services\Music\StageChordFormatterService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                    ->description('Edite no formato de 2 linhas (acordes sobre a letra) ou no formato ChordPro [C]. O modo palco sempre renderiza com alinhamento perfeito!')
                    ->headerActions([
                        Action::make('convertToChordPro')
                            ->label('Converter p/ ChordPro [G]')
                            ->icon(Heroicon::OutlinedArrowsRightLeft)
                            ->color('amber')
                            ->tooltip('Converte a cifra de 2 linhas para ChordPro inline ancorando os acordes sobre cada sílaba')
                            ->action(function ($action, callable $get, callable $set): void {
                                $content = (string) $get('chordpro_content');
                                if (blank($content)) {
                                    Notification::make()->title('A cifra está vazia.')->warning()->send();

                                    return;
                                }

                                $converter = app(ChordToChordProConverter::class);
                                $converted = $converter->toChordPro($content);
                                $set('chordpro_content', $converted);

                                $livewire = $action->getLivewire();
                                if (is_object($livewire) && isset($livewire->data['chordpro_content'])) {
                                    $livewire->data['chordpro_content'] = $converted;
                                }

                                Notification::make()
                                    ->title('Cifra convertida para ChordPro!')
                                    ->body('Os acordes foram ancorados nas sílabas correspondentes.')
                                    ->success()
                                    ->send();
                            }),

                        Action::make('convertToTwoLines')
                            ->label('Converter p/ 2 Linhas')
                            ->icon(Heroicon::OutlinedBars3BottomLeft)
                            ->color('gray')
                            ->tooltip('Converte a cifra ChordPro de volta para 2 linhas (acordes acima da letra)')
                            ->action(function ($action, callable $get, callable $set): void {
                                $content = (string) $get('chordpro_content');
                                if (blank($content)) {
                                    Notification::make()->title('A cifra está vazia.')->warning()->send();

                                    return;
                                }

                                $converter = app(ChordToChordProConverter::class);
                                $converted = $converter->toTwoLine($content);
                                $set('chordpro_content', $converted);

                                $livewire = $action->getLivewire();
                                if (is_object($livewire) && isset($livewire->data['chordpro_content'])) {
                                    $livewire->data['chordpro_content'] = $converted;
                                }

                                Notification::make()
                                    ->title('Cifra convertida para 2 linhas!')
                                    ->body('A cifra agora está no formato visual de acordes acima da letra.')
                                    ->success()
                                    ->send();
                            }),

                        Action::make('previewStage')
                            ->label('Pré-visualizar no Palco')
                            ->icon(Heroicon::OutlinedEye)
                            ->color('primary')
                            ->modalHeading('Pré-visualização do Modo Palco')
                            ->modalDescription('Veja exatamente como a cifra será visualizada pelos músicos no palco.')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Fechar')
                            ->modalWidth('4xl')
                            ->modalContent(function (callable $get) {
                                $content = (string) ($get('chordpro_content') ?? '');
                                $key = (string) ($get('original_key') ?? 'C');
                                $formatter = app(StageChordFormatterService::class);
                                $formatted = $formatter->transposeAndFormat($content, $key, $key);

                                return view('filament.components.stage-preview-modal', [
                                    'title' => $get('title') ?: 'Música sem título',
                                    'artist' => $get('artist') ?: 'Compositor não informado',
                                    'key' => $key,
                                    'formattedChords' => $formatted,
                                ]);
                            }),
                    ])
                    ->schema([
                        Textarea::make('chordpro_content')
                            ->label('Conteúdo da Cifra')
                            ->rows(22)
                            ->extraInputAttributes([
                                'class' => 'font-mono text-sm leading-relaxed',
                                'style' => 'white-space: pre; overflow-x: auto; font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; tab-size: 4; -moz-tab-size: 4; letter-spacing: 0px !important;',
                                'wrap' => 'off',
                                'spellcheck' => 'false',
                            ])
                            ->placeholder("Você pode usar o formato de 2 linhas:\nC             G\nGraça maravilhosa\n\nOu o formato ChordPro inline:\n[C]Graça maravilh[G]osa"),
                    ]),
            ]);
    }
}
