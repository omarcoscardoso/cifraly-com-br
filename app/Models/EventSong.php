<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\EventSongFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSong extends Model
{
    /** @use HasFactory<EventSongFactory> */
    use BelongsToOrganization, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_songs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'event_id',
        'song_id',
        'song_version_id',
        'target_key',
        'capo_fret',
        'order_index',
        'arrangement_notes',
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
            'order_index' => 'integer',
        ];
    }

    /**
     * Get the event for this setlist song.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the song.
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Get the specific song version used in this event.
     */
    public function songVersion(): BelongsTo
    {
        return $this->belongsTo(SongVersion::class);
    }
}
