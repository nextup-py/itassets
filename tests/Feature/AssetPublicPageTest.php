<?php

use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;

it('shows basic asset info to anyone, without logging in', function () {
    $asset = Asset::factory()->create([
        'asset_tag' => 'IT-0099',
        'name' => 'Notebook de prueba',
        'brand' => 'Dell',
        'model' => 'Latitude 5420',
        'serial_number' => 'SN-12345',
    ]);

    $response = $this->get(route('assets.public', $asset));

    $response->assertOk();
    $response->assertSee('IT-0099');
    $response->assertSee('Notebook de prueba');
    $response->assertSee('Dell / Latitude 5420');
    $response->assertSee('SN-12345');
});

it('does not reveal the assigned employee name on the public page', function () {
    $employee = Employee::factory()->create(['name' => 'Empleado Confidencial']);
    $asset = Asset::factory()->assigned()->create();
    $assignment = Assignment::factory()->create(['employee_id' => $employee->id]);
    $assignment->assets()->attach($asset->id, ['assigned_at' => now()]);

    $response = $this->get(route('assets.public', $asset));

    $response->assertOk();
    $response->assertDontSee('Empleado Confidencial');
});

it('shows a link to log in for the full asset view', function () {
    $asset = Asset::factory()->create();

    $response = $this->get(route('assets.public', $asset));

    $response->assertOk();
    $response->assertSee('Iniciar sesión para ver más');
});
