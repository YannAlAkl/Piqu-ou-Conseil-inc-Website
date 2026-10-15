<?php
// forms/contact.php

// 1. Récupération des données du formulaire Bootstrap
$name    = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$subject = isset($_POST['subject']) ? strip_tags(trim($_POST['subject'])) : 'Nouveau message de contact';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

// Validation rapide
if (empty($name) || empty($email) || empty($message)) {
    echo "Veuillez remplir tous les champs requis.";
    exit;
}

// 2. Configuration Resend
$apiKey = 're_votre_cle_api_secrete'; // Mettez votre vraie clé API Resend ici

// Corps du mail au format HTML
$htmlContent = "
    <h2>Nouveau message de contact</h2>
    <p><strong>Nom :</strong> {$name}</p>
    <p><strong>Email de l'expéditeur :</strong> {$email}</p>
    <p><strong>Message :</strong></p>
    <p style='white-space: pre-line;'>{$message}</p>
";

// Préparation des données pour l'API Resend
$data = [
    'from'    => 'Piquéou Conseil <onboarding@resend.dev>', // Laissez onboarding@resend.dev tant que votre domaine n'est pas validé sur Resend
    'to'      => ['votre_adresse_personnelle@gmail.com'],  // La boîte e-mail où vous voulez RECEVOIR les messages
    'subject' => $subject,
    'html'    => $htmlContent,
    'reply_to'=> $email // Permet de répondre directement au client en cliquant sur "Répondre"
];

// 3. Envoi via cURL (API REST)
$ch = curl_init('https://resend.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 4. Retour de la réponse à l'application (Pour le script validate.js)
if ($httpCode === 200 || $httpCode === 201) {
    echo "OK"; // Le script validate.js attend généralement "OK" pour afficher le message de succès
} else {
    echo "Une erreur est survenue lors de l'envoi de votre message. (Code d'erreur : " . $httpCode . ")";
}
