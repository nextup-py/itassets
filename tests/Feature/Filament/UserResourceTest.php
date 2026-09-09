<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    loginAsAdmin();
});

it('lists users', function () {
    User::factory()->count(3)->create();

    $this->get('/users')->assertOk();
});

it('creates a user with a hashed password', function () {
    $viewerRoleId = Role::where('name', 'Viewer')->value('id');

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'new.user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$viewerRoleId],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'new.user@example.com')->first();
    expect($user)->not->toBeNull()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'dup@example.com']);
    $viewerRoleId = Role::where('name', 'Viewer')->value('id');

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Dup',
            'email' => 'dup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$viewerRoleId],
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('requires at least one role', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'No Role',
            'email' => 'norole@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['roles' => 'required']);
});

it('keeps the existing password when left blank on edit', function () {
    $user = User::factory()->viewer()->create();
    $originalHash = $user->password;

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'roles' => $user->roles->pluck('id')->all(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->password)->toBe($originalHash);
});

it('prevents an admin from deactivating their own account', function () {
    $admin = auth()->user();

    Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->callAction('toggleActive');

    expect($admin->fresh()->is_active)->toBeTrue();
});

it('prevents an admin from removing their own Admin role', function () {
    $admin = auth()->user();
    $viewerRoleId = Role::where('name', 'Viewer')->value('id');

    Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm([
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => [$viewerRoleId],
        ])
        ->call('save');

    expect($admin->fresh()->hasRole('Admin'))->toBeTrue();
});

// Note: the "someone else demotes the last remaining Admin" branch of the
// last-admin guard in EditUser::beforeSave() is now unreachable via the UI —
// reaching EditUser requires being an Admin yourself (see AdminOnlyAccessTest),
// so a distinct actor implies at least 2 Admins exist, and self-demotion is
// covered separately by "prevents an admin from removing their own Admin role".

it('denies editor from reaching the edit form at all, so it can never grant the Admin role', function () {
    loginAsEditor();
    $target = User::factory()->viewer()->create();

    Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
        ->assertForbidden();

    expect($target->fresh()->hasRole('Admin'))->toBeFalse();
});

it('allows deactivating a user who is not the last active admin', function () {
    $otherAdmin = User::factory()->admin()->create();

    Livewire::test(EditUser::class, ['record' => $otherAdmin->getRouteKey()])
        ->callAction('toggleActive');

    expect($otherAdmin->fresh()->is_active)->toBeFalse();
});

it('records the last login time when a user logs in', function () {
    $user = User::factory()->viewer()->create(['last_login_at' => null]);

    event(new Login('web', $user, false));

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('returns 404 for a non-existent user', function () {
    $this->get('/users/99999')->assertNotFound();
});

it('denies viewer from creating a user', function () {
    loginAsViewer();

    Livewire::test(CreateUser::class)->assertForbidden();
});

it('denies editor access to the edit page entirely (toggleActive included)', function () {
    $user = User::factory()->create();
    loginAsEditor();

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertForbidden();
});

it('denies editor access to the list page entirely (deactivate bulk action included)', function () {
    User::factory()->create();
    loginAsEditor();

    Livewire::test(ListUsers::class)->assertForbidden();
});
