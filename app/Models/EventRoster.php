<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\EventRosterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventRoster extends Model
{
    /** @use HasFactory<EventRosterFactory> */
    use BelongsToOrganization, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_OPTIONS = [
        self::STATUS_PENDING => 'Pendente',
        self::STATUS_CONFIRMED => 'Confirmado',
        self::STATUS_DECLINED => 'Recusado',
    ];

    public const STATUS_COLORS = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_CONFIRMED => 'success',
        self::STATUS_DECLINED => 'danger',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_rosters';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'event_id',
        'user_id',
        'role_id',
        'status',
        'confirmation_token',
        'responded_at',
        'decline_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $roster): void {
            if (empty($roster->confirmation_token)) {
                $roster->confirmation_token = Str::random(40);
            }
        });
    }

    /**
     * Get the event for this roster entry.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the scheduled user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role/instrument assigned to the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
