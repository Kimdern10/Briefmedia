{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach($posts as $post)
    <url>
        <loc>{{ route('posts.show', $post->slug) }}</loc>
        <news:news>
            <news:publication>
                <news:name>{{ $publicationName }}</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>{{ $post->created_at->toAtomString() }}</news:publication_date>
            <news:title>{{ $post->title }}</news:title>
@if($post->meta_keywords)
            <news:keywords>{{ Str::limit($post->meta_keywords, 250, '') }}</news:keywords>
@endif
        </news:news>
    </url>
@endforeach
</urlset>
