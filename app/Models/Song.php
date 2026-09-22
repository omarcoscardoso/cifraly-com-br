<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\SongFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Song extends Model
{
    /** @use HasFactory<SongFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'title',
        'artist',
        'original_key',
        'capo_fret',
        'bpm',
        'time_signature',
        'spotify_url',
        'youtube_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capo_fret' => 'integer',
            'bpm' => 'integer',
        ];
    }

    /**
     * Get all versions of this song.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(SongVersion::class);
    }

    /**
     * Get the default version of this song.
     */
    public function defaultVersion(): HasOne
    {
        return $this->hasOne(SongVersion::class)->where('is_default', true);
    }
}
