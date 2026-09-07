<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SongVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongVersion extends Model
{
    /** @use HasFactory<SongVersionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'song_id',
        'label',
        'base_key',
        'chordpro_content',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the song that owns this version.
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
