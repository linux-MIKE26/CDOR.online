<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">

    <!-- Primary Meta Tags -->
    <title><?= isset($title) ? htmlspecialchars($title) : 'Mike Corredor // Full Stack Developer' ?></title>
    <meta name="description"
        content="<?= isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Portafolio profesional de Mike Corredor. Desarrollador Full-Stack especializado en PHP, arquitectura de alto rendimiento y seguridad web.' ?>">
    <meta name="author" content="Mike Corredor">
    <meta name="theme-color" content="#020203">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="<?= isset($canonicalUrl) ? htmlspecialchars($canonicalUrl) : 'https://cdor.online' ?>">
    <meta property="og:title"
        content="<?= isset($title) ? htmlspecialchars($title) : 'Mike Corredor // Full Stack Developer' ?>">
    <meta property="og:description"
        content="<?= isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Portafolio profesional de Mike Corredor. Desarrollador Full-Stack especializado en PHP, arquitectura de alto rendimiento y seguridad web.' ?>">
    <meta property="og:image" content="https://cdor.online/assets/images/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url"
        content="<?= isset($canonicalUrl) ? htmlspecialchars($canonicalUrl) : 'https://cdor.online' ?>">
    <meta property="twitter:title"
        content="<?= isset($title) ? htmlspecialchars($title) : 'Mike Corredor // Full Stack Developer' ?>">
    <meta property="twitter:description"
        content="<?= isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Portafolio profesional de Mike Corredor' ?>">
    <meta property="twitter:image" content="https://cdor.online/assets/images/og-image.jpg">

    <!-- Canonical -->
    <link rel="canonical" href="<?= isset($canonicalUrl) ? htmlspecialchars($canonicalUrl) : 'https://cdor.online' ?>">

    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Critical Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.min.css?v=6.0.0">

    <!-- Favicon (Premium branding) -->
    <link rel="icon" href="/assets/css/images/favicon_final.png" type="image/png">
    <link rel="shortcut icon" href="/assets/css/images/favicon_final.png" type="image/png">

    <!-- Critical Inlined CSS -->
    <style>
        :root {
            --bg-void: #020202;
            --bg-panel: #0a0a0b;
            --primary: #FFD700;
            --primary-glow: rgba(255, 215, 0, .35);
            --text-main: #fff;
            --text-dim: #71717a;
            --border: rgba(255, 255, 255, .08);
            --font-display: 'Orbitron', sans-serif;
            --font-body: 'Rajdhani', sans-serif;
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-void);
            color: var(--text-main);
            font-family: var(--font-body);
            line-height: 1.7;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3 {
            font-family: var(--font-display);
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s var(--ease-out);
        }

        /* Smooth Navbar Hover Effects */
        .mobile-nav-link {
            transition: all 0.4s var(--ease-out) !important;
            display: inline-block;
            transform-origin: left center;
        }

        .mobile-nav-link:hover {
            transform: scale(1.1) translateX(15px) !important;
            color: var(--primary) !important;
            text-shadow: 0 0 20px var(--primary-glow);
            letter-spacing: 3px;
        }

        /* Improved Button Glow (No Glitch) */
        .btn,
        .btn-outline {
            transition: all 0.4s var(--ease-out) !important;
        }

        .btn:hover {
            box-shadow: 0 0 25px var(--primary-glow), 0 0 50px rgba(255, 215, 0, 0.1) !important;
        }
    </style>

    <!-- FontAwesome (External) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <?php if (isset($structuredData)): ?>
        <script type="application/ld+json">
                <?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
            </script>
    <?php endif; ?>
</head>

<body>