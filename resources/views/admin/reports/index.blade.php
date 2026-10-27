@extends('admin.admin_layout')

@php
    use App\Models\JobApplication;
    use Illuminate\Support\Str;

    $range = array_filter(['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]);
    $breakdowns = [
        'Status' => $byStatus->mapWithKeys(fn ($total, $value) => [Str::headline($value) => $total]),
        'Heard about us via' => $bySource->mapWithKeys(fn ($total, $value) => [$value ?: 'Not given' => $total]),
        'Work authorization' => $byAuthorization->mapWithKeys(fn ($total, $value) => [JobApplication::WORK_AUTHORIZATION[$value] ?? 'Not given' => $total]),
        'Availability to start' => $byAvailability->mapWithKeys(fn ($total, $value) => [JobApplication::AVAILABILITY[$value] ?? 'Not given' => $total]),
    ];
@endphp

@section('title', 'Reports')

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Admin', 'title' => 'Reports'])
        <a href="{{ route('admin.reports.index', $range + ['export' => 'csv']) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-2"></i> Export applications (CSV)
        </a>
    @endcomponent

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label for="from" class="form-label">From</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ $range['from'] ?? '' }}">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label for="to" class="form-label">To</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ $range['to'] ?? '' }}">
                </div>
                <div class="col-sm-4 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-gst">Update</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">All time</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['bi-file-earmark-person', 'Applications', $total],
            ['bi-calendar-check', 'Interviews', $interviews],
            ['bi-person-check', 'Hired', $hired],
        ] as [$icon, $label, $value])
            <div class="col-md-4">
                <div class="card stats-card h-100 mb-0">
                    <div class="card-body">
                        <span class="icon-box"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ number_format($value) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Applications by job</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Job</th><th class="text-end">Applications</th>
                            @foreach(JobApplication::RANKS as $label)<th class="text-end">{{ $label }}</th>@endforeach
                            <th class="text-end">Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byJob as $row)
                            <tr>
                                <td><a href="{{ route('admin.applicants.index', ['job' => $row->id]) }}" class="cell-title">{{ $row->title }}</a></td>
                                <td class="text-end">{{ $row->total }}</td>
                                @foreach(array_keys(JobApplication::RANKS) as $rank)<td class="text-end">{{ $row->$rank }}</td>@endforeach
                                <td class="text-end">{{ $row->hired }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No applications in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach($breakdowns as $title => $rows)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            @forelse($rows as $label => $count)
                                <tr><td>{{ $label }}</td><td class="text-end">{{ $count }}</td></tr>
                            @empty
                                <tr><td class="text-muted">No data.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Voluntary disability self-identification</h5></div>
                <div class="card-body">
                    <p class="small text-muted">Totals only. Individual answers are never shown with an application.</p>
                    <table class="table table-sm mb-0">
                        @foreach(\App\Models\DisabilitySelfIdentification::OPTIONS as $value => $label)
                            <tr><td>{{ $label }}</td><td class="text-end">{{ $disability[$value] ?? 0 }}</td></tr>
                        @endforeach
                        <tr><td>Left blank</td><td class="text-end">{{ max(0, $total - $disability->sum()) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
