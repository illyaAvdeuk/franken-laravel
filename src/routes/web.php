<?php

use App\Models\Job;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));

Route::get('/about', fn () => view('about'));

Route::get('/contacts', fn () => view('contacts'));

Route::get('/jobs', fn () => view(
    'jobs', ['jobs' => Job::all()]
));

Route::get('/jobs/{id}', fn ($id) => view(
    'job', ['job' => Job::find($id)]
));
