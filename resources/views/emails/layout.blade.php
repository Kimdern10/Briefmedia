{{--
    Shared BriefMedia email layout.
    Table-based with inline styles so it renders consistently in Gmail, Outlook and Apple Mail.
    Sections: preheader (inbox preview text), content, footer_note (optional extra footer line).
--}}
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title', 'BriefMedia')</title>
    <style>
        @media only screen and (max-width: 620px) {
            .bm-shell { width: 100% !important; }
            .bm-pad { padding-left: 22px !important; padding-right: 22px !important; }
            .bm-h1 { font-size: 24px !important; }
            .bm-stack { display: block !important; width: 100% !important; }
            .bm-stack-img { width: 100% !important; height: auto !important; margin-bottom: 12px !important; }
            .bm-code { font-size: 30px !important; letter-spacing: 8px !important; }
        }
        a { color: #e4407f; }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f3f0; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

    {{-- Inbox preview text (hidden in the email body) --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; mso-hide:all;">
        @yield('preheader')&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f3f0;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" class="bm-shell" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px; max-width:600px;">

                    {{-- Brand header --}}
                    <tr>
                        <td align="left" style="padding:0 4px 18px;">
                            <a href="{{ url('/') }}" style="text-decoration:none;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td width="36" height="36" align="center" valign="middle"
                                            style="width:36px; height:36px; background-color:#e4407f; border-radius:10px; color:#ffffff; font-family:Georgia, 'Times New Roman', serif; font-size:20px; font-weight:bold; line-height:36px;">B</td>
                                        <td style="padding-left:10px; font-family:Arial, Helvetica, sans-serif; font-size:21px; font-weight:bold; color:#121216; letter-spacing:-0.3px;">
                                            Brief<span style="color:#e4407f;">Media</span>
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#ffffff; border-radius:18px; border:1px solid #e9e6e1; overflow:hidden;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td height="5" style="height:5px; line-height:5px; font-size:0; background-color:#e4407f; border-radius:18px 18px 0 0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="bm-pad" style="padding:36px 40px 40px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:1.65; color:#3a3a44;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" class="bm-pad" style="padding:26px 30px 8px; font-family:Arial, Helvetica, sans-serif; font-size:12.5px; line-height:1.7; color:#8a8a96;">
                            <p style="margin:0 0 10px;">
                                <a href="https://www.facebook.com/BriefMediablog" style="color:#6e6e7a; text-decoration:none; font-weight:bold;">Facebook</a>
                                &nbsp;·&nbsp;
                                <a href="{{ url('/') }}" style="color:#6e6e7a; text-decoration:none; font-weight:bold;">Website</a>
                                &nbsp;·&nbsp;
                                <a href="{{ route('contact') }}" style="color:#6e6e7a; text-decoration:none; font-weight:bold;">Contact us</a>
                            </p>
                            @hasSection('footer_note')
                                <p style="margin:0 0 10px;">@yield('footer_note')</p>
                            @endif
                            <p style="margin:0;">© {{ date('Y') }} BriefMedia · Awka, Anambra, Nigeria</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
