<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\Security\RecordLauncherSecurityEventsAction;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class LauncherSecurityEventController extends Controller
{
    public function __invoke(Request $request, RecordLauncherSecurityEventsAction $record): Response
    {
        // The account comes from the token and the device from the launcher.device
        // middleware -- never from the body -- so a device can only ever report about
        // itself, and nobody can file events against another customer.
        $device = $request->attributes->get('launcher_device');

        // Forward compatible: an event type this server does not know yet is dropped
        // instead of failing the whole batch, so a launcher released before the server is
        // updated does not lose every other report along with it.
        $known = array_keys(RecordLauncherSecurityEventsAction::TYPES);
        $request->merge(['events' => array_values(array_filter(
            (array)$request->input('events', []),
            fn ($event) => is_array($event) && in_array($event['type'] ?? null, $known, true),
        ))]);

        $validated = $request->validate([
            'app_version' => ['nullable', 'string', 'max:32', 'regex:/^[0-9A-Za-z.\-+]+$/'],
            'events' => ['present', 'array', 'max:50'],
            'events.*.type' => ['required', 'string', Rule::in(array_keys(RecordLauncherSecurityEventsAction::TYPES))],
            'events.*.subject' => ['nullable', 'string', 'max:255'],
            'events.*.sha256' => ['nullable', 'regex:/\A[a-fA-F0-9]{64}\z/'],
            'events.*.count' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        $record->execute(
            user: $request->user(),
            device: $device instanceof LauncherDevice ? $device : null,
            events: $validated['events'],
            appVersion: $validated['app_version'] ?? null,
            ipAddress: $request->ip(),
        );

        return response()->noContent();
    }
}
