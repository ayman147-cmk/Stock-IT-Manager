<?php
require_once __DIR__ . '/db.php';

try {
    $pdo = getPDO();

    $hash = password_hash('123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('UPDATE utilisateurs SET password = :password WHERE username = :username');
    $stmt->execute([
        ':password' => $hash,
        ':username' => 'ayman',
    ]);

    echo 'Mot de passe mis à jour avec succès';
} catch (Throwable $e) {
    // Message générique pour éviter d'exposer des détails sensibles
    http_response_code(500);
    echo 'Une erreur est survenue lors de la mise à jour du mot de passe.';
}

