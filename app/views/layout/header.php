<?php
$isAuthPage = !empty($hideShell);
$user = Session::user();
$icons = [
    'home' => '<svg class="i" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z"/></svg>',
    'box' => '<svg class="i" viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3.3 7.3 12 12l8.7-4.7M12 22V12"/></svg>',
    'users' => '<svg class="i" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'user' => '<svg class="i" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'chart' => '<svg class="i" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>',
    'sliders' => '<svg class="i" viewBox="0 0 24 24"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" data-device="pc" data-lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'MEMOLOG') ?> - MEMOLOG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        (function(){
            var t = localStorage.getItem('memolog_theme') || 'dark';
            var d = localStorage.getItem('memolog_device') || 'pc';
            var l = localStorage.getItem('memolog_lang') || 'en';
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-device', d);
            document.documentElement.setAttribute('data-lang', l);
        })();
    </script>
</head>
<body>
<!-- Minimalist Action Loading Screen (Shown only on Sign In, Sign Up & Confirmations) -->
<div id="pageLoader" class="page-loader is-hidden" aria-live="polite" aria-label="Processing">
    <div class="loader-box">
        <div class="loader-stage">
            <span class="loader-ripple" aria-hidden="true"></span>
            <svg class="loader-ring" viewBox="0 0 64 64" aria-hidden="true">
                <circle class="loader-track" cx="32" cy="32" r="26"></circle>
                <circle class="loader-arc" cx="32" cy="32" r="26"></circle>
            </svg>
            <svg class="loader-check" viewBox="0 0 64 64" aria-hidden="true">
                <circle class="loader-check-ring" cx="32" cy="32" r="26"></circle>
                <path class="loader-check-path" d="M21 33 L29 41 L44 24"></path>
            </svg>
        </div>
        <div class="loader-status" id="loaderStatus">Processing...</div>
    </div>
</div>

<div class="viewport-stage" id="viewportStage">
<?php if (!$isAuthPage): ?>
<div class="app" id="appShell">
    <aside>
        <div class="brand">
            <div class="logo">M</div>
            <b>MEMOLOG</b>
        </div>
        <nav>
            <?php foreach ($menuItems as $item): ?>
                <a href="index.php?page=<?= htmlspecialchars($item['page']) ?>"
                   class="<?= ($currentPage === $item['page']) ? 'on' : '' ?>">
                    <?= $icons[$item['icon']] ?? '' ?>
                    <span class="lbl" data-i18n-text="<?= htmlspecialchars($item['label']) ?>"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="foot">
            <button type="button" id="toggleSidebar" title="Collapse sidebar">
                <svg class="i" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
                <span class="lbl" data-i18n-text="Collapse">Collapse</span>
            </button>
        </div>
    </aside>
    <main class="main">
        <header>
            <div>
                <h1 data-i18n-text="<?= htmlspecialchars($pageTitle ?? 'MEMOLOG') ?>"><?= htmlspecialchars($pageTitle ?? 'MEMOLOG') ?></h1>
                <?php if (!empty($pageSubtitle)): ?>
                    <p data-i18n-text="<?= htmlspecialchars($pageSubtitle) ?>"><?= htmlspecialchars($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="sp"></div>
            <div class="toolbar-cluster">
                <div class="seg" aria-label="Language switcher" title="What's your language?">
                    <button type="button" data-set-lang="en">EN</button>
                    <button type="button" data-set-lang="fil">FIL</button>
                </div>
                <div class="seg" aria-label="Device layout" title="What are you using?">
                    <button type="button" data-set-device="mobile" title="Mobile"><svg class="i-xs" viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M12 18h.01"/></svg><span>Mobile</span></button>
                    <button type="button" data-set-device="tablet" title="Tablet"><svg class="i-xs" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M12 17h.01"/></svg><span>Tablet</span></button>
                    <button type="button" data-set-device="pc" title="PC"><svg class="i-xs" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg><span>PC</span></button>
                </div>
                <div class="seg" aria-label="Theme switcher">
                    <button type="button" data-set-theme="dark">Dark</button>
                    <button type="button" data-set-theme="light">Light</button>
                    <button type="button" data-set-theme="sand">Sand</button>
                </div>
                <?php if ($user): ?>
                    <div class="who">
                        <span class="badge"><?= htmlspecialchars($user['role_name']) ?></span>
                        <a href="index.php?page=auth&action=logout" data-i18n-text="Sign Out">Sign Out</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <?php if (!empty($flash)): ?>
            <div class="alert-box <?= htmlspecialchars($flash['type']) ?>" role="alert">
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>
<?php endif; ?>
