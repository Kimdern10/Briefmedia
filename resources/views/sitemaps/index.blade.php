{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ optional($posts->first())->updated_at?->toAtomString() ?? now()->toAtomString() }}</lastmod>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>
@foreach($categories as $category)
    <url>
        <loc>{{ route('category.page', $category->slug) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
@foreach($posts as $post)
    <url>
        <loc>{{ route('posts.show', $post->slug) }}</loc>
        <lastmod>{{ ($post->updated_at ?? $post->created_at)->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
@if($post->featured_image_1)
        <image:image>
            <image:loc>{{ asset('storage/'.$post->featured_image_1) }}</image:loc>
            <image:title>{{ $post->title }}</image:title>
        </image:image>
@endif
    </url>
@endforeach
    <url>
        <loc>{{ route('contact') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>{{ route('terms') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.2</priority>
    </url>
    <url>
        <loc>{{ route('privacy') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.2</priority>
    </url>
</urlset>
