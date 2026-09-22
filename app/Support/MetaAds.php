<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\MetaAdInsight;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MetaAds
{
    public function configured(): bool
    {
        return filled(config('services.meta.access_token')) && filled(config('services.meta.ad_account_id'));
    }

    public function webhookConfigured(): bool
    {
        return filled(config('services.meta.page_access_token') ?: config('services.meta.access_token'))
            && filled(config('services.meta.app_secret'))
            && filled(config('services.meta.verify_token'));
    }

    public function leadsConfigured(): bool
    {
        return filled(config('services.meta.page_id'))
            && filled(config('services.meta.page_access_token') ?: config('services.meta.access_token'));
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        $secret = (string) config('services.meta.app_secret');

        if ($secret === '' || ! is_string($signature) || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        return hash_equals('sha256='.hash_hmac('sha256', $payload, $secret), $signature);
    }

    public function fetchLead(string $leadId): array
    {
        return $this->leadRequest()->get($this->url($leadId), [
            'fields' => implode(',', [
                'id', 'created_time', 'ad_id', 'ad_name', 'adset_id', 'adset_name',
                'campaign_id', 'campaign_name', 'form_id', 'platform', 'field_data',
            ]),
        ])->throw()->json();
    }

    public function importLead(string $leadId, array $webhook = []): Lead
    {
        $payload = $this->fetchLead($leadId);
        $fields = collect($payload['field_data'] ?? [])->mapWithKeys(function (array $field): array {
            $value = implode(', ', array_filter(Arr::wrap($field['values'] ?? []), 'is_scalar'));

            return [(string) ($field['name'] ?? '') => $value];
        });
        $first = $fields->get('first_name');
        $last = $fields->get('last_name');
        $name = $fields->get('full_name') ?: trim($first.' '.$last) ?: $fields->get('name');
        $known = ['full_name', 'first_name', 'last_name', 'name', 'email', 'phone_number', 'phone'];
        $answers = $fields->except($known)->filter()->map(fn ($value, $key) => Str::headline($key).': '.$value)->implode("\n");

        $lead = Lead::firstOrNew(['meta_lead_id' => $leadId]);
        $lead->fill([
            'name' => $name ?: 'Meta Lead '.$leadId,
            'phone' => $fields->get('phone_number') ?: $fields->get('phone'),
            'email' => $fields->get('email'),
            'message' => $answers ?: 'Lead received from Meta Lead Ads.',
            'source' => 'meta',
            'status' => $lead->status ?: 'new',
            'locale' => 'ar',
            'is_demo' => false,
            'meta_page_id' => $webhook['page_id'] ?? null,
            'meta_form_id' => $payload['form_id'] ?? $webhook['form_id'] ?? null,
            'meta_campaign_id' => $payload['campaign_id'] ?? null,
            'meta_campaign_name' => $payload['campaign_name'] ?? null,
            'meta_adset_id' => $payload['adset_id'] ?? null,
            'meta_adset_name' => $payload['adset_name'] ?? null,
            'meta_ad_id' => $payload['ad_id'] ?? $webhook['ad_id'] ?? null,
            'meta_ad_name' => $payload['ad_name'] ?? null,
            'meta_platform' => $payload['platform'] ?? 'facebook',
            'meta_created_at' => $payload['created_time'] ?? now(),
            'meta_payload' => $payload,
        ]);
        $lead->save();

        if ($lead->wasRecentlyCreated) {
            app(StudioNotifier::class)->newLead($lead);
        }

        return $lead;
    }

    public function syncInsights(int $days = 30): int
    {
        $account = preg_replace('/^act_/', '', (string) config('services.meta.ad_account_id'));
        if (! $this->configured() || ! $account) {
            throw new RuntimeException('Meta Ads credentials are incomplete.');
        }

        $params = [
            'level' => 'ad',
            'time_increment' => 1,
            'time_range' => json_encode(['since' => today()->subDays(max(1, $days) - 1)->toDateString(), 'until' => today()->toDateString()], JSON_THROW_ON_ERROR),
            'fields' => implode(',', [
                'account_id', 'account_name', 'campaign_id', 'campaign_name', 'adset_id', 'adset_name',
                'ad_id', 'ad_name', 'date_start', 'impressions', 'reach', 'clicks', 'inline_link_clicks',
                'spend', 'cpc', 'cpm', 'ctr', 'actions', 'cost_per_action_type',
            ]),
            'limit' => 500,
        ];
        $url = $this->url('act_'.$account.'/insights');
        $synced = 0;

        do {
            $response = $this->request()->get($url, $params)->throw()->json();
            foreach ($response['data'] ?? [] as $row) {
                $leads = (int) round($this->actionValue($row['actions'] ?? []));
                $spend = (float) ($row['spend'] ?? 0);
                MetaAdInsight::updateOrCreate(
                    ['date' => $row['date_start'], 'account_id' => $row['account_id'] ?? $account, 'ad_id' => $row['ad_id']],
                    [
                        'account_name' => $row['account_name'] ?? null,
                        'campaign_id' => $row['campaign_id'] ?? null,
                        'campaign_name' => $row['campaign_name'] ?? null,
                        'adset_id' => $row['adset_id'] ?? null,
                        'adset_name' => $row['adset_name'] ?? null,
                        'ad_name' => $row['ad_name'] ?? null,
                        'impressions' => (int) ($row['impressions'] ?? 0),
                        'reach' => (int) ($row['reach'] ?? 0),
                        'clicks' => (int) ($row['clicks'] ?? 0),
                        'link_clicks' => (int) ($row['inline_link_clicks'] ?? 0),
                        'leads' => $leads,
                        'spend' => $spend,
                        'cpc' => (float) ($row['cpc'] ?? 0),
                        'cpm' => (float) ($row['cpm'] ?? 0),
                        'ctr' => (float) ($row['ctr'] ?? 0),
                        'cost_per_lead' => $leads > 0 ? $spend / $leads : $this->actionValue($row['cost_per_action_type'] ?? []),
                        'currency' => (string) config('services.meta.currency', 'EGP'),
                    ],
                );
                $synced++;
            }

            $url = data_get($response, 'paging.next');
            $params = [];
        } while (filled($url));

        return $synced;
    }

    public function syncLeads(int $days = 90): int
    {
        if (! $this->leadsConfigured()) {
            throw new RuntimeException('Meta Page credentials are incomplete.');
        }

        $cutoff = now()->subDays(max(1, $days));
        $pageId = (string) config('services.meta.page_id');
        $formsUrl = $this->url($pageId.'/leadgen_forms');
        $formsParams = ['fields' => 'id', 'limit' => 100];
        $synced = 0;

        do {
            $formsResponse = $this->leadRequest()->get($formsUrl, $formsParams)->throw()->json();

            foreach ($formsResponse['data'] ?? [] as $form) {
                $formId = (string) ($form['id'] ?? '');
                if ($formId === '') {
                    continue;
                }

                $leadsUrl = $this->url($formId.'/leads');
                $leadsParams = ['fields' => 'id,created_time', 'limit' => 100];
                $reachedCutoff = false;

                do {
                    $leadsResponse = $this->leadRequest()->get($leadsUrl, $leadsParams)->throw()->json();

                    foreach ($leadsResponse['data'] ?? [] as $lead) {
                        $createdAt = filled($lead['created_time'] ?? null) ? Carbon::parse($lead['created_time']) : null;
                        if ($createdAt?->lt($cutoff)) {
                            $reachedCutoff = true;
                            break;
                        }

                        $leadId = (string) ($lead['id'] ?? '');
                        if ($leadId === '') {
                            continue;
                        }

                        try {
                            $this->importLead($leadId, ['page_id' => $pageId, 'form_id' => $formId]);
                            $synced++;
                        } catch (Throwable $error) {
                            report($error);
                        }
                    }

                    $leadsUrl = $reachedCutoff ? null : data_get($leadsResponse, 'paging.next');
                    $leadsParams = [];
                } while (filled($leadsUrl));
            }

            $formsUrl = data_get($formsResponse, 'paging.next');
            $formsParams = [];
        } while (filled($formsUrl));

        return $synced;
    }

    private function actionValue(array $actions): float
    {
        $priorities = ['lead', 'onsite_conversion.lead_grouped', 'onsite_conversion.lead_grouped_website', 'offsite_conversion.fb_pixel_lead'];
        foreach ($priorities as $type) {
            $match = collect($actions)->firstWhere('action_type', $type);
            if ($match) {
                return (float) ($match['value'] ?? 0);
            }
        }

        $match = collect($actions)->first(fn (array $action): bool => str_contains((string) ($action['action_type'] ?? ''), 'lead'));

        return (float) ($match['value'] ?? 0);
    }

    private function request(): PendingRequest
    {
        $token = (string) config('services.meta.access_token');
        if ($token === '') {
            throw new RuntimeException('Meta access token is missing.');
        }

        return Http::withToken($token)->acceptJson()->timeout(25)->retry(2, 500, throw: false);
    }

    private function leadRequest(): PendingRequest
    {
        $token = (string) (config('services.meta.page_access_token') ?: config('services.meta.access_token'));
        if ($token === '') {
            throw new RuntimeException('Meta page access token is missing.');
        }

        return Http::withToken($token)->acceptJson()->timeout(25)->retry(2, 500, throw: false);
    }

    private function url(string $path): string
    {
        return 'https://graph.facebook.com/'.config('services.meta.version', 'v26.0').'/'.ltrim($path, '/');
    }
}
