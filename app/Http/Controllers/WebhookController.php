<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\SyncWebhook;

class WebhookController extends Controller
{
    private array $eventHandlers = [
        'eloquent.created: App\Models\Node'       => 'node.create',
        'eloquent.updated: App\Models\Node'       => 'node.update',
        'eloquent.deleted: App\Models\Node'       => 'node.delete',
        'eloquent.created: App\Models\Allocation' => 'allocation.create',
        'eloquent.updated: App\Models\Allocation' => 'allocation.update',
        'eloquent.deleted: App\Models\Allocation' => 'allocation.delete',
        'eloquent.created: App\Models\Egg'        => 'egg.create',
        'eloquent.updated: App\Models\Egg'        => 'egg.create',
        'eloquent.deleted: App\Models\Egg'        => 'egg.delete',
        'eloquent.created: App\Models\Server'     => 'server.create',
        'eloquent.updated: App\Models\Server'     => 'server.update',
        'eloquent.deleted: App\Models\Server'     => 'server.delete',
        'eloquent.created: App\Models\User'       => 'user.create',
        'eloquent.updated: App\Models\User'       => 'user.update',
        'eloquent.deleted: App\Models\User'       => 'user.delete',
    ];

    public function handleWebhook(Request $request): Response
    {
        $event = $request->header('x-webhook-event');
        if (isset($this->eventHandlers[$event])) {
            SyncWebhook::dispatch($request->json()->all(), $this->eventHandlers[$event]);
        }
        return response()->noContent();
    }
}
