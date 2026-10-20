@extends('layouts.app')

@section('title', 'About BriefMedia')
@section('meta_description', 'Who we are, what BriefMedia covers and how we report the news: our editorial standards, corrections policy and how to reach the newsroom.')

@section('content')
@php
    $sections = \App\Models\Category::whereHas('posts', fn ($query) => $query->where('status', 'published'))
        ->orderBy('name')
        ->get(['name', 'slug']);
@endphp

<div class="bm-container">
<article class="bm-page">
    <span class="bm-section__eyebrow">About us</span>

    <h1>About BriefMedia</h1>

    <p><strong>BriefMedia</strong> is an online news publication that brings readers clear, timely reporting on the stories that shape their lives, from politics and public affairs to health, business, sports and entertainment. Our aim is simple: tell readers what happened, why it matters and what comes next, in plain language and without the noise.</p>

    <h3>What we cover</h3>
    <p>Our newsroom publishes breaking news as it happens, follow-up reports as stories develop, and live coverage of major events such as elections.</p>
    @if($sections->isNotEmpty())
        <p>You can follow each of our sections:</p>
        <ul>
            @foreach($sections as $section)
                <li><a href="{{ route('category.page', $section->slug) }}">{{ $section->name }}</a></li>
            @endforeach
        </ul>
    @endif

    <h3>How we report</h3>
    <ul>
        <li><strong>Accuracy first.</strong> We check facts before we publish and say clearly when information is still unconfirmed.</li>
        <li><strong>Clear sources.</strong> We tell readers where information comes from: official statements, documents, eyewitnesses or other news organisations.</li>
        <li><strong>Fairness.</strong> We give people and organisations named in a story the chance to respond.</li>
        <li><strong>Independence.</strong> Advertising never decides what we cover or how we cover it. Ads on this site are clearly separate from our journalism.</li>
        <li><strong>Named authors.</strong> Every story shows who wrote it, and each writer has a page listing their work.</li>
    </ul>

    <h3>Corrections</h3>
    <p>When we get something wrong, we fix it and update the story. If you spot an error, please <a href="{{ route('contact') }}">tell us</a> and include the link to the story.</p>

    <h3>Comments and community</h3>
    <p>Readers can like, save and comment on stories. Comments are reviewed before they appear, and we remove anything abusive, misleading or illegal. See our <a href="{{ route('terms') }}">Terms &amp; Conditions</a>.</p>

    <h3>Privacy</h3>
    <p>We collect as little information as possible. Our <a href="{{ route('privacy') }}">Privacy Policy</a> explains what we collect, including cookies used for analytics and advertising, and how to control it.</p>

    <h3>Contact the newsroom</h3>
    <p>Have a news tip, a correction or a question? <a href="{{ route('contact') }}">Contact us</a> or reach us on our <a href="https://www.facebook.com/Briefmediablog" target="_blank" rel="noopener">Facebook page</a>.</p>
</article>
</div>
@endsection
