<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;

class SitemapController extends Controller
{
    /**
     * Public pages plus every open job, for search engines.
     */
    public function index()
    {
        $jobs = JobPosting::active()
            ->orderBy('published_at', 'desc')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', ['jobs' => $jobs])
            ->header('Content-Type', 'application/xml');
    }
}
