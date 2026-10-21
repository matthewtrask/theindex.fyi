<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasskeyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_passkey_requires_a_recently_confirmed_password(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('passkey.registration-options'))
            ->assertStatus(423);
    }

    public function test_passkey_registration_options_are_available_after_confirming_the_password(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->getJson(route('passkey.registration-options'))
            ->assertOk();
    }

    public function test_guests_cannot_register_passkeys(): void
    {
        $this->getJson(route('passkey.registration-options'))->assertUnauthorized();
    }
}
