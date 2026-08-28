@extends('main_layout')

@section('main_content')

<!-- ======= Terms of Service Hero ======= -->
<section class="legal-hero">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Legal</h2>
            <p>Terms of Service</p>
        </div>
        <p class="legal-hero-subtitle">
            The terms governing your use of the Global Scalable Technologies (GST) website and related services.
        </p>
    </div>
</section>

<!-- ======= Terms of Service Content ======= -->
<section id="terms-content" class="legal-page" aria-labelledby="terms-heading">
    <div class="container" data-aos="fade-up">
        <div class="legal-body">
            <h3 id="terms-heading" class="visually-hidden">Terms of Service</h3>

            <p class="legal-updated">Last updated: {{ date('F j, Y') }}</p>

            <p class="legal-intro">
                These Terms of Service (&ldquo;Terms&rdquo;) are a binding agreement between you and Global Scalable
                Technologies, Inc. (&ldquo;GST,&rdquo; &ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;) governing
                your access to and use of the website at gscalabletech.com, including its careers and job-application
                features and any related content or communications (collectively, the &ldquo;Site&rdquo;). By accessing or
                using the Site, you agree to these Terms and to our
                <a href="{{ route('privacy') }}">Privacy Policy</a>. If you do not agree, do not use the Site.
            </p>

            <p>
                These Terms govern the Site only. Professional services that GST provides to clients &mdash; including
                cybersecurity assessments, cloud and infrastructure work, software engineering, and services delivered
                under federal contract vehicles &mdash; are governed exclusively by a separately signed contract, task
                order, or statement of work, not by these Terms. Nothing on the Site is an offer to perform such services
                or a commitment to contract.
            </p>

            <h3>1. Eligibility</h3>
            <p>
                You must be at least 16 years old to use the Site and at least 18 years old, or the age of majority in
                your jurisdiction, to submit a job application or create an account. By using the Site you represent that
                you meet these requirements and that any information you provide is accurate and current.
            </p>

            <h3>2. Accounts</h3>
            <p>
                Some features require an account. You are responsible for maintaining the confidentiality of your login
                credentials and for all activity under your account. You agree to notify us promptly at
                <a href="mailto:support@gscalabletech.com">support@gscalabletech.com</a> of any unauthorized use. We may
                suspend or terminate accounts that violate these Terms or that we reasonably believe present a security or
                legal risk.
            </p>

            <h3>3. Job Applications and Submissions</h3>
            <ul>
                <li>You agree that all information in your application &mdash; including your resume, work history, education, and credentials &mdash; is truthful, accurate, and your own, and that you have the right to share it and any links you provide.</li>
                <li>Submitting an application does not create an offer of employment, a contract of employment, or any obligation on GST to interview, hire, or respond. Unless a written offer is signed, any employment with GST would be at will where permitted by law.</li>
                <li>GST is an equal-opportunity employer. We do not discriminate on the basis of race, color, religion, sex, sexual orientation, gender identity, national origin, age, disability, veteran status, genetic information, or any other status protected by applicable law.</li>
                <li>We handle the personal information in your application as described in our <a href="{{ route('privacy') }}">Privacy Policy</a>.</li>
                <li>You are responsible for ensuring that any file you upload is free of malware and does not infringe the rights of others. We may scan, reject, or remove submissions at our discretion.</li>
                <li>Except for personal information (which is governed by the Privacy Policy), any feedback, ideas, or suggestions you send us about the Site may be used by GST without restriction or obligation to you.</li>
            </ul>

            <h3>4. Acceptable Use</h3>
            <p>You agree not to:</p>
            <ul>
                <li>use the Site in violation of any law or regulation, or in support of any unlawful activity;</li>
                <li>attempt to gain unauthorized access to the Site, other users&rsquo; accounts, or any GST system or network, or probe, scan, or test the vulnerability of any system without our prior written authorization;</li>
                <li>interfere with or disrupt the Site, including by introducing malware, launching denial-of-service attacks, or placing unreasonable load on our infrastructure;</li>
                <li>scrape, harvest, or use automated means to collect data or content from the Site except as permitted by our robots file;</li>
                <li>submit false, misleading, fraudulent, or impersonating information, including in a job application;</li>
                <li>upload content that is unlawful, defamatory, harassing, or infringes intellectual-property or privacy rights; or</li>
                <li>reverse engineer, copy, or create derivative works of the Site except to the extent this restriction is prohibited by law.</li>
            </ul>
            <p>
                We appreciate responsible disclosure of security issues. If you believe you have found a vulnerability in
                the Site, contact <a href="mailto:support@gscalabletech.com">support@gscalabletech.com</a> before taking
                any further action.
            </p>

            <h3>5. Intellectual Property</h3>
            <p>
                The Site and its content &mdash; including text, graphics, logos, the GST name and marks, layout, and
                software &mdash; are owned by GST or its licensors and are protected by intellectual-property laws. We
                grant you a limited, revocable, non-exclusive, non-transferable license to access and use the Site for
                your personal, non-commercial purposes and, if applicable, to evaluate and pursue employment with GST. All
                other rights are reserved. Marks of third parties, including certification bodies and partner
                organizations, are the property of their respective owners and are used for identification only.
            </p>

            <h3>6. Third-Party Sites and Services</h3>
            <p>
                The Site links to external websites, including ScalePlusPro, AdvanceMentor Academy, and government
                resources. Those sites are not under our control, and we are not responsible for their content, policies,
                or practices. Your use of them is at your own risk and subject to their terms.
            </p>

            <h3>7. Disclaimers</h3>
            <p>
                The Site is provided &ldquo;as is&rdquo; and &ldquo;as available,&rdquo; without warranties of any kind,
                whether express, implied, or statutory, including implied warranties of merchantability, fitness for a
                particular purpose, title, and non-infringement. We do not warrant that the Site will be uninterrupted,
                timely, secure, or error-free, or that any content is accurate or complete. Content on the Site is for
                general informational purposes only and is not legal, security, or professional advice.
            </p>

            <h3>8. Limitation of Liability</h3>
            <p>
                To the fullest extent permitted by law, GST and its officers, directors, employees, and agents will not be
                liable for any indirect, incidental, special, consequential, exemplary, or punitive damages, or for any
                loss of profits, revenue, data, or goodwill, arising out of or relating to your use of or inability to use
                the Site, even if advised of the possibility of such damages. To the fullest extent permitted by law,
                GST&rsquo;s total liability for all claims relating to the Site will not exceed one hundred U.S. dollars
                (US$100). Some jurisdictions do not allow certain limitations, so some of the above may not apply to you.
            </p>

            <h3>9. Indemnification</h3>
            <p>
                You agree to indemnify and hold harmless GST from any claims, damages, liabilities, and expenses
                (including reasonable attorneys&rsquo; fees) arising out of your use of the Site, your submissions, or your
                violation of these Terms or of any law or third-party right.
            </p>

            <h3>10. Termination</h3>
            <p>
                We may suspend or terminate your access to the Site at any time, with or without notice, for any reason,
                including if we believe you have violated these Terms. Provisions that by their nature should survive
                termination &mdash; including intellectual-property, disclaimer, limitation-of-liability, indemnification,
                and governing-law provisions &mdash; will survive.
            </p>

            <h3>11. Governing Law and Disputes</h3>
            <p>
                These Terms are governed by the laws of the State of Maryland, United States, without regard to its
                conflict-of-laws rules. You agree that any dispute arising out of or relating to the Site or these Terms
                will be brought exclusively in the state or federal courts located in Maryland, and you consent to their
                jurisdiction, except that either party may seek injunctive relief in any court of competent jurisdiction.
                Nothing in these Terms limits any rights GST or a client may have under a federal contract or applicable
                federal law.
            </p>

            <h3>12. Changes to These Terms</h3>
            <p>
                We may modify these Terms from time to time. When we do, we will update the &ldquo;Last updated&rdquo;
                date above. Your continued use of the Site after changes take effect constitutes acceptance of the revised
                Terms.
            </p>

            <h3>13. Contact Us</h3>
            <div class="legal-contact">
                <p>Questions about these Terms can be directed to:</p>
                <p>
                    Global Scalable Technologies<br>
                    Email: <a href="mailto:support@gscalabletech.com">support@gscalabletech.com</a><br>
                    Phone: <a href="tel:+12403198823">+1 240-319-8823</a>
                </p>
            </div>
        </div>
    </div>
</section>

@endsection
