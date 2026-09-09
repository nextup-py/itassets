<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| "Administración" is Admin-only
|--------------------------------------------------------------------------
|
| Editor actually holds the view_any_user / view_user Spatie permissions
| (RoleSeeder grants Editor every permission except delete_* and the extras),
| so a plain permission check is not enough to keep Editor out of Users. Same
| story for Roles & Permissions, which ship from the Althinect plugin with no
| authorization of their own — see app/Policies/{Role,Permission}Policy.php.
|
*/

it('hides Users from the navigation for editor and viewer', function () {
    foreach (['loginAsEditor', 'loginAsViewer'] as $login) {
        $login();

        Livewire::test(ListUsers::class)->assertForbidden();

        $this->get('/users')->assertForbidden();
    }
});

it('allows admin to access Users', function () {
    loginAsAdmin();

    $this->get('/users')->assertOk();
});

it('blocks editor and viewer from the Roles screen', function () {
    foreach (['loginAsEditor', 'loginAsViewer'] as $login) {
        $login();

        $this->get('/roles')->assertForbidden();
    }
});

it('blocks editor and viewer from the Permissions screen', function () {
    foreach (['loginAsEditor', 'loginAsViewer'] as $login) {
        $login();

        $this->get('/permissions')->assertForbidden();
    }
});

it('allows admin to access Roles and Permissions', function () {
    loginAsAdmin();

    $this->get('/roles')->assertOk();
    $this->get('/permissions')->assertOk();
});
