@extends('layouts.site')
@section('content')<section class="page-intro shell"><x-page-eyebrow :label="$heading['label']" /><h1>{{ $heading['title'] }}</h1>@if(filled($heading['description']))<p>{{ $heading['description'] }}</p>@endif</section><section class="section shell process-page">@include('site.partials.process')</section>@endsection
