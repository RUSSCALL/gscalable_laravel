<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JobApplicantController;
use App\Http\Controllers\ApplicantAccountController;
use App\Http\Controllers\ApplicantDashboardController;
use App\Http\Controllers\AuthRedirectsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/contract_vehicles' , function() {
    return view('contract-vehicles');
})->name('contract_vehicles');

Route::get('/privacy', function() {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function() {
    return view('terms');
})->name('terms');



Route::get('/auth_redirect' , [AuthRedirectsController::class, 'index'])->middleware(['auth' , 'verified'])->name('auth.redirect');

// Applicant dashboard — read-only view of their own applications.
Route::get('/dashboard', [ApplicantDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'can:user-is-an-applicant'])
    ->name('applicant.dashboard');


// Admin Routes
Route::middleware(['auth' , 'verified' , 'can:user-is-admin'])->group(function(){
    Route::get('/admin_dashboard', [AdminController::class, 'index'])->name('AdminDashboard');
    Route::get('/job_listings', [AdminController::class, 'listings'])->name('jobListings');

    Route::post('/jobs', [AdminController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{id}', [AdminController::class, 'show'])->name('jobs.show');
    Route::get('/jobs/{id}/edit', [AdminController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{id}', [AdminController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{id}', [AdminController::class, 'destroy'])->name('jobs.destroy');
    
    // Job status changes
    Route::put('/jobs/{id}/publish', [AdminController::class, 'publish'])->name('jobs.publish');
    Route::put('/jobs/{id}/unpublish', [AdminController::class, 'unpublish'])->name('jobs.unpublish');

});


/*
|--------------------------------------------------------------------------
| Careers (applicant-facing)
|--------------------------------------------------------------------------
| Everything applicant-facing lives under /careers/*. The board handles its
| own filtering from the query string, so there is no separate search route.
*/
Route::get('/careers', [JobApplicantController::class, 'index'])->name('careers');

// Declared before /careers/{slug} so the wildcard cannot swallow it.
Route::post('/careers/claim-account', [ApplicantAccountController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('careers.claim');

Route::get('/careers/{slug}', [JobApplicantController::class, 'show'])->name('careers.show');

// Applying is open to guests: an account is offered after the fact, never
// required to apply. Throttled as a backstop the honeypot cannot provide.
Route::get('/careers/{slug}/apply', [JobApplicantController::class, 'apply'])->name('careers.apply');
Route::post('/careers/{slug}/apply', [JobApplicantController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('careers.apply.store');
Route::get('/careers/{slug}/applied', [JobApplicantController::class, 'applied'])->name('careers.applied');

// Legacy URLs — keep old links, bookmarks and inbound SEO alive.
Route::permanentRedirect('/applicantjobs/search', '/careers');
Route::get('/applicantjobs/{slug}', function ($slug) {
    return redirect()->route('careers.show', $slug, 301);
});
Route::get('/jobs/{slug}/apply', function ($slug) {
    return redirect()->route('careers.apply', $slug, 301);
})->where('slug', '[A-Za-z][A-Za-z0-9\-]*');