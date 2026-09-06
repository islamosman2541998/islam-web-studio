@php($row=$getRecord())
<div class="studio-mobile-card"><strong>{{ $row->name }}</strong>@if($row instanceof \App\Models\User)<span>{{ $row->email }}</span><span>{{ $row->roles->pluck('name')->join(' / ') }}</span>@else<span>{{ $row->permissions_count }} {{ \App\Support\Studio::text('permissions') }}</span>@endif<span>{{ $row->created_at?->format('Y-m-d') }}</span></div>
