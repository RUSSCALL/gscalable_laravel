{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach (['home', 'contract_vehicles', 'careers', 'privacy', 'terms'] as $page)
  <url>
    <loc>{{ route($page) }}</loc>
  </url>
@endforeach
@foreach ($jobs as $job)
  <url>
    <loc>{{ route('careers.show', $job->slug) }}</loc>
@if ($job->updated_at)
    <lastmod>{{ $job->updated_at->toAtomString() }}</lastmod>
@endif
  </url>
@endforeach
</urlset>
