<?php

namespace App\Http\Controllers;

use App\Jobs\ImportMetaLead;
use App\Support\MetaAds;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetaLeadWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $valid = filled(config('services.meta.verify_token'))
            && $request->query('hub_mode') === 'subscribe'
            && hash_equals((string) config('services.meta.verify_token'), (string) $request->query('hub_verify_token'));

        abort_unless($valid, 403);

        return response((string) $request->query('hub_challenge'), 200)->header('Content-Type', 'text/plain');
    }

    public function handle(Request $request, MetaAds $meta): Response
    {
        abort_unless($meta->verifySignature($request->getContent(), $request->header('X-Hub-Signature-256')), 403);

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                if (($change['field'] ?? null) === 'leadgen' && filled($value['leadgen_id'] ?? null)) {
                    ImportMetaLead::dispatch((string) $value['leadgen_id'], $value);
                }
            }
        }

        return response('EVENT_RECEIVED', 200)->header('Content-Type', 'text/plain');
    }
}
