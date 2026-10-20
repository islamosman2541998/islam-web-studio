<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\Service;

/** One intake path for project enquiries from the website form and the mobile app. */
class LeadSubmission
{
    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:180',
            'phone' => ['required', 'string', 'max:30', 'regex:/^[+0-9() .-]{7,30}$/'],
            'message' => 'required|string|min:15|max:5000',
            'service_id' => 'nullable|integer',
            'consent' => 'accepted',
        ];
    }

    /** @return array<string, string> */
    public static function attributes(): array
    {
        return [
            'name' => Studio::text('name'),
            'email' => Studio::text('email'),
            'phone' => Studio::text('phone'),
            'message' => Studio::text('message'),
            'service_id' => Studio::text('field_service_id'),
            'consent' => Studio::text('privacy_consent'),
        ];
    }

    public static function serviceIsAvailable(mixed $serviceId, string $locale): bool
    {
        return blank($serviceId) || Service::published($locale)->whereKey($serviceId)->exists();
    }

    /** @param array{name: string, email: string, phone: string, message: string, service_id?: int|string|null} $data */
    public static function create(array $data, string $source, string $locale): Lead
    {
        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['message'],
            'service_id' => filled($data['service_id'] ?? null) ? (int) $data['service_id'] : null,
            'source' => $source,
            'status' => 'new',
            'locale' => $locale,
            'is_demo' => (bool) config('studio.demo'),
        ]);

        app(StudioNotifier::class)->newLead($lead);

        return $lead;
    }
}
