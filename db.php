<?php
// Connexion PDO à la base MySQL
// Adapter le nom de base si nécessaire (gestion_stock par défaut)

const DB_HOST = 'localhost';
const DB_NAME = 'gestion_stock';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Retourne une instance PDO configurée.
 *
 * @return PDO
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Éviter d'afficher les détails de l'erreur en production
            die('Erreur de connexion à la base de données.');
        }
    }

    return $pdo;
}

