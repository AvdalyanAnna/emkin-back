<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('optimize')->group(function () {
    Route::get('/', function () {
        \Illuminate\Support\Facades\Artisan::call('optimize');
        return 'caches [configs,routes] has clear successfully !';
    });

    Route::get('clear', function () {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return '[events,views,cache,route,config,compiled] has cleared successfully !';
    });
});

Route::prefix('config')->group(function () {
    Route::get('cache', function () {
        \Illuminate\Support\Facades\Artisan::call('config:cache');
        return 'Config cache has cleared and new generated successfully !';
    });

    Route::get('clear', function () {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        return 'Application cache has clear successfully!';
    });
});

Route::prefix('route')->group(function () {
    Route::get('cache', function () {
        \Illuminate\Support\Facades\Artisan::call('route:cache');
        return 'Route cache has cleared and new generated successfully !';
    });

    Route::get('clear', function () {
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        return 'Application Route has clear successfully!';
    });
});

Route::get('test-job', function (\Illuminate\Http\Request $request) {
    \App\Jobs\TestJob::dispatch();
    return 'Job Created Successfully';
});
