@extends('layouts.site')
@section('content')<section class="section shell contact-page"><div><x-page-eyebrow :label="\App\Support\Studio::text('route_contact')" /><h1>@t('contact_title')</h1><p>@t('contact_intro')</p>@include('site.partials.contact-details')<div class="contact-decoration" aria-hidden="true">↗</div></div><livewire:quote-form /></section>@endsection
