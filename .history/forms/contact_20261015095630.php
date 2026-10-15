<?php
/**
 * Script de contact pour Piquéou Conseil Inc.
 * Intégration Native Resend API (Phase de Test)
 */

// Permettre les requêtes depuis votre serveur local (Sécurité)
header('Content-Type: application/json');

// 1. Récupération et nettoyage des données du formulaire Bootstrap
$name    = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$subject = isset($_POST['subject']) ? strip_tags(trim($_POST['subject'])) : 'Nouveau message de contact';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

// Validation de sécurité rapide
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo "Veuillez remplir tous les champs requis.";
    exit;
}

// 2. Configuration de vos identifiants Resend
// ⚠️ REMPLACEZ 're_xxxx_xxxx' PAR VOTRE VRAIE CLÉ API CRÉÉE SUR RESEND.COM
$apiKey = 're_votre_cle_api_secrete'; 

// Construction du corps du mail avec un design HTML propre
$htmlContent = "
    <div style='font-family: sans-serif; padding: 20px; color: #333;'>
        <h2 style='color: #00A3FF;'>Nouveau message de contact - Piquéou Conseil</h2>
        <p><strong>Nom du contact :</strong> {$name}</p>
        <p><strong>Email de l'expéditeur :</strong> {$email}</p>
        <p><strong>Sujet :</strong> {$subject}</p>
        <hr style='border: 1px solid #eee;' />
        <p><strong>Message :</strong></p>
        <p style='white-space: pre-line; background: #f9f9f9; padding: 15px; border-radius: 5px;'>{$message}</p>
    </div>
";

// Préparation des données pour l'API Resend (Mode Test / Sandbox)
$data = [
    // Obligatoire en phase de test
    'from'     => 'Piquéou Conseil <onboarding@resend.dev>', 
    
    // 💡 MODE TEST : Envoyez à 'delivered@resend.dev' pour simuler un envoi parfait.
    // 💡 ASTUCE : Vous pouvez aussi remplacer cette adresse par VOTRE propre e-mail de connexion Resend pour le recevoir en vrai.
    'to'       => ['delivered@resend.dev'], 
    
    'subject'  => "Formulaire : " . $subject,
    'html'     => $htmlContent,
    'reply_to' => $email // Permet de répondre directement au client en cliquant sur "Répondre"
];

// 3. Envoi de la requête à l'API Resend via PHP cURL
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

// 4. Traitement du retour pour le script JavaScript du template (validate.js)
// Les templates Bootstrap s'attendent généralement à recevoir le texte brut "OK" en cas de succès.
if ($httpCode === 200 || $httpCode === 201) {
    echo "OK";
} else {
    // Si Resend retourne une erreur (Ex: Clé API invalide)
    http_response_code($httpCode);
    echo "Une erreur est survenue sur le serveur de messagerie. (Code : " . $httpCode . ")";
}
