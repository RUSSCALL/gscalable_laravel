@component('mail::message')
# Thank You for Your Application

Dear {{ $application->first_name }} {{ $application->last_name }},

Thank you for applying for the **{{ $jobPosting->title }}** position at Global Scalable Technologies.

We have received your application and will review it shortly. Your application reference is **{{ $application->reference }}** — please quote it if you get in touch about this application.

If your qualifications match our requirements, our hiring team will contact you for the next steps in the selection process.

Thank you for your interest in joining our team!

Best regards,  
The Recruitment Team

@endcomponent