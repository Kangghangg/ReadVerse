<x-guest-layout>
    <h1 class="mb-6 font-serif text-3xl font-semibold text-espresso">Đăng nhập</h1>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Mật khẩu" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-mocha">
                <input id="remember_me" type="checkbox" name="remember" class="rounded-md border-beige text-espresso focus:ring-mocha/30">
                Ghi nhớ đăng nhập
            </label>

            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-sm text-mocha underline-offset-4 transition hover:text-espresso hover:underline">Quên mật khẩu?</a>
            @endif
        </div>

        <x-primary-button class="w-full">Đăng nhập</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-mocha">
        Chưa có tài khoản?
        <a href="{{ route('register') }}" class="font-medium text-espresso underline-offset-4 hover:underline">Đăng ký</a>
    </p>
</x-guest-layout>
