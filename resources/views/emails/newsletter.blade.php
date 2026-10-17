@extends('emails.layout')

@php
    $latestPosts = collect($latestPosts ?? []);
    $trendingPosts = collect($trendingPosts ?? []);
    $leadStory = $latestPosts->first();
    $moreStories = $latestPosts->slice(1);
    // Full web addresses: email apps can't load /storage/... links
    $imageFor = fn ($post) => $post->emailImageUrl();
    $summaryFor = fn ($post) => \Illuminate\Support\Str::limit(trim(strip_tags($post->excerpt ?: $post->content)), 150);
@endphp

@section('title', 'BriefMedia Newsletter')
@section('preheader', $leadStory ? 'Top story: ' . $leadStory->title : 'The latest stories from BriefMedia')

@section('content')
    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e4407f;">
        The Brief · {{ now()->format('F j, Y') }}
    </p>
    <h1 class="bm-h1" style="margin:0 0 8px; font-family:Georgia, 'Times New Roman', serif; font-size:28px; line-height:1.2; font-weight:bold; color:#121216;">
        This week's top stories
    </h1>
    <p style="margin:0 0 26px; color:#6e6e7a;">The news, people and moments everyone is talking about, in one quick read.</p>

    {{-- Lead story --}}
    @if($leadStory)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:28px;">
            @if($imageFor($leadStory))
                <tr>
                    <td>
                        <a href="{{ url('posts/' . $leadStory->slug) }}">
                            <img src="{{ $imageFor($leadStory) }}" alt="{{ $leadStory->title }}" width="520"
                                 style="display:block; width:100%; max-width:520px; height:auto; border:0; border-radius:14px;">
                        </a>
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding-top:16px;">
                    @if($leadStory->category)
                        <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#e4407f;">{{ $leadStory->category->name }}</p>
                    @endif
                    <a href="{{ url('posts/' . $leadStory->slug) }}" style="font-family:Georgia, 'Times New Roman', serif; font-size:23px; line-height:1.25; font-weight:bold; color:#121216; text-decoration:none;">
                        {{ $leadStory->title }}
                    </a>
                    <p style="margin:10px 0 16px; color:#3a3a44;">{{ $summaryFor($leadStory) }}</p>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="background-color:#121216; border-radius:999px;">
                                <a href="{{ url('posts/' . $leadStory->slug) }}" style="display:inline-block; padding:11px 22px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">Read the story →</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endif

    {{-- More stories --}}
    @if($moreStories->isNotEmpty())
        <p style="margin:0 0 4px; padding-top:22px; border-top:1px solid #e9e6e1; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#8a8a96;">Latest stories</p>
        @foreach($moreStories as $post)
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-bottom:1px solid #f0ede8;">
                <tr>
                    @if($imageFor($post))
                        <td class="bm-stack" width="120" valign="top" style="width:120px; padding:16px 16px 16px 0;">
                            <a href="{{ url('posts/' . $post->slug) }}">
                                <img class="bm-stack-img" src="{{ $imageFor($post) }}" alt="" width="120" height="90"
                                     style="display:block; width:120px; height:90px; object-fit:cover; border:0; border-radius:10px;">
                            </a>
                        </td>
                    @endif
                    <td class="bm-stack" valign="top" style="padding:16px 0;">
                        @if($post->category)
                            <p style="margin:0 0 4px; font-size:10.5px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#e4407f;">{{ $post->category->name }}</p>
                        @endif
                        <a href="{{ url('posts/' . $post->slug) }}" style="font-family:Georgia, 'Times New Roman', serif; font-size:17px; line-height:1.3; font-weight:bold; color:#121216; text-decoration:none;">{{ $post->title }}</a>
                        <p style="margin:6px 0 0; font-size:13.5px; line-height:1.55; color:#6e6e7a;">{{ \Illuminate\Support\Str::limit($summaryFor($post), 110) }}</p>
                    </td>
                </tr>
            </table>
        @endforeach
    @endif

    {{-- Trending --}}
    @if($trendingPosts->isNotEmpty())
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px; background-color:#121216; border-radius:16px;">
            <tr>
                <td style="padding:24px 24px 10px;">
                    <p style="margin:0; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#ff8fbc;">🔥 Trending now</p>
                </td>
            </tr>
            @foreach($trendingPosts->take(5) as $post)
                <tr>
                    <td style="padding:8px 24px {{ $loop->last ? '24px' : '8px' }};">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td width="34" valign="top" style="width:34px; font-family:Georgia, 'Times New Roman', serif; font-size:22px; font-weight:bold; line-height:1.2; color:#e4407f;">{{ $loop->iteration }}</td>
                                <td valign="top">
                                    <a href="{{ url('posts/' . $post->slug) }}" style="font-size:15px; line-height:1.4; font-weight:bold; color:#ffffff; text-decoration:none;">{{ $post->title }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Call to action --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
        <tr>
            <td align="center" style="padding:22px; border:1px solid #e9e6e1; border-radius:14px;">
                <p style="margin:0 0 14px; font-size:15px; color:#3a3a44;">There's more where that came from.</p>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="background-color:#e4407f; border-radius:999px;">
                            <a href="{{ url('/') }}" style="display:inline-block; padding:12px 26px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">Visit BriefMedia</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection

@section('footer_note')
    You're receiving this because you subscribed to the BriefMedia newsletter.
    @if(!empty($subscriber?->unsubscribe_token))
        <a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}" style="color:#6e6e7a; font-weight:bold;">Unsubscribe</a>
    @endif
@endsection
