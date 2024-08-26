<x-guest-layout>
    <!-- Session Status -->
    {{-- @dump(session('status')) --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-center mb-3">LOGIN FORM</h2>
    {{-- <button onclick="navigator.clipboard.writeText(window.foo ?? 'aaa')">aaa</button> --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
          <x-input-label for="email" :value="__('Email')" />
          <x-text-input id="email" class="form-control" type="email" name="email" required autocomplete="email" />
          <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->        
        <div class="mb-3">
          <x-input-label for="password" :value="__('Password')" />
          <x-text-input id="password" class="form-control" type="password" name="password" required autocomplete="password" />
          <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ml-2 fs-6">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- <div class="flex items-center justify-end mt-4"> -->
        <div class="d-flex align-items-center justify-content-end mt-3">
            @if (Route::has('password.request'))
                <a class="btn text-decoration-underline fs-6 rounded" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ml-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
