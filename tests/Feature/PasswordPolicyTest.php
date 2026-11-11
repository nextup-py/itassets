<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

afterEach(function () {
    app()->instance('env', 'testing');
});

it('enforces a stronger password policy in production', function () {
    app()->instance('env', 'production');

    // Fails on mixedCase (all-lowercase) before ever reaching the
    // uncompromised() check, so this needs no outbound HTTP call.
    $validator = Validator::make(
        ['password' => 'password123'],
        ['password' => Password::default()],
    );

    expect($validator->fails())->toBeTrue();
});

it('keeps a lenient password policy outside production', function () {
    app()->instance('env', 'testing');

    $validator = Validator::make(
        ['password' => 'password123'],
        ['password' => Password::default()],
    );

    expect($validator->fails())->toBeFalse();
});
