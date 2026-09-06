@extends('layouts.site')
@section('content')<section class="page-intro shell error-page"><x-page-eyebrow :label="\App\Support\Studio::text('not_found')" /><h1>@t('not_found')</h1><p>@t('not_found_text')</p><a href="{{ route('home',['locale'=>app()->getLocale()]) }}" class="button" wire:navigate>@t('home') ↗</a></section>@endsection
