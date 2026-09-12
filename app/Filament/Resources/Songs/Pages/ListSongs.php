<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Pages;

use App\Filament\Resources\Songs\SongResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Column;

class ListSongs extends ListRecords
{
    protected static string $resource = SongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function initTableColumnManager(): void
    {
        if (blank($this->tableColumns)) {
            $this->setTableColumns($this->loadTableColumnsFromSession());
        }

        $this->applyTableColumnManager();
    }

    /**
     * @return array<int, array{type: string, name: string, label: string, isHidden: bool, isToggled: bool, isToggleable: bool, isToggledHiddenByDefault: ?bool, columns?: array<int, array{type: string, name: string, label: string, isHidden: bool, isToggled: bool, isToggleable: bool, isToggledHiddenByDefault: ?bool}>}>
     */
    public function getDefaultTableColumnState(): array
    {
        return $this->cachedDefaultTableColumnState ??= collect($this->getTable()->getColumns())
            ->map(fn (Column $column): ?array => $this->mapTableColumnToArray($column))
            ->filter()
            ->values()
            ->all();
    }
}
