@php($row=$getRecord())
<div class="studio-mobile-card"><strong>{{ $row->titleText() ?: ($row instanceof \App\Models\Partner ? \App\Support\ModuleRegistry::label('partners').' #'.$row->id : '') }}</strong><span>#{{ $row->id }} · {{ $row->created_at?->format('Y-m-d') }}</span>
@foreach(['status','is_active','is_featured','is_approved'] as $field) @if(isset($row->definition()['fields'][$field])) <span>{{ \App\Support\Studio::text('field_'.$field) }}: {{ is_bool($row->$field) ? \App\Support\Studio::text($row->$field?'yes':'no') : \App\Support\Studio::text($row->$field ?? '') }}</span> @endif @endforeach
</div>
