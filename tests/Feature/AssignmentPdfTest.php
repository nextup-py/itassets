<?php

use App\Models\Assignment;

it('requires authentication to download an assignment pdf', function () {
    $assignment = Assignment::factory()->create();

    $response = $this->get(route('assignments.pdf', $assignment));

    $response->assertStatus(302);
});

it('allows an authenticated user with view_assignment permission to download the pdf', function () {
    loginAsAdmin();
    $assignment = Assignment::factory()->create();

    $response = $this->get(route('assignments.pdf', $assignment));

    $response->assertOk();
});
