{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>
    @foreach($urls as $item)
        <url>
            <loc>{{ $urlEncoder->encode($item->url) }}</loc>
            @if($item->lastmod)
                <lastmod>{{ $item->lastmod->copy()->timezone('UTC')->toAtomString() }}</lastmod>
            @endif

            @foreach($item->images as $image)
                <image:image>
                    <image:loc>{{ $urlEncoder->encode($image->url) }}</image:loc>
                    @if($image->title)
                        <image:title>{{ e($image->title) }}</image:title>
                    @endif
                    @if($image->caption)
                        <image:caption>{{ e($image->caption) }}</image:caption>
                    @endif
                </image:image>
            @endforeach
        </url>
    @endforeach
</urlset>
