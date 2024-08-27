<x-guest-layout>

  <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-center mb-3">Register</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <x-input-label for="first_name_register" :value="__('First Name')" />
            <x-text-input id="first_name_register" class="form-control" type="text" name="first_name_register" :value="old('first_name_register')" required autofocus autocomplete="first_name" />
            <x-input-error :messages="$errors->get('first_name_register')" class="mt-2" />
        </div>
        <div class="mb-3">
            <x-input-label for="middle_name_register" :value="__('Middle Name')" />
            <x-text-input id="middle_name_register" class="form-control" type="text" name="middle_name_register" :value="old('middle_name_register')" required autofocus autocomplete="middle_name" />
            <x-input-error :messages="$errors->get('middle_name_register')" class="mt-2" />
        </div>
        <div class="mb-3">
            <x-input-label for="last_name_register" :value="__('Last Name')" />
            <x-text-input id="last_name_register" class="form-control" type="text" name="last_name_register" :value="old('last_name_register')" required autofocus autocomplete="last_name" />
            <x-input-error :messages="$errors->get('last_name_register')" class="mt-2" />
        </div>

        <!-- Work Enterprise -->
        <div class="mb-3">
            <x-input-label for="enterprise_name" :value="__('Work Enterprise')" />
            <x-text-input id="enterprise_name" class="form-control" type="text" name="enterprise_name" :value="old('enterprise_name')" required autofocus autocomplete="work_enterprise" />
            <x-input-error :messages="$errors->get('enterprise_name')" class="mt-2" />
        </div>

        <!-- Job Title -->
        <div class="mb-3">
            <x-input-label for="job_title_register" :value="__('Job Title')" />
            <x-text-input id="job_title_register" class="form-control" type="text" name="job_title_register" :value="old('job_title_register')" required autofocus autocomplete="job_title" />
            <x-input-error :messages="$errors->get('job_title_register')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email_register" :value="__('Email')" />
            <x-text-input id="email_register" class="form-control" type="email" name="email_register" :value="old('email_register')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email_register')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password_register" :value="__('Password')" />

            <x-text-input id="password_register" class="form-control"
                            type="password"
                            name="password_register"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_register')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_register_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_register_confirmation" class="form-control"
                            type="password"
                            name="password_register_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_register_confirmation')" class="mt-2" />
        </div>

        <div class="flex justify-between items-center mt-4">
            <a class="mx-2 underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
