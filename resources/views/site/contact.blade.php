@extends('layouts.site')
@section('content')<section class="section shell contact-page"><div><x-page-eyebrow :label="$heading['label']" /><h1>{{ $heading['title'] }}</h1>@if(filled($heading['description']))<p>{{ $heading['description'] }}</p>@endif @include('site.partials.contact-details')<div class="contact-decoration" aria-hidden="true">↗</div></div><livewire:quote-form /></section>@endsection
