<?php

namespace App\Support;

use App\Jobs\SendLeadMessageEmail;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\User;
use Illuminate\Support\Collection;

class LeadMessaging
{
    /** @var array<string, string> */
    public const Placeholders = [
        '{الاسم}' => 'name',
        '{name}' => 'name',
        '{الاسم_الأول}' => 'first_name',
        '{first_name}' => 'first_name',
        '{الخدمة}' => 'service',
        '{service}' => 'service',
    ];

    public function personalize(string $template, Lead $lead): string
    {
        $name = trim((string) $lead->name);
        $values = [
            'name' => $name,
            'first_name' => strtok($name, ' ') ?: $name,
            'service' => $lead->service?->titleText($lead->locale ?: 'ar') ?? '',
        ];

        return strtr($template, array_map(fn (string $value): string => $values[$value], self::Placeholders));
    }

    /** Converts local Egyptian numbers (01xxxxxxxxx) and international formats to the digits-only form wa.me expects. */
    public function whatsAppNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            $digits = '2'.$digits;
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '20'.$digits;
        }

        return strlen($digits) >= 8 && strlen($digits) <= 15 ? $digits : null;
    }

    public function whatsAppUrl(LeadMessage $message): string
    {
        return 'https://wa.me/'.$message->recipient.'?text='.rawurlencode($message->body);
    }

    public function recipient(Lead $lead, string $channel): ?string
    {
        if ($channel === 'whatsapp') {
            return $this->whatsAppNumber($lead->phone);
        }

        return filter_var($lead->email, FILTER_VALIDATE_EMAIL) ? $lead->email : null;
    }

    /**
     * Stores one message per lead; emails are queued for delivery while WhatsApp messages wait in the outbox.
     *
     * @param  Collection<int, Lead>  $leads
     * @param  array<int, array{lead_id: int|string, body: ?string}>  $messages
     * @return array{prepared: int, skipped: int}
     */
    public function prepare(Collection $leads, string $channel, array $messages, ?string $subject, ?User $user): array
    {
        $leads = $leads->keyBy('id');
        $prepared = 0;
        foreach ($messages as $item) {
            $lead = $leads->get((int) ($item['lead_id'] ?? 0));
            $body = trim((string) ($item['body'] ?? ''));
            $recipient = $lead ? $this->recipient($lead, $channel) : null;
            if (! $recipient || $body === '') {
                continue;
            }
            $message = LeadMessage::create([
                'lead_id' => $lead->id,
                'user_id' => $user?->id,
                'channel' => $channel,
                'recipient' => $recipient,
                'subject' => $channel === 'email' ? $subject : null,
                'body' => $body,
                'status' => 'pending',
            ]);
            $prepared++;
            if ($channel === 'email') {
                SendLeadMessageEmail::dispatch($message->id);
            }
        }

        return ['prepared' => $prepared, 'skipped' => $leads->count() - $prepared];
    }

    public function markSent(LeadMessage $message): void
    {
        $message->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        if ($message->lead?->status === 'new') {
            $message->lead->update(['status' => 'contacted']);
        }
    }
}
