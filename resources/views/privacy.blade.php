@extends('main_layout')

@section('main_content')

<!-- ======= Privacy Policy Hero ======= -->
<section class="legal-hero">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Legal</h2>
            <p>Privacy Policy</p>
        </div>
        <p class="legal-hero-subtitle">
            How Global Scalable Technologies (GST) collects, uses, shares, and protects your information.
        </p>
    </div>
</section>

<!-- ======= Privacy Policy Content ======= -->
<section id="privacy-content" class="legal-page" aria-labelledby="privacy-heading">
    <div class="container" data-aos="fade-up">
        <div class="legal-body">
            <h3 id="privacy-heading" class="visually-hidden">Privacy Policy</h3>

            <p class="legal-updated">Last updated: {{ date('F j, Y') }}</p>

            <p class="legal-intro">
                Global Scalable Technologies, Inc. (&ldquo;GST,&rdquo; &ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;)
                is a cybersecurity and technology services company serving U.S. federal agencies and enterprise clients.
                This Privacy Policy explains what personal information we collect through our website at gscalabletech.com
                (the &ldquo;Site&rdquo;), our recruiting and job-application process, and related communications, and how
                we use and protect that information. It does not apply to information we process on behalf of our clients
                under a contract or statement of work, where the client is the data controller and its own privacy notices
                and our contractual terms govern.
            </p>

            <h3>1. Information We Collect</h3>

            <h4>Information you provide directly</h4>
            <ul>
                <li><strong>Account registration.</strong> If you create an account, we collect your name, email address, and a password (stored only in hashed form). We also record email-verification status.</li>
                <li><strong>Job applications.</strong> When you apply for a position, we collect the information you submit through the application form, which may include: your first and last name, email address, phone number, cover letter, resume/CV file, links to your LinkedIn, GitHub, and portfolio or personal website, a summary of your skills, your current employer and job title, your education history and highest degree, your years of experience, your expected salary, how you heard about the role, and any additional information you choose to provide. If you are signed in when you apply, your application is linked to your account.</li>
                <li><strong>Contact and other correspondence.</strong> If you use the contact feature on our Site, your message is composed in and sent from your own email application, so GST receives whatever name, email address, and message content you choose to send. If you email or call us directly, we receive the information contained in that communication.</li>
            </ul>

            <h4>Information collected automatically</h4>
            <ul>
                <li><strong>Server and session data.</strong> Like most websites, our servers automatically record technical information such as your IP address, browser type, device and operating system information, referring pages, and the dates and times of your requests. We use a session cookie and a security (CSRF) token that are strictly necessary for the Site to function and to keep you signed in.</li>
                <li><strong>Aggregate usage counts.</strong> We keep non-identifying tallies such as how many times a job posting has been viewed or applied to. These counts are not tied to individual visitors.</li>
            </ul>

            <p>
                We do not use third-party advertising cookies or sell behavioral advertising space. We do not knowingly
                collect personal information from children under 16, and the Site is not directed to children.
            </p>

            <h3>2. How We Use Your Information</h3>
            <ul>
                <li>To operate, maintain, secure, and improve the Site and our services;</li>
                <li>To create and manage your account and authenticate you;</li>
                <li>To receive, evaluate, and respond to your job application, communicate with you about your candidacy, schedule interviews, conduct reference and background checks where permitted and with any legally required consent, and &mdash; if you are hired &mdash; to initiate onboarding;</li>
                <li>To send transactional messages, such as the confirmation email you receive after submitting an application, and to respond to your inquiries;</li>
                <li>To keep a record of applicants and applications for a reasonable period so we may consider you for other suitable roles and demonstrate compliance with equal-opportunity, recordkeeping, and other legal obligations;</li>
                <li>To detect, investigate, and prevent fraud, abuse, security incidents, and violations of our Terms of Service; and</li>
                <li>To comply with applicable law and respond to lawful requests from government authorities, including under the contracts through which GST supports federal agencies.</li>
            </ul>

            <h3>3. Legal Bases for Processing</h3>
            <p>
                Where required by law, we rely on the following legal bases: performance of a contract or taking steps at
                your request before entering a contract (including processing your job application); our legitimate
                interests in operating and securing our business and evaluating candidates; your consent, where we ask for
                it (which you may withdraw at any time); and compliance with our legal obligations.
            </p>

            <h3>4. How We Share Information</h3>
            <p>We do not sell your personal information. We share it only as described below:</p>
            <ul>
                <li><strong>Service providers.</strong> With vendors that host our infrastructure, store uploaded files, send email on our behalf, and provide similar operational support, under contracts that limit their use of the information to providing services to us.</li>
                <li><strong>Within GST and its ecosystem.</strong> With personnel involved in hiring decisions. We do not share applicant data with ScalePlusPro, AdvanceMentor Academy, or other affiliated entities for their own purposes without your consent.</li>
                <li><strong>Corporate transactions.</strong> In connection with a merger, acquisition, financing, or sale of assets, subject to customary confidentiality protections.</li>
                <li><strong>Legal and safety.</strong> When we believe disclosure is required by law, regulation, legal process, or government request, or is necessary to protect the rights, property, or safety of GST, our clients, our personnel, or the public.</li>
            </ul>

            <h3>5. Data Retention</h3>
            <p>
                We keep account information for as long as your account is active and for a reasonable period afterward.
                We retain job-application records (including resumes) for the duration of the recruitment process and for a
                limited period afterward &mdash; generally up to two years unless a longer period is required by law or a
                shorter period is requested &mdash; so we can consider you for future openings and meet recordkeeping
                obligations. Server logs are kept for a short period for security and troubleshooting. When information is
                no longer needed, we delete or de-identify it.
            </p>

            <h3>6. Data Security</h3>
            <p>
                We use administrative, technical, and physical safeguards designed to protect personal information,
                including encryption of traffic to the Site, hashed storage of passwords, access controls that limit who
                can view application data, and restricted storage of uploaded resume files. No method of transmission or
                storage is completely secure, and we cannot guarantee absolute security.
            </p>

            <h3>7. Your Rights and Choices</h3>
            <p>
                Depending on where you live, you may have the right to request access to the personal information we hold
                about you, to have it corrected or deleted, to receive a copy in a portable format, to object to or
                restrict certain processing, and to withdraw consent. You may also ask us to remove your resume and
                application from consideration for future roles. To exercise any of these rights, contact us using the
                details below. We will verify your request and respond within the time required by applicable law. We will
                not discriminate against you for exercising these rights.
            </p>
            <p>
                You can update or delete your account information by signing in, and you can opt out of non-transactional
                emails by following the unsubscribe instructions in them. Because our session and security cookies are
                strictly necessary, the Site may not function correctly if your browser blocks them.
            </p>

            <h3>8. International Users</h3>
            <p>
                GST is based in the United States and our systems are operated in the United States. If you access the
                Site or apply for a role from outside the United States, your information will be transferred to and
                processed in the United States, where data-protection laws may differ from those in your country.
            </p>

            <h3>9. Third-Party Links</h3>
            <p>
                The Site links to external sites, including ScalePlusPro, AdvanceMentor Academy, and government resources.
                We are not responsible for the privacy practices of those sites, and this Policy does not apply to them.
            </p>

            <h3>10. Changes to This Policy</h3>
            <p>
                We may update this Privacy Policy from time to time. When we do, we will revise the &ldquo;Last
                updated&rdquo; date above, and significant changes may be communicated through the Site.
            </p>

            <h3>11. Contact Us</h3>
            <div class="legal-contact">
                <p>If you have questions about this Privacy Policy or how we handle your information, contact:</p>
                <p>
                    Global Scalable Technologies<br>
                    Email: <a href="mailto:reply@gscalabletech.com">reply@gscalabletech.com</a><br>
                    Phone: <a href="tel:+12403198823">+1 240-319-8823</a>
                </p>
            </div>
        </div>
    </div>
</section>

@endsection
