<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PostTooLargeException $e, $request) {
            return redirect()->back()
                ->withInput($request->except('resume_path'))
                ->with('error', 'The uploaded file is too large. Maximum allowed size is ' . ini_get('upload_max_filesize') . '.');
        });
    }
}
