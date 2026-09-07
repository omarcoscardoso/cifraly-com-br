<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Actions;

use App\Services\Music\ChordScraperService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;
use Throwable;

class ImportChordFromWebAction
{
    public static function make(): Action
    {
        return Action::make('searchWebChord')
            ->label('Buscar Cifra na Web')
            ->icon(Heroicon::OutlinedGlobeAlt)
            ->color('primary')
            ->modalHeading('Buscar e Importar Cifra da Web')
            ->modalDescription('Digite o nome da música e artista para buscar automaticamente ou cole a URL direta da cifra.')
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('Importar Cifra')
            ->form([
                TextInput::make('search_query')
                    ->label('Música / Artista ou URL direta')
                    ->placeholder('Ex: Gabriela Rocha Lugar Secreto ou https://www.cifraclub.com.br/...')
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function (?string $state, callable $set): void {
                        if (blank($state)) {
                            $set('selected_url', null);

                            return;
                        }

                        $scraper = app(ChordScraperService::class);
                        $results = $scraper->searchSong($state);

                        if (! empty($results)) {
                            $set('selected_url', $results[0]['url']);
                        } else {
                            $set('selected_url', null);
                        }
                    }),

                Select::make('selected_url')
                    ->label('Resultados Encontrados')
                    ->placeholder('Digite acima para buscar ou selecione um resultado...')
                    ->options(function (callable $get): array {
                        $query = (string) ($get('search_query') ?? '');
                        if (blank($query)) {
                            return [];
                        }

                        $scraper = app(ChordScraperService::class);
                        $results = $scraper->searchSong($query);

                        $options = [];
                        foreach ($results as $result) {
                            $options[$result['url']] = "{$result['title']} - {$result['artist']} ({$result['source']})";
                        }

                        return $options;
                    })
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        $scraper = app(ChordScraperService::class);
                        $results = $scraper->searchSong($search);

                        $options = [];
                        foreach ($results as $result) {
                            $options[$result['url']] = "{$result['title']} - {$result['artist']} ({$result['source']})";
                        }

                        return $options;
                    })
                    ->live()
                    ->helperText('Selecione uma das cifras encontradas para importar.'),
            ])
            ->action(function (array $data, Component $livewire): void {
                $url = $data['selected_url'] ?? null;

                if (blank($url) && ! empty($data['search_query'])) {
                    $trimmed = trim((string) $data['search_query']);
                    if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
                        $url = $trimmed;
                    } else {
                        $results = app(ChordScraperService::class)->searchSong($trimmed);
                        if (! empty($results)) {
                            $url = $results[0]['url'];
                        }
                    }
                }

                if (blank($url)) {
                    Notification::make()
                        ->title('Nenhuma cifra encontrada ou selecionada.')
                        ->body('Por favor, digite um termo de busca válido ou cole o link direto da cifra.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $scraper = app(ChordScraperService::class);
                    $imported = $scraper->importFromUrl($url);

                    $currentData = is_array($livewire->data ?? null) ? $livewire->data : [];

                    if (method_exists($livewire, 'form')) {
                        $livewire->form->fill([
                            ...$currentData,
                            'title' => $imported['title'] ?: ($currentData['title'] ?? ''),
                            'artist' => $imported['artist'] ?: ($currentData['artist'] ?? ''),
                            'original_key' => $imported['original_key'] ?: ($currentData['original_key'] ?? 'C'),
                            'chordpro_content' => $imported['chordpro_content'] ?: ($currentData['chordpro_content'] ?? ''),
                        ]);
                    }

                    Notification::make()
                        ->title('Cifra importada com sucesso!')
                        ->body("Música: {$imported['title']} - {$imported['artist']} (Tom: {$imported['original_key']})")
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Erro ao importar cifra')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
