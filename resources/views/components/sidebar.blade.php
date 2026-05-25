@props(['layout' => 'sidebar'])

<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 {{ $layout === 'navbar' ? 'lg:hidden' : '' }}">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <x-app-logo href="{{ route('home') }}" class="px-2" wire:navigate />

    <flux:navlist variant="grid">
        @auth
            <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>
        @endauth

        <flux:navlist.item icon="video-camera" :href="route('videos.create')" :current="request()->routeIs('videos.create')" wire:navigate>
            {{ __('Novo Vídeo') }}
        </flux:navlist.item>

        @if(request()->route('video'))
            <flux:navlist.item icon="document-text" :href="route('videos.transcript', request()->route('video'))" :current="request()->routeIs('videos.transcript')" wire:navigate>
                {{ __('Transcrição') }}
            </flux:navlist.item>

            <flux:navlist.item icon="scissors" :href="route('videos.editor', request()->route('video'))" :current="request()->routeIs('videos.editor')" wire:navigate>
                {{ __('Editor') }}
            </flux:navlist.item>
        @endif
    </flux:navlist>

    <flux:spacer />

    @auth
        <flux:dropdown position="bottom" align="start">
            <flux:sidebar.profile
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down"
                data-test="sidebar-menu-button"
            />

            <flux:menu>
                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                    <flux:avatar
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                    />
                    <div class="grid flex-1 text-start text-sm leading-tight">
                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                    </div>
                </div>
                <flux:menu.separator />
                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    @endauth
</flux:sidebar>
