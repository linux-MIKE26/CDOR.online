<?php
// CDOR v5.0 HIGH VOLTAGE - EMAIL DESIGN SYSTEM
class EmailDesign
{

    public static function getTemplate($title, $message, $actionText = null, $actionUrl = null)
    {
        $year = date('Y');
        $primary = "#FFD700";
        $bg = "#050505";
        $surface = "#0a0a0a";

        $buttonHtml = "";
        if ($actionText && $actionUrl) {
            $buttonHtml = "
                <div style='margin-top: 40px; text-align: center;'>
                    <a href='$actionUrl' style='display: inline-block; padding: 16px 36px; background-color: $primary; color: #000; font-family: sans-serif; font-weight: 900; text-decoration: none; border-radius: 8px; text-transform: uppercase; letter-spacing: 2px;'>
                        $actionText
                    </a>
                </div>
            ";
        }

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>$title</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: $bg; font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: $bg;'>
                <tr>
                    <td align='center' style='padding: 40px 0;'>
                        <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: $surface; border: 1px solid #222; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);'>
                            
                            <!-- HEADER -->
                            <tr>
                                <td align='center' style='padding: 40px 0; background: linear-gradient(135deg, #000, #111); border-bottom: 2px solid $primary;'>
                                    <h1 style='color: $primary; font-family: \"Arial Black\", sans-serif; text-transform: uppercase; letter-spacing: 4px; margin: 0; font-size: 28px;'>
                                        CDOR <span style='color: #fff;'>// ONLINE</span>
                                    </h1>
                                    <p style='color: #666; font-size: 10px; margin-top: 10px; text-transform: uppercase; letter-spacing: 2px;'>
                                        SISTEMAS DE ALTO RENDIMIENTO
                                    </p>
                                </td>
                            </tr>

                            <!-- CONTENT -->
                            <tr>
                                <td style='padding: 50px 40px;'>
                                    <h2 style='color: #fff; font-size: 22px; margin-bottom: 25px; font-weight: 300;'>
                                        $title
                                    </h2>
                                    <div style='color: #ccc; line-height: 1.8; font-size: 16px;'>
                                        $message
                                    </div>
                                    $buttonHtml
                                </td>
                            </tr>

                            <!-- FOOTER -->
                            <tr>
                                <td align='center' style='padding: 30px; background-color: #000; border-top: 1px solid #1a1a1a;'>
                                    <p style='color: #444; font-size: 12px; margin: 0; text-transform: uppercase; letter-spacing: 1px;'>
                                        Este es un mensaje automático generado por el Núcleo CDOR.<br>
                                        &copy; $year MIKE CORREDOR. TODOS LOS DERECHOS RESERVADOS.
                                    </p>
                                    <div style='margin-top: 15px;'>
                                        <a href='https://cdor.online' style='color: $primary; font-size: 12px; text-decoration: none;'>cdor.online</a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    public static function send($to, $subject, $title, $message, $actionText = null, $actionUrl = null)
    {
        $html = self::getTemplate($title, $message, $actionText, $actionUrl);
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: CDOR System <web@cdor.online>" . "\r\n";
        $headers .= "X-Mailer: CDOR/5.0 HV" . "\r\n";

        return mail($to, $subject, $html, $headers);
    }
}
