<?php
// router.php
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false; // Laisse passer les fichiers normaux (images, CSS, JS...)
} else {
    // Si la page demandée n'existe pas, renvoie vers index.php (ou adapte selon ton projet)
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    if (file_exists(__DIR__ . '/index.php')) {
        require __DIR__ . '/index.php';
    }
}