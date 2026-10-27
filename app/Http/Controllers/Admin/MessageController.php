<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ApplicantMessageMail;
use App\Models\ApplicantMessage;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ApplicantMessage::with(['application.jobPosting:id,title', 'sender:id,name'])->latest();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('application', fn ($application) => $application
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return view('admin.messages.index', [
            'messages' => $query->paginate(25)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $admin = $request->user();

        // Logged only once the email has actually gone, so the history never shows an unsent message.
        try {
            Mail::to($application->email)->send(new ApplicantMessageMail(
                $application,
                $data['subject'],
                $data['body'],
                new Address($admin->email, $admin->name),
            ));
        } catch (\Exception $e) {
            Log::error('Failed to send applicant message: ' . $e->getMessage());

            return back()->withInput()->with('error', 'The email could not be sent. Please try again.');
        }

        $message = new ApplicantMessage($data);
        $message->job_application_id = $application->id;
        $message->sent_by = $admin->id;
        $message->save();

        return redirect(route('admin.applicants.show', $application) . '#messages')
            ->with('success', 'Message sent to ' . $application->email . '.');
    }
}
