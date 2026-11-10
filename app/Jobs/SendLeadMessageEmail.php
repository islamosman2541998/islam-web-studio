<?php

namespace App\Jobs;

use App\Mail\LeadMessageMail;
use App\Models\LeadMessage;
use App\Support\LeadMessaging;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendLeadMessageEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public int $messageId) {}

    public function handle(LeadMessaging $messaging): void
    {
        $message = LeadMessage::find($this->messageId);
        if (! $message || $message->status !== 'pending') {
            return;
        }
        Mail::to($message->recipient)->send(new LeadMessageMail($message));
        $messaging->markSent($message);
    }

    public function failed(?Throwable $exception): void
    {
        LeadMessage::whereKey($this->messageId)->update(['status' => 'failed', 'error' => mb_substr((string) $exception?->getMessage(), 0, 1000)]);
    }
}
