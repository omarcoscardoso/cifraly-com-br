<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_OPTIONS = [
        self::STATUS_DRAFT => 'Rascunho',
        self::STATUS_PUBLISHED => 'Publicado / Escala Aberta',
        self::STATUS_COMPLETED => 'Concluído',
        self::STATUS_CANCELED => 'Cancelado',
    ];

    public const STATUS_COLORS = [
        self::STATUS_DRAFT => 'gray',
        self::STATUS_PUBLISHED => 'info',
        self::STATUS_COMPLETED => 'success',
        self::STATUS_CANCELED => 'danger',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'team_id',
        'title',
        'status',
        'starts_at',
        'rehearsal_at',
        'notes',
        'scheduled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'rehearsal_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Get the team associated with this event.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the roster entries (people scheduled) for this event.
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(EventRoster::class);
    }

    /**
     * Get the event song entries (setlist) for this event.
     */
    public function eventSongs(): HasMany
    {
        return $this->hasMany(EventSong::class)->orderBy('order_index');
    }

    /**
     * Get the songs in the setlist for this event.
     */
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'event_songs')
            ->using(EventSong::class)
            ->withPivot(['id', 'organization_id', 'song_version_id', 'target_key', 'order_index', 'arrangement_notes'])
            ->withTimestamps();
    }
}
