# 💻 GestInfo Stock - Système de Gestion de Stock Informatique

**GestInfo Stock** est une application web légère développée en **PHP** permettant de gérer efficacement l'inventaire des composants informatiques (Disques durs, RAM, PC, etc.). Elle offre un suivi précis des mouvements (Entrées/Sorties) et gère le stock initial et final de chaque produit.

---

## 🚀 Fonctionnalités Clés

### 1. 🔐 Système d'Authentification
- Accès sécurisé via une page de connexion.
- Gestion des utilisateurs stockés en base de données avec des mots de passe hachés.

### 2. 📊 Tableau de Bord (Dashboard)
- Vue d'ensemble des statistiques du stock.
- Accès rapide aux différentes sections de l'application via une interface intuitive.

### 3. 📦 Gestion des Produits (CRUD)
- Ajout, modification et suppression de produits informatiques.
- Suivi par **Nom de produit**, **Numéro de série**, et **Type**.

### 4. 🔄 Gestion des Mouvements (Entrées/Sorties)
- Enregistrement des flux de stock.
- **Calcul Automatique :** Le système calcule le stock final en temps réel selon la formule :
  `Stock Final = Stock Initial + Somme(Entrées) - Somme(Sorties)`

---

## 📸 Captures d'Écran de l'Interface

Voici un aperçu visuel de l'application :

| Page de Connexion | Tableau de Bord (Stats) |
|---|---|
| <img src="https://github.com/user-attachments/assets/b44f2df8-7806-438b-bd64-217754ff7379" width="350" alt="Login Page" /> | <img src="https://github.com/user-attachments/assets/0aa6fb5e-059f-400d-a1f0-6ae2042683ac" width="350" alt="Dashboard" /> |

| Gestion des Produits | Enregistrement des Mouvements |
|---|---|
| <img src="https://github.com/user-attachments/assets/0cffcf32-ce61-43e7-91ac-7951eb7d98ba" width="350" alt="Products List" /> | <img src="https://github.com/user-attachments/assets/e8e28bdb-8787-494c-8911-bdd27386ce39" width="350" alt="Movements" /> |

---

## 🛠️ Technologies Utilisées

- **Backend :** PHP (PDO).
- **Base de données :** MySQL (phpMyAdmin).
- **Frontend :** HTML5, CSS3, Bootstrap 5.
- **Développement :** Réalisé avec **Cursor AI**.

---

## ⚙️ Installation Rapide

1. Clonez le projet.
2. Importez `database.sql` dans votre base MySQL.
3. Configurez `db.php` avec vos identifiants locaux.
4. Lancez le serveur via le terminal Cursor :
   ```bash
   php -S localhost:8000
