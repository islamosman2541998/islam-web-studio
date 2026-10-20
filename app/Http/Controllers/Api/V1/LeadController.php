<?php

namespace App\Http\Controllers\Api\V1;

use App\Support\LeadSubmission;
use App\Support\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadController extends ApiController
{
    /** Leads from the app appear in the dashboard with the "mobile app" source. */
    public const SOURCE = 'mobile_app';

    public function store(Request $request): JsonResponse
    {
        $success = ['message' => Studio::text('quote_success'), 'title' => Studio::text('request_received')];

        // Bots fill the hidden `website` field; answer as if accepted and store nothing.
        if (filled($request->input('website'))) {
            return response()->json($success, 201);
        }

        $data = $request->validate(LeadSubmission::rules(), [], LeadSubmission::attributes());
        if (! LeadSubmission::serviceIsAvailable($data['service_id'] ?? null, app()->getLocale())) {
            throw ValidationException::withMessages(['service_id' => Studio::text('choose_service')]);
        }

        LeadSubmission::create($data, self::SOURCE, app()->getLocale());

        return response()->json($success, 201);
    }
}
