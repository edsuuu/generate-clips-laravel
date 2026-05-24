<div class="flex flex-col gap-6">
    <div class="flex w-full flex-col text-center">
        <flux:heading size="xl">{{ __('Confirm password') }}</flux:heading>
        <flux:subheading>{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</flux:subheading>
    </div>

    @if (session('status'))
        <div class="text-center font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="current-password"
            autofocus
            viewable
        />

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Confirm') }}
            </flux:button>
        </div>
    </form>
</div>
