<?php

use App\Models\User;
use Filament\Auth\Pages\Login;
use Livewire\Livewire;

beforeEach(function () {
    createRolesAndPermissions();
});

it('redirects unauthenticated users to login', function () {
    $this->get('/')->assertRedirect('/login');
});

it('allows admin to access filament panel', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('allows viewer to access filament panel', function () {
    $user = User::factory()->viewer()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('allows editor to access filament panel', function () {
    $user = User::factory()->editor()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('rate limits login after 5 failed attempts from the same source', function () {
    // Filament's own Login page (Filament\Auth\Pages\Login) wires this via the
    // danharrin/livewire-rate-limiting package — no config on our side, but worth a
    // regression test so a future Login page override can't silently drop it. The 6th
    // attempt is intercepted by rateLimit() before credential checking ever runs, so it
    // produces no form errors — unlike the first 5, which fail real credential validation.
    $user = User::factory()->admin()->create();

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'wrong-password'])
            ->call('authenticate')
            ->assertHasFormErrors();
    }

    Livewire::test(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'wrong-password'])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});
