@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-espresso']) }}>
    {{ $value ?? $slot }}
</label>
