<?php
/**
 * Piqueou Conseil Inc. — Formulaire de contact
 *
 * Envoi via un serveur SMTP local sans authentification (Mailpit : 127.0.0.1:1025).
 * En production, remplacer $smtp_* par les paramètres du fournisseur courriel.
 */

header( 'Content-Type: text/plain; charset=UTF-8' );

$receiving_email_address = 'info@piqueou.ca';

$smtp_host = '127.0.0.1';
$smtp_port = 1025;

$php_email_form = __DIR__ . '/../assets/vendor/php-email-form/php-email-form.php';
if ( ! file_exists( $php_email_form ) ) {
    die( 'Unable to load the PHP Email Form Library!' );
}
require_once $php_email_form;

function sanitize( string $value ): string {
    return htmlspecialchars( strip_tags( trim( $value ) ), ENT_QUOTES, 'UTF-8' );
}

if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
    http_response_code( 405 );
    exit( 'Méthode non permise.' );
}

$name      = sanitize( $_POST['name'] ?? '' );
$email     = filter_var( trim( $_POST['email'] ?? '' ), FILTER_VALIDATE_EMAIL );
$org       = sanitize( $_POST['org'] ?? '' );
$framework = sanitize( $_POST['framework'] ?? '' );
$message   = sanitize( $_POST['message'] ?? '' );

if ( $name === '' || ! $email || $message === '' ) {
    exit( 'Veuillez remplir tous les champs requis.' );
}

$body = "Nom complet : $name\n"
      . "Courriel : $email\n"
      . "Organisation : $org\n"
      . "Cadre visé : $framework\n\n"
      . "Contexte :\n$message\n";

$mail = new PHPMailer( true );

try {
    $mail->isSMTP();
    $mail->Host        = $smtp_host;
    $mail->Port        = $smtp_port;
    $mail->SMTPAuth    = false;
    $mail->SMTPSecure  = '';
    $mail->SMTPAutoTLS = false;
    $mail->CharSet     = 'UTF-8';

    $mail->setFrom( $receiving_email_address, 'Piqueou – Site web' );
    $mail->addAddress( $receiving_email_address );
    $mail->addReplyTo( $email, $name );
    $mail->Subject = 'Piqueou – Demande de ' . $name;
    $mail->Body    = $body;

    $mail->send();
    echo 'OK';
} catch ( Throwable $e ) {
    http_response_code( 500 );
    echo 'Erreur lors de l\'envoi : ' . $mail->ErrorInfo;
}