<?php

namespace App\Livewire;

use App\Models\Lead;
use App\Models\Service;
use App\Support\Studio;
use App\Support\StudioNotifier;
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
        $data = $this->validate(['name' => 'required|string|min:2|max:100', 'email' => 'required|email|max:180', 'phone' => ['required', 'string', 'max:30', 'regex:/^[+0-9() .-]{7,30}$/'], 'message' => 'required|string|min:15|max:5000', 'service_id' => 'nullable|integer', 'consent' => 'accepted'], [], ['name' => Studio::text('name'), 'email' => Studio::text('email'), 'phone' => Studio::text('phone'), 'message' => Studio::text('message'), 'consent' => Studio::text('privacy_consent')]);
        if ($this->service_id && ! Service::published($this->locale)->whereKey($this->service_id)->exists()) {
            $this->addError('service_id', Studio::text('choose_service'));

            return;
        }
        $lead = Lead::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'message' => $data['message'], 'service_id' => $this->service_id ?: null, 'source' => 'form', 'status' => 'new', 'locale' => $this->locale, 'is_demo' => config('studio.demo')]);
        app(StudioNotifier::class)->newLead($lead);
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
