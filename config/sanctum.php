<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Stateful Domains
	|--------------------------------------------------------------------------
	|
	| Requests from the following domains / hosts will receive stateful API
	| authentication cookies. Typically, these should include your local
	| and production domains which access your API via a frontend SPA.
	|
	*/

	'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
		'%s%s',
		'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000,::1',
		env('APP_URL') ? ',' . parse_url(env('APP_URL'), PHP_URL_HOST) : ''
	))),

	/*
	|--------------------------------------------------------------------------
	| Expiration Minutes
	|--------------------------------------------------------------------------
	|
	| This value controls the number of minutes until an issued token will be
	| considered expired. If this value is null, personal access tokens do
	| not expire. This won't tweak the lifetime of first-party sessions.
	|
	*/

	'expiration' => null,

	/*
	|--------------------------------------------------------------------------
	| Route Path Prefix
	|--------------------------------------------------------------------------
	|
	| This value controls the prefix to sanctum's route path to start a
	| stateful section. This keeps our application consistent, with all routes
	| accessible to our webapp starting with "api"
	|
	*/

	'prefix' => 'api/sanctum',

	/*
	|--------------------------------------------------------------------------
	| Sanctum Middleware
	|--------------------------------------------------------------------------
	|
	| When authenticating your first-party SPA with Sanctum you may need to
	| customize some of the middleware Sanctum uses while processing the
	| request. You may change the middleware listed below as required.
	|
	*/

	'middleware' => [
		'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
		'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
	],

];