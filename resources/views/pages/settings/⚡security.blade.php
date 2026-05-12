<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Passkey;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    /** @var \Illuminate\Support\Collection<int, Passkey> */
    public $passkeys;

    public string $newPasskeyName = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        $this->passkeys = Features::canManagePasskeys()
            ? auth()->user()->passkeys()->latest()->get()
            : collect();
    }

    public function removePasskey(int $id): void
    {
        $passkey = auth()->user()->passkeys()->findOrFail($id);
        $passkey->delete();
        $this->passkeys = auth()->user()->passkeys()->latest()->get();
        Flux::toast(variant: 'success', text: __('Passkey removed.'));
    }

    #[On('passkey-registered')]
    public function onPasskeyRegistered(): void
    {
        $this->passkeys = auth()->user()->passkeys()->latest()->get();
        $this->newPasskeyName = '';
        Flux::toast(variant: 'success', text: __('Passkey added.'));
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Security settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-password-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12">
                <flux:heading>{{ __('Two-factor authentication') }}</flux:heading>
                <flux:subheading>{{ __('Manage your two-factor authentication settings') }}</flux:subheading>

                <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <flux:text>
                                {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                            </flux:text>

                            <div class="flex justify-start">
                                <flux:button
                                    variant="danger"
                                    wire:click="disable"
                                >
                                    {{ __('Disable 2FA') }}
                                </flux:button>
                            </div>

                            <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                        </div>
                    @else
                        <div class="space-y-4">
                            <flux:text variant="subtle">
                                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                            </flux:text>

                            <flux:modal.trigger name="two-factor-setup-modal">
                                <flux:button
                                    variant="primary"
                                    wire:click="$dispatch('start-two-factor-setup')"
                                >
                                    {{ __('Enable 2FA') }}
                                </flux:button>
                            </flux:modal.trigger>

                            <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if (Features::canManagePasskeys())
            <section class="mt-12">
                <flux:heading>{{ __('Passkeys') }}</flux:heading>
                <flux:subheading>{{ __('Sign in without a password using Face ID, Touch ID, or a hardware security key.') }}</flux:subheading>

                <div class="mt-4 space-y-4">
                    @forelse ($passkeys as $passkey)
                        <div class="flex items-center justify-between p-3 border rounded-lg dark:border-zinc-700">
                            <div>
                                <flux:text class="font-medium">{{ $passkey->name }}</flux:text>
                                <flux:text variant="subtle" class="text-xs">
                                    {{ __('Added') }} {{ $passkey->created_at->diffForHumans() }}
                                    @if ($passkey->last_used_at)
                                        &middot; {{ __('Last used') }} {{ $passkey->last_used_at->diffForHumans() }}
                                    @endif
                                </flux:text>
                            </div>
                            <flux:button
                                variant="danger"
                                size="sm"
                                wire:click="removePasskey({{ $passkey->id }})"
                                wire:confirm="{{ __('Remove this passkey?') }}"
                            >
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    @empty
                        <flux:text variant="subtle">{{ __('No passkeys registered yet.') }}</flux:text>
                    @endforelse

                    <div class="flex items-end gap-3 mt-4">
                        <flux:input
                            wire:model="newPasskeyName"
                            :label="__('Passkey name')"
                            placeholder="{{ __('e.g. MacBook Touch ID') }}"
                            class="max-w-xs"
                        />
                        <flux:button
                            x-data
                            x-on:click="
                                const name = $wire.newPasskeyName.trim();
                                if (!name) return;
                                try {
                                    await window.registerPasskey(name);
                                    $wire.dispatch('passkey-registered');
                                } catch (e) {
                                    alert(e.message || 'Registration failed.');
                                }
                            "
                        >
                            {{ __('Add passkey') }}
                        </flux:button>
                    </div>
                </div>
            </section>
        @endif
    </x-pages::settings.layout>
</section>
