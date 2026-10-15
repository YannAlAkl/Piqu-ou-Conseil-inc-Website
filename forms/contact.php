<?php
/**
 * Piqueou Conseil Inc. — Formulaire de contact
 *
 * Envoi via l'API Resend (https://resend.com), pas de serveur SMTP requis.
 *
 * IMPORTANT — Clé API :
 * La clé Resend ne doit JAMAIS être écrite en dur ici ni être commise dans Git
 * (ce dépôt est public).
 *   1) Variable d'environnement RESEND_API_KEY (prioritaire), ex :
 *      $env:RESEND_API_KEY="re_xxx" ; php -S 127.0.0.1:8000 router.php
 *   2) À défaut, fichier local resend_key.local.php (voir resend_key.local.example.php) —
 *      ce fichier est listé dans .gitignore, il ne sera jamais envoyé sur GitHub.
 *   - Production : configurez RESEND_API_KEY dans les variables d'environnement
 *                  de votre hébergeur.
 */

header( 'Content-Type: text/plain; charset=UTF-8' );

$resend_api_key = getenv( 'RESEND_API_KEY' );

if ( ! $resend_api_key ) {
    $local_key_file = __DIR__ . '/../resend_key.local.php';
    if ( file_exists( $local_key_file ) ) {
        $resend_api_key = trim( (string) require $local_key_file );
    }
}

// Adresse d'envoi : doit appartenir à un domaine vérifié dans Resend.
// En attendant la vérification de piqueou.ca, Resend fournit une adresse
// de test (onboarding@resend.dev) qui fonctionne sans configuration DNS.
$from_address = 'Piqueou – Site web <onboarding@resend.dev>';

// TEMPORAIRE : le sandbox onboarding@resend.dev ne peut envoyer qu'à l'adresse
// du compte Resend. Remettre "info@piqueou.ca" une fois le domaine piqueou.ca
// vérifié dans Resend (Domains → Add Domain).
$receiving_address = 'alaklyann56@gmail.com';

function sanitize( string $value ): string {
    return htmlspecialchars( strip_tags( trim( $value ) ), ENT_QUOTES, 'UTF-8' );
}

if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
    http_response_code( 405 );
    exit( 'Méthode non permise.' );
}

if ( ! $resend_api_key ) {
    http_response_code( 500 );
    exit( 'Erreur de configuration serveur : RESEND_API_KEY manquante.' );
}

$name      = sanitize( $_POST['name'] ?? '' );
$email     = filter_var( trim( $_POST['email'] ?? '' ), FILTER_VALIDATE_EMAIL );
$org       = sanitize( $_POST['org'] ?? '' );
$framework = sanitize( $_POST['framework'] ?? '' );
$message   = sanitize( $_POST['message'] ?? '' );

if ( $name === '' || ! $email || $message === '' ) {
    exit( 'Veuillez remplir tous les champs requis.' );
}

$body_html = '<p><strong>Nom complet :</strong> ' . $name . '</p>'
           . '<p><strong>Courriel :</strong> ' . $email . '</p>'
           . '<p><strong>Organisation :</strong> ' . $org . '</p>'
           . '<p><strong>Socle de conformité visé :</strong> ' . $framework . '</p>'
           . '<p><strong>Contexte :</strong><br>' . nl2br( $message ) . '</p>';

$payload = [
    'from'     => $from_address,
    'to'       => [ $receiving_address ],
    'reply_to' => $email,
    'subject'  => 'Piqueou – Demande de ' . $name,
    'html'     => $body_html,
];

$ch = curl_init( 'https://api.resend.com/emails' );
curl_setopt_array( $ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $resend_api_key,
        'Content-Type: application/json',
        'User-Agent: Piqueou-ContactForm/1.0',
    ],
    CURLOPT_POSTFIELDS => json_encode( $payload ),
    CURLOPT_TIMEOUT    => 15,
] );

$response   = curl_exec( $ch );
$curl_error = curl_error( $ch );
$http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
curl_close( $ch );

if ( $curl_error ) {
    http_response_code( 500 );
    exit( 'Erreur lors de l\'envoi : ' . $curl_error );
}

if ( $http_code >= 200 && $http_code < 300 ) {
    echo 'OK';
} else {
    $decoded     = json_decode( $response, true );
    $error_label = $decoded['message'] ?? $response;
    http_response_code( 500 );
    echo 'Erreur lors de l\'envoi (Resend ' . $http_code . ') : ' . $error_label;
}