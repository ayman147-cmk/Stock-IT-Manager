-- Base de données pour l'application de gestion de stock informatique
-- SGBD : MySQL

CREATE DATABASE IF NOT EXISTS gestion_stock
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE gestion_stock;

-- Table des utilisateurs
-- Les mots de passe doivent être stockés sous forme de hash (password_hash en PHP)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Table des produits
CREATE TABLE IF NOT EXISTS produits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    num_serie VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    stock_initial INT NOT NULL DEFAULT 0,
    stock_actuel INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Table des mouvements de stock
CREATE TABLE IF NOT EXISTS mouvements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produit_id INT UNSIGNED NOT NULL,
    type ENUM('entrée', 'sortie') NOT NULL,
    quantite INT NOT NULL,
    date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mouvements_produit
      FOREIGN KEY (produit_id) REFERENCES produits(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Exemple de création d'un utilisateur admin (penser à remplacer le hash):
-- INSERT INTO utilisateurs (username, password) VALUES ('admin', '<hash_password>');

