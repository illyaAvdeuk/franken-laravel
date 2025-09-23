<?php

use App\Models\Job;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));

Route::get('/about', fn () => view('about'));

Route::get('/contacts', fn () => view('contacts'));

Route::get('/jobs', function () {
    $page = request('page', 1);
    $queryParams = request()->except('page');
    $cacheKey = 'jobs.list.' . $page . '.' . md5(serialize($queryParams));

    $jobs = Cache::remember($cacheKey, 60, function () use ($queryParams) {
        $query = Job::select('id', 'title', 'salary');
        
        // Apply filters based on query parameters
        if (isset($queryParams['sort'])) {
            $query->orderBy($queryParams['sort']);
        }
        
        return $query->paginate(20)->toArray();
    });

    return view('jobs', ['jobs' => $jobs]);
});

Route::get('/jobs/{id}', fn ($id) => view(
    'job', ['job' => Job::with('employer')->findOrFail($id)]
));

Route::get('/ping', fn () => response('pong', 200));
