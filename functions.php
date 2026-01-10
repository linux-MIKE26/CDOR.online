<?php
function sendStyledEmail($to, $subject, $title, $messageContent, $actionText = '', $actionUrl = '') {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: CDOR <no-reply@cdor.online>" . "\r\n";

    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { background-color: #09090b; color: #e4e4e7; font-family: sans-serif; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #18181b; padding: 20px; border-radius: 8px; border: 1px solid #27272a; }
            .code { font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #3b82f6; margin: 20px 0; }
            .btn { background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>$title</h2>
            <p>$messageContent</p>
            " . ($actionUrl ? "<div class='code'>$actionText</div>" : "") . "
            " . ($actionUrl ? "<p><a href='$actionUrl' class='btn'>Verificar</a></p>" : "") . "
        </div>
    </body>
    </html>";

    return mail($to, $subject, $body, $headers);
}