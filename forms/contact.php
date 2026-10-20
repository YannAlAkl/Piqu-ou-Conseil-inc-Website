<?php
/**
 * Piqueou Conseil Inc. — Formulaire de contact
 * Envoi via l'API Resend (https://resend.com) — API REST, pas de SMTP requis.
 */

header('Content-Type: text/plain; charset=UTF-8');

// ---------- 1) Récupération de la clé API ----------
$resend_api_key = getenv('RESEND_API_KEY');

if (!$resend_api_key) {
    $local_key_file = __DIR__ . '/../resend_key.local.php';
    if (file_exists($local_key_file)) {
        $resend_api_key = trim((string) require $local_key_file);
    }
}

if (!$resend_api_key || strpos($resend_api_key, 're_') !== 0) {
    http_response_code(500);
    error_log('[contact.php] Clé Resend absente ou invalide.');
    exit('Erreur de configuration serveur : clé API Resend manquante ou invalide. Créez resend_key.local.php à la racine du projet.');
}

// ---------- 2) Config envoi ----------
$from_address       = 'Piqueou – Site web <onboarding@resend.dev>';
$receiving_address  = 'alaklyann56@gmail.com'; // doit être l'adresse de VOTRE compte Resend (mode test)

// ---------- 3) Vérifications de base ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Méthode non permise.');
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

$name      = sanitize($_POST['name'] ?? '');
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$org       = sanitize($_POST['org'] ?? '');
$framework = sanitize($_POST['framework'] ?? '');
$message   = sanitize($_POST['message'] ?? '');

if ($name === '' || !$email || $message === '') {
    http_response_code(422);
    exit('Veuillez remplir tous les champs requis.');
}

// ---------- 4) Construction de l'e-mail ----------
$body_html = '<p><strong>Nom complet :</strong> ' . $name . '</p>'
           . '<p><strong>Courriel :</strong> ' . $email . '</p>'
           . '<p><strong>Organisation :</strong> ' . $org . '</p>'
           . '<p><strong>Socle de conformité visé :</strong> ' . $framework . '</p>'
           . '<p><strong>Contexte :</strong><br>' . nl2br($message) . '</p>';

$payload = [
    'from'     => $from_address,
    'to'       => [$receiving_address],
    'reply_to' => $email,
    'subject'  => 'Piqueou – Demande de ' . $name,
    'html'     => $body_html,
];

// ---------- 5) Envoi via l'API Resend ----------
$ch = curl_init('https://api.resend.com/emails');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $resend_api_key,
        'Content-Type: application/json',
        'User-Agent: Piqueou-ContactForm/1.0',
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response   = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ---------- 6) Gestion du résultat ----------
if ($curl_error) {
    http_response_code(500);
    error_log('[contact.php] Erreur cURL : ' . $curl_error);
    exit('Erreur réseau lors de l\'envoi. Réessayez dans quelques instants.');
}

if ($http_code >= 200 && $http_code < 300) {
    echo 'OK';
    exit;
}

$decoded     = json_decode((string) $response, true);
$error_label = $decoded['message'] ?? ('Code HTTP ' . $http_code);
error_log('[contact.php] Échec Resend (' . $http_code . ') : ' . $error_label);

http_response_code(500);
if ($http_code === 403) {
    exit('Envoi refusé par Resend : en mode test, l\'adresse de destination doit être celle de votre compte Resend.');
} elseif ($http_code === 401) {
    exit('Clé API Resend invalide ou révoquée. Vérifiez resend_key.local.php.');
} else {
    exit('Erreur lors de l\'envoi (' . $error_label . ').');
}