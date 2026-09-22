<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs;

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Filament\Resources\Songs\Pages\ListSongs;
use App\Filament\Resources\Songs\Schemas\SongForm;
use App\Filament\Resources\Songs\Tables\SongsTable;
use App\Models\Song;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SongResource extends Resource
{
    protected static ?string $model = Song::class;

    protected static ?string $modelLabel = 'Música';

    protected static ?string $pluralModelLabel = 'Músicas';

    protected static ?string $navigationLabel = 'Músicas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    public const KEY_OPTIONS = [
        'C' => 'C',
        'C#' => 'C#',
        'Db' => 'Db',
        'D' => 'D',
        'D#' => 'D#',
        'Eb' => 'Eb',
        'E' => 'E',
        'F' => 'F',
        'F#' => 'F#',
        'Gb' => 'Gb',
        'G' => 'G',
        'G#' => 'G#',
        'Ab' => 'Ab',
        'A' => 'A',
        'A#' => 'A#',
        'Bb' => 'Bb',
        'B' => 'B',
        'Cm' => 'Cm',
        'C#m' => 'C#m',
        'Dbm' => 'Dbm',
        'Dm' => 'Dm',
        'D#m' => 'D#m',
        'Ebm' => 'Ebm',
        'Em' => 'Em',
        'Fm' => 'Fm',
        'F#m' => 'F#m',
        'Gbm' => 'Gbm',
        'Gm' => 'Gm',
        'G#m' => 'G#m',
        'Abm' => 'Abm',
        'Am' => 'Am',
        'A#m' => 'A#m',
        'Bbm' => 'Bbm',
        'Bm' => 'Bm',
    ];

    public const TIME_SIGNATURE_OPTIONS = [
        '4/4' => '4/4',
        '3/4' => '3/4',
        '2/4' => '2/4',
        '6/8' => '6/8',
        '12/8' => '12/8',
    ];

    public static function form(Schema $schema): Schema
    {
        return SongForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SongsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSongs::route('/'),
            'create' => CreateSong::route('/create'),
            'edit' => EditSong::route('/{record}/edit'),
        ];
    }
}
