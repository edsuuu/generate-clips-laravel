<div class="flex flex-col gap-6">
    <div class="flex w-full flex-col text-center">
        <flux:heading size="xl">{{ __('Forgot password') }}</flux:heading>
        <flux:subheading>{{ __('Enter your email to receive a password reset link') }}</flux:subheading>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="text-center font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Email password reset link') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Remember your password?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
