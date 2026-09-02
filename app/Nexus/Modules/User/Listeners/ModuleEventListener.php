<?php

namespace App\Nexus\Modules\User\Listeners;

use Illuminate\Support\Facades\Log;
use Nodex\Nexus\Events\ModuleEvent;

class ModuleEventListener
{
    public function handle(ModuleEvent $event)
    {
        Log::info("ModuleEventListener handling event: {$event->action} for module {$event->module}", ['data' => $event->data]);

        if ($event->module === 'user' && $event->action === 'before_delete') {
            Log::info('ModuleEventListener: Before deleting user', ['id' => $event->data->id, 'name' => $event->data->name]);
        } elseif ($event->module === 'user' && $event->action === 'store') {
            Log::info('ModuleEventListener: User created', ['id' => $event->data->id, 'name' => $event->data->name]);
        } elseif ($event->module === 'user' && $event->action === 'search_field' && $event->data['field'] === 'role') {
            $event->data['options'][] = ['value' => 'guest', 'label' => 'Guest'];
            Log::info('ModuleEventListener: Added guest role to filter options', $event->data['options']);
        }
    }
}
