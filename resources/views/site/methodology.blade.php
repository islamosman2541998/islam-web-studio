@extends('layouts.site')
@section('content')<section class="page-intro shell"><x-page-eyebrow :label="\App\Support\Studio::text('route_methodology')" /><h1>@t('methodology_title')</h1><p>@t('methodology_intro')</p></section><section class="section shell process-page">@include('site.partials.process')</section>@endsection
