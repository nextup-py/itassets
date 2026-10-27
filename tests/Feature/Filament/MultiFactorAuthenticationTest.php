<?php

use App\Models\User;

beforeEach(function () {
    createRolesAndPermissions();
});

it('redirects an admin without 2FA set up to the setup screen', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect('/multi-factor-authentication/set-up');
});

it('allows an admin with 2FA already set up into the panel', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('does not require 2FA for an editor', function () {
    $user = User::factory()->editor()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('does not require 2FA for a viewer', function () {
    $user = User::factory()->viewer()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});
