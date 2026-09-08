<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Pages;

use App\Actions\Events\AddTeamToEventRosterAction;
use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\Team;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function afterCreate(): void
    {
        /** @var Event $event */
        $event = $this->record;

        if ($event->team_id) {
            $team = $event->team ?? Team::find($event->team_id);

            if ($team) {
                $result = app(AddTeamToEventRosterAction::class)->execute(
                    event: $event,
                    team: $team,
                );

                if ($result['added'] > 0) {
                    Notification::make()
                        ->title("{$result['added']} membro(s) da equipe '{$team->name}' adicionados automaticamente à escala!")
                        ->success()
                        ->send();
                }
            }
        }
    }
}
