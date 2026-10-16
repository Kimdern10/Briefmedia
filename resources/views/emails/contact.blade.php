@extends('emails.layout')

@section('title', 'New contact form message')
@section('preheader', 'New message from ' . ($data['name'] ?? 'a visitor') . ': ' . ($data['subject'] ?? 'No subject'))

@section('content')
    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e4407f;">Contact form</p>
    <h1 class="bm-h1" style="margin:0 0 22px; font-family:Georgia, 'Times New Roman', serif; font-size:26px; line-height:1.25; font-weight:bold; color:#121216;">
        {{ $data['subject'] ?? 'New message' }}
    </h1>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e9e6e1; border-radius:12px; border-collapse:separate;">
        <tr>
            <td style="padding:12px 16px; width:90px; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#8a8a96; border-bottom:1px solid #e9e6e1;">From</td>
            <td style="padding:12px 16px; font-size:15px; color:#121216; font-weight:bold; border-bottom:1px solid #e9e6e1;">{{ $data['name'] ?? 'Not provided' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#8a8a96; border-bottom:1px solid #e9e6e1;">Email</td>
            <td style="padding:12px 16px; font-size:15px; border-bottom:1px solid #e9e6e1;">
                <a href="mailto:{{ $data['email'] ?? '' }}" style="color:#e4407f; text-decoration:none;">{{ $data['email'] ?? 'Not provided' }}</a>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 16px; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#8a8a96;">Sent</td>
            <td style="padding:12px 16px; font-size:15px; color:#3a3a44;">{{ now()->format('l, F j, Y \a\t g:i A') }}</td>
        </tr>
    </table>

    <p style="margin:24px 0 8px; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#8a8a96;">Message</p>
    <div style="padding:18px 20px; background-color:#f7f5f2; border-left:3px solid #e4407f; border-radius:0 12px 12px 0; font-size:15px; line-height:1.7; color:#121216; white-space:pre-line;">{{ $data['message'] ?? '' }}</div>

    @if(!empty($data['email']))
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:26px;">
            <tr>
                <td style="background-color:#121216; border-radius:999px;">
                    <a href="mailto:{{ $data['email'] }}?subject={{ rawurlencode('Re: ' . ($data['subject'] ?? 'Your message to BriefMedia')) }}"
                       style="display:inline-block; padding:12px 24px; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">
                        Reply to {{ Str::before($data['name'] ?? 'sender', ' ') }}
                    </a>
                </td>
            </tr>
        </table>
    @endif
@endsection
