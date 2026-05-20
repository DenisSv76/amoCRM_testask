<?php

use App\Models\Joke;
use Illuminate\Support\Facades\Route;

Route::get('/random_joke', function () {
    $jokes = Joke::all();
    return response()->json($jokes);
});
