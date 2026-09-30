@push('head')
    <link rel="stylesheet" href="{{ asset('assets/vendor/tom-select-2.6.2/tom-select.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/trix-2.1.19/trix.css') }}">
    <style>
        trix-editor { min-height: 10rem; background: #fff; border-color: #dee2e6; border-radius: 0.375rem; }
        trix-editor.is-invalid { border-color: #dc3545; }
        trix-editor h3 { font-size: 1.15rem; font-weight: 600; }
        /* Files aren't supported in job descriptions; the script also removes the group. */
        trix-toolbar .trix-button-group--file-tools { display: none; }
        trix-toolbar .trix-button--underline { width: 2.6em; font-weight: 600; text-decoration: underline; }
    </style>
@endpush

@section('scripts')
    <script src="{{ asset('assets/vendor/tom-select-2.6.2/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/trix-2.1.19/trix.umd.min.js') }}"></script>
    <script src="{{ asset('assets/js/admin_job_form.js') }}"></script>
@endsection
