<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| This backend is a pure API — there are no web views to serve.
| The Vue 3 SPA is served separately by Vite (dev) or a CDN (prod).
|
| DO NOT add a catch-all here — it would intercept /api/* requests
| that haven't matched yet and return the wrong response.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});
