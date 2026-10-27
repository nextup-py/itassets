<?php

use App\Http\Middleware\ForceHttpsInProduction;
use Illuminate\Http\Request;

function callForceHttpsMiddleware(Request $request): mixed
{
    return (new ForceHttpsInProduction())->handle($request, fn (Request $r) => response('ok'));
}

it('redirects a plain http GET request to https in production', function () {
    app()->instance('env', 'production');

    $response = callForceHttpsMiddleware(Request::create('http://example.com/foo?bar=1', 'GET'));

    expect($response->getStatusCode())->toBe(301);
    expect($response->headers->get('Location'))->toBe('https://example.com/foo?bar=1');
});

it('does not redirect when the request is already secure', function () {
    app()->instance('env', 'production');

    $request = Request::create('https://example.com/foo', 'GET');
    $request->server->set('HTTPS', 'on');

    $response = callForceHttpsMiddleware($request);

    expect($response->getContent())->toBe('ok');
});

it('does not redirect the /up health check, even over plain http', function () {
    app()->instance('env', 'production');

    $response = callForceHttpsMiddleware(Request::create('http://example.com/up', 'GET'));

    expect($response->getContent())->toBe('ok');
});

it('does not redirect a non-GET request', function () {
    app()->instance('env', 'production');

    $response = callForceHttpsMiddleware(Request::create('http://example.com/foo', 'POST'));

    expect($response->getContent())->toBe('ok');
});

it('does not redirect outside production (e.g. local/testing)', function () {
    app()->instance('env', 'testing');

    $response = callForceHttpsMiddleware(Request::create('http://example.com/foo', 'GET'));

    expect($response->getContent())->toBe('ok');
});
