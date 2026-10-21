<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg bg-espresso px-5 py-2.5 text-sm font-medium text-cream transition hover:bg-mocha focus:outline-none focus:ring-2 focus:ring-mocha/40 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
