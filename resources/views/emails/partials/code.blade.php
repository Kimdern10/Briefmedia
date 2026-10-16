{{--
    One-time code box used by the verification and password-reset emails.
    @param string|int $code
    @param string     $expiresIn
--}}
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:26px 0 22px;">
    <tr>
        <td align="center" style="background-color:#fdf0f5; border:1px dashed #f2a6c4; border-radius:14px; padding:22px 12px;">
            <p style="margin:0 0 6px; font-family:Arial, Helvetica, sans-serif; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#b0336a;">Your code</p>
            <p class="bm-code" style="margin:0; font-family:'Courier New', Courier, monospace; font-size:38px; font-weight:bold; letter-spacing:12px; color:#121216;">{{ $code }}</p>
            <p style="margin:8px 0 0; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#6e6e7a;">Expires in <strong style="color:#121216;">{{ $expiresIn }}</strong></p>
        </td>
    </tr>
</table>
