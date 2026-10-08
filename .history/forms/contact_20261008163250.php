<?php
/**
 * Piqueou Conseil Inc. — Formulaire de contact
 *
 * Prérequis :
 *   1. Télécharger php-email-form.php sur https://github.com/bootstrapmade/php-email-form
 *   2. Placer le fichier dans : assets/vendor/php-email-form/php-email-form.php
 *   3. Remplacer YOUR-EMAIL@example.com par votre adresse courriel
 *   4. Déployer sur un serveur PHP (≥ 7.4)
 */

$receiving_email_address = 'YOUR-EMAIL@example.com';

$php_email_form = '../assets/vendor/php-email-form/php-email-form.php';
if ( file_exists( $php_email_form ) ) {
    include( $php_email_form );
} else {
    die( 'Unable to load the PHP Email Form Library!' );
}

$contact = new PHP_Email_Form;
$contact->ajax   = true;
$contact->to     = $receiving_email_address;
$contact->from_name  = sanitize( $_POST['name'] ?? '' );
$contact->from_email = sanitize( $_POST['email'] ?? '' );
$contact->subject    = 'Piqueou – Demande de ' . sanitize( $_POST['name'] ?? 'inconnu' );

$contact->add_message( sanitize( $_POST['name']      ?? '' ), 'Nom complet',      3 );
$contact->add_message( sanitize( $_POST['email']     ?? '' ), 'Courriel',         3 );
$contact->add_message( sanitize( $_POST['org']       ?? '' ), 'Organisation',     3 );
$contact->add_message( sanitize( $_POST['framework'] ?? '' ), 'Cadre visé',       3 );
$contact->add_message( sanitize( $_POST['message']   ?? '' ), 'Contexte',        10 );

echo $contact->send();

function sanitize( string $value ): string {
    return htmlspecialchars( strip_tags( trim( $value ) ), ENT_QUOTES, 'UTF-8' );
}