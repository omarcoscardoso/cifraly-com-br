<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Str;

class AddTeamToEventRosterAction
{
    /**
     * Add all members of a team to the event's roster.
     *
     * @return array{added: int, skipped: int}
     */
    public function execute(Event $event, Team $team, ?int $fallbackRoleId = null, bool $skipExisting = true): array
    {
        $team->loadMissing(['teamMembers.defaultRole', 'teamMembers.user']);

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($team->teamMembers as $member) {
            if ($skipExisting) {
                $exists = EventRoster::where('event_id', $event->id)
                    ->where('user_id', $member->user_id)
                    ->exists();

                if ($exists) {
                    $skippedCount++;

                    continue;
                }
            }

            $roleId = $member->default_role_id ?? $fallbackRoleId;

            if (! $roleId) {
                $roleId = Role::where('organization_id', $event->organization_id)->value('id');
            }

            if (! $roleId) {
                continue;
            }

            EventRoster::create([
                'organization_id' => $event->organization_id,
                'event_id' => $event->id,
                'user_id' => $member->user_id,
                'role_id' => $roleId,
                'status' => EventRoster::STATUS_PENDING,
                'confirmation_token' => Str::random(40),
            ]);

            $addedCount++;
        }

        return [
            'added' => $addedCount,
            'skipped' => $skippedCount,
        ];
    }
}
