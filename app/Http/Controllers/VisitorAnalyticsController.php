<?php

namespace App\Http\Controllers;

use App\Models\VisitorEvent;
use App\Models\VisitorPageView;
use App\Models\VisitorSession;
use App\Support\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisitorAnalyticsController extends Controller
{
    private const EVENT_NAMES = ['cta_click', 'outbound_click', 'whatsapp_click', 'download', 'form_start', 'form_submit', 'video_play', 'video_complete'];

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(Studio::setting('analytics.enabled', true) && ! config('studio.demo'), 404);
        if ($this->isBot((string) $request->userAgent())) {
            return response()->json(['accepted' => false]);
        }

        $data = $request->validate([
            'type' => ['required', 'in:pageview,activity,event'],
            'visitor_id' => ['required', 'uuid'],
            'session_id' => ['required', 'uuid'],
            'pageview_id' => ['nullable', 'uuid'],
            'event_id' => ['nullable', 'uuid'],
            'path' => ['nullable', 'string', 'max:2048'],
            'title' => ['nullable', 'string', 'max:500'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'locale' => ['nullable', 'string', 'max:10'],
            'language' => ['nullable', 'string', 'max:20'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'screen_width' => ['nullable', 'integer', 'between:0,10000'],
            'screen_height' => ['nullable', 'integer', 'between:0,10000'],
            'viewport_width' => ['nullable', 'integer', 'between:0,10000'],
            'viewport_height' => ['nullable', 'integer', 'between:0,10000'],
            'duration' => ['nullable', 'integer', 'between:0,86400'],
            'scroll_depth' => ['nullable', 'integer', 'between:0,100'],
            'event' => ['nullable', 'in:'.implode(',', self::EVENT_NAMES)],
            'label' => ['nullable', 'string', 'max:255'],
            'target_path' => ['nullable', 'string', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data): void {
            $now = now();
            $agent = $this->agent((string) $request->userAgent());
            $referrerHost = $this->host($data['referrer'] ?? null);
            $path = $this->safePath($data['path'] ?? '/');
            $session = VisitorSession::firstOrCreate(
                ['public_id' => $data['session_id']],
                [
                    'visitor_hash' => hash_hmac('sha256', $data['visitor_id'], (string) config('app.key')),
                    'ip_hash' => null,
                    'country_code' => $this->country($request),
                    'device_type' => $agent['device'],
                    'browser' => $agent['browser'],
                    'operating_system' => $agent['os'],
                    'language' => $data['language'] ?? null,
                    'timezone' => null,
                    'screen_width' => null,
                    'screen_height' => null,
                    'viewport_width' => $data['viewport_width'] ?? null,
                    'viewport_height' => $data['viewport_height'] ?? null,
                    'user_agent' => null,
                    'landing_path' => $path,
                    'exit_path' => $path,
                    'referrer_host' => $referrerHost,
                    'utm_source' => $data['utm_source'] ?? null,
                    'utm_medium' => $data['utm_medium'] ?? null,
                    'utm_campaign' => $data['utm_campaign'] ?? null,
                    'utm_content' => $data['utm_content'] ?? null,
                    'utm_term' => $data['utm_term'] ?? null,
                    'consented_at' => null,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                ],
            );

            $session->forceFill([
                'last_seen_at' => $now,
                'exit_path' => $path,
                'duration_seconds' => max((int) $session->duration_seconds, (int) ($data['duration'] ?? 0)),
            ])->save();

            if ($data['type'] === 'pageview' && filled($data['pageview_id'] ?? null)) {
                $view = VisitorPageView::firstOrCreate(
                    ['client_id' => $data['pageview_id']],
                    [
                        'visitor_session_id' => $session->id,
                        'path' => $path,
                        'title' => Str::limit(strip_tags((string) ($data['title'] ?? '')), 500, ''),
                        'referrer_host' => $referrerHost,
                        'locale' => $data['locale'] ?? null,
                        'viewed_at' => $now,
                        'last_activity_at' => $now,
                    ],
                );
                if ($view->wasRecentlyCreated) {
                    $session->increment('page_views_count');
                }
            }

            if ($data['type'] === 'activity' && filled($data['pageview_id'] ?? null)) {
                $view = VisitorPageView::where('client_id', $data['pageview_id'])
                    ->where('visitor_session_id', $session->id)->first();
                $view?->forceFill([
                    'duration_seconds' => max((int) $view->duration_seconds, (int) ($data['duration'] ?? 0)),
                    'scroll_depth' => max((int) $view->scroll_depth, (int) ($data['scroll_depth'] ?? 0)),
                    'last_activity_at' => $now,
                ])->save();
            }

            if ($data['type'] === 'event' && filled($data['event_id'] ?? null) && filled($data['event'] ?? null)) {
                $view = VisitorPageView::where('client_id', $data['pageview_id'] ?? '')->where('visitor_session_id', $session->id)->first();
                $event = VisitorEvent::firstOrCreate(
                    ['client_id' => $data['event_id']],
                    [
                        'visitor_session_id' => $session->id,
                        'visitor_page_view_id' => $view?->id,
                        'name' => $data['event'],
                        'label' => Str::limit(strip_tags((string) ($data['label'] ?? '')), 255, ''),
                        'target_path' => $this->safePath($data['target_path'] ?? null),
                        'metadata' => Arr::only($data, ['locale']),
                        'occurred_at' => $now,
                    ],
                );
                if ($event->wasRecentlyCreated) {
                    $session->increment('events_count');
                }
            }
        });

        return response()->json(['accepted' => true], 202);
    }

    private function safePath(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }
        $path = parse_url($value, PHP_URL_PATH) ?: '/';

        return '/'.ltrim(Str::limit($path, 2048, ''), '/');
    }

    private function host(?string $url): ?string
    {
        $host = filled($url) ? parse_url($url, PHP_URL_HOST) : null;

        return is_string($host) ? Str::lower(Str::limit($host, 255, '')) : null;
    }

    private function country(Request $request): ?string
    {
        $country = strtoupper((string) $request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }

    /** @return array{device:string,browser:string,os:string} */
    private function agent(string $agent): array
    {
        $device = preg_match('/tablet|ipad/i', $agent) ? 'tablet' : (preg_match('/mobile|iphone|android/i', $agent) ? 'mobile' : 'desktop');
        $browser = match (true) {
            preg_match('/Edg\//i', $agent) === 1 => 'Edge',
            preg_match('/OPR\//i', $agent) === 1 => 'Opera',
            preg_match('/Chrome\//i', $agent) === 1 => 'Chrome',
            preg_match('/Firefox\//i', $agent) === 1 => 'Firefox',
            preg_match('/Safari\//i', $agent) === 1 => 'Safari',
            default => 'Other',
        };
        $os = match (true) {
            preg_match('/Windows/i', $agent) === 1 => 'Windows',
            preg_match('/Android/i', $agent) === 1 => 'Android',
            preg_match('/iPhone|iPad|iOS/i', $agent) === 1 => 'iOS',
            preg_match('/Mac OS/i', $agent) === 1 => 'macOS',
            preg_match('/Linux/i', $agent) === 1 => 'Linux',
            default => 'Other',
        };

        return compact('device', 'browser', 'os');
    }

    private function isBot(string $agent): bool
    {
        return $agent === '' || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|lighthouse|headless/i', $agent) === 1;
    }
}

