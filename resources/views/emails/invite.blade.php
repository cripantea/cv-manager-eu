<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invito CV Manager EU</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1F3864;padding:32px 40px;">
                            <p style="margin:0;color:#ffffff;font-size:20px;font-weight:bold;">CV Manager EU</p>
                            <p style="margin:4px 0 0;color:#a8bdd8;font-size:13px;">Uni Systems</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">
                            <p style="margin:0 0 16px;font-size:16px;color:#111827;">Ciao <strong>{{ $user->name }}</strong>,</p>

                            <p style="margin:0 0 16px;font-size:15px;color:#374151;line-height:1.6;">
                                <strong>Irene Sampieri</strong> ti ha invitato a compilare il tuo CV nel portale
                                <strong>CV Manager EU</strong> di Uni Systems.
                            </p>

                            <p style="margin:0 0 32px;font-size:15px;color:#374151;line-height:1.6;">
                                Clicca il bottone qui sotto per impostare la tua password e accedere alla piattaforma.
                            </p>

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
                                <tr>
                                    <td style="background-color:#1F3864;border-radius:6px;">
                                        <a href="{{ url('/reset-password/' . $token . '?email=' . urlencode($user->email)) }}"
                                           style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">
                                            Imposta la tua password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                                Se non riesci a cliccare il bottone, copia e incolla questo link nel browser:<br>
                                <a href="{{ url('/reset-password/' . $token . '?email=' . urlencode($user->email)) }}"
                                   style="color:#1F3864;word-break:break-all;">
                                    {{ url('/reset-password/' . $token . '?email=' . urlencode($user->email)) }}
                                </a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 40px;background-color:#f9fafb;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                                Se non hai richiesto questo invito, ignora questa email. Nessuna azione è richiesta da parte tua.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
