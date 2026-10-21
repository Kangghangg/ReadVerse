@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-beige bg-white px-4 py-2.5 text-sm text-ink placeholder:text-taupe shadow-none focus:border-mocha focus:ring-mocha/30 disabled:opacity-60']) }}>
