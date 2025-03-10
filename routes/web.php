<?php

use App\Models\User;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JobApplicantController;
use App\Http\Controllers\AuthRedirectsController;
use App\Models\JobApplication;

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



Route::get('/auth_redirect' , [AuthRedirectsController::class, 'index'])->middleware(['auth' , 'verified'])->name('auth.redirect');


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


// Job listing routes for applicants
Route::middleware(['auth' , 'verified' ,'can:user-is-an-applicant'])->group(function(){
    Route::get('/jobs/{slug}/apply' , [JobApplicantController::class, 'apply'])->name('job.apply');
    Route::post('/saveJobApplication', [JobApplicantController::class, 'store'])->name('jobapplication.store');
});
Route::get('/careers', [JobApplicantController::class, 'index'])->name('careers');
Route::get('/applicantjobs/search', [JobApplicantController::class, 'search'])->name('jobapplicant.search');
Route::get('/applicantjobs/{slug}', [JobApplicantController::class, 'show'])->name('jobapplicant.show');

// // Optional API routes for getting dynamic data
// Route::get('/api/jobs/employment-types', [JobApplicantController::class, 'getEmploymentTypes'])->name('api.jobs.employment-types');
// Route::get('/api/jobs/experience-levels', [JobApplicantController::class, 'getExperienceLevels'])->name('api.jobs.experience-levels');