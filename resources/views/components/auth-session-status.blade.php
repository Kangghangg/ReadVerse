@props(['status'])

@if ($status)
<div {{ $attributes->merge(['class' => 'rounded-lg border border-beige bg-sand/60 px-4 py-3 text-sm font-medium text-espresso']) }}>
    {{ $status }}
</div>
@endif
