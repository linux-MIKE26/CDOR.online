<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $message = $_POST['message'];
    $name = $_POST['name'] ?? 'Usuario';

    // 1. Save to Database
    try {
        require __DIR__ . '/app/config/bootstrap.php'; // Ensure Access
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, 'Mensaje Web', $message]);
    } catch (Exception $e) { /* Ignore */ }

    // 2. Send Email
    mail(
        'ceo@cdor.online',
        'Mensaje web de ' . $name,
        $message,
        'From: '.$email . "\r\nReply-To: " . $email
    );
}
header('Location: /contact.php');
exit;
