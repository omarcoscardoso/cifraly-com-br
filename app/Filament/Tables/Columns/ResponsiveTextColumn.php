<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class ResponsiveTextColumn extends TextColumn
{
    public function renderInLayout(): ?HtmlString
    {
        if ($this->isToggledHidden()) {
            return null;
        }

        return parent::renderInLayout();
    }
}
