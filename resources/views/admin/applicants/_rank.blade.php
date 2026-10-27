@if($application->screener_rank)
    <span class="rank-badge rank-{{ $application->screener_rank }}">{{ $application->rank_label }}</span>
@else
    <span class="rank-badge rank-none">Not ranked</span>
@endif
