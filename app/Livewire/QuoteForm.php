<?php

namespace App\Livewire;

use App\Models\Service;
use App\Support\LeadSubmission;
use App\Support\Studio;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class QuoteForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    public string $service_id = '';

    public bool $consent = false;

    public string $website = '';

    #[Locked]
    public string $locale = 'ar';

    #[Locked]
    public bool $sent = false;

    public function mount(?int $serviceId = null): void
    {
        $this->locale = app()->getLocale();
        $this->service_id = $serviceId ? (string) $serviceId : '';
    }

    public function submit(): void
    {
        app()->setLocale($this->locale);
        if ($this->sent) {
            return;
        }$key = 'quote:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', Studio::text('too_many'));
            $this->dispatch('studio-toast', type: 'warning', title: Studio::text('warning'), message: Studio::text('too_many'));

            return;
        }RateLimiter::hit($key, 60);
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }
        $data = $this->validate(LeadSubmission::rules(), [], LeadSubmission::attributes());
        if (! LeadSubmission::serviceIsAvailable($this->service_id, $this->locale)) {
            $this->addError('service_id', Studio::text('choose_service'));

            return;
        }
        LeadSubmission::create([...$data, 'service_id' => $this->service_id], 'form', $this->locale);
        $this->sent = true;
        $this->reset('name', 'email', 'phone', 'message', 'consent');
        $this->dispatch('studio-toast', type: 'success', title: Studio::text('request_received'), message: Studio::text('quote_success'));
    }

    public function render()
    {
        app()->setLocale($this->locale);

        return view('livewire.quote-form', ['services' => Service::published($this->locale)->orderBy('sort_order')->get()]);
    }
}
