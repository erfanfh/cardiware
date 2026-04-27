{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($items as $item)
        <sitemap>
            <loc>{{ $urlEncoder->encode($item->url) }}</loc>
            @if($item->lastmod)
                <lastmod>{{ $item->lastmod->copy()->timezone('UTC')->toAtomString() }}</lastmod>
            @endif
        </sitemap>
    @endforeach
</sitemapindex>
