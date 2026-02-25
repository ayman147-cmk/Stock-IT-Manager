<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$pdo = getPDO();
$errors = [];
$success = null;

// Création / mise à jour d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = isset($_POST['id']) ? (int) $_POST['id'] : null;
    $nom          = trim($_POST['nom'] ?? '');
    $num_serie    = trim($_POST['num_serie'] ?? '');
    $type         = trim($_POST['type'] ?? '');
    $stockInitial = (int) ($_POST['stock_initial'] ?? 0);

    if ($nom === '' || $num_serie === '' || $type === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    }

    if (empty($errors)) {
        if ($id) {
            // Mise à jour : ne pas écraser le stock actuel avec le stock initial
            $stmt = $pdo->prepare('
                UPDATE produits
                SET nom = :nom, num_serie = :num_serie, type = :type, stock_initial = :stock_initial
                WHERE id = :id
            ');
            $stmt->execute([
                ':nom'           => $nom,
                ':num_serie'     => $num_serie,
                ':type'          => $type,
                ':stock_initial' => $stockInitial,
                ':id'            => $id,
            ]);
            $success = 'Produit mis à jour avec succès.';
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO produits (nom, num_serie, type, stock_initial, stock_actuel)
                VALUES (:nom, :num_serie, :type, :stock_initial, :stock_actuel)
            ');
            $stmt->execute([
                ':nom'           => $nom,
                ':num_serie'     => $num_serie,
                ':type'          => $type,
                ':stock_initial' => $stockInitial,
                // Au départ, le stock actuel est égal au stock initial
                ':stock_actuel'  => $stockInitial,
            ]);
            $success = 'Produit ajouté avec succès.';
        }
    }
}

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM produits WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $success = 'Produit supprimé avec succès.';
    }
}

// Récupération de l'éventuel produit à éditer
$editProduct = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM produits WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $editProduct = $stmt->fetch();
    }
}

// Liste de tous les produits
$stmt = $pdo->query('SELECT * FROM produits ORDER BY id DESC');
$produits = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Produits - Gestion de stock</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Gestion de Stock</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="produits.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="mouvements.php">Mouvements</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                Connecté en tant que <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container my-4">
    <div class="row">
        <div class="col-lg-4">
            <h1 class="h4 mb-3"><?= $editProduct ? 'Modifier un produit' : 'Ajouter un produit' ?></h1>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post">
                        <?php if ($editProduct): ?>
                            <input type="hidden" name="id" value="<?= (int) $editProduct['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required
                                   value="<?= htmlspecialchars($editProduct['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="num_serie" class="form-label">Numéro de série</label>
                            <input type="text" class="form-control" id="num_serie" name="num_serie" required
                                   value="<?= htmlspecialchars($editProduct['num_serie'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <input type="text" class="form-control" id="type" name="type" required
                                   value="<?= htmlspecialchars($editProduct['type'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="stock_initial" class="form-label">Stock initial</label>
                            <input type="number" class="form-control" id="stock_initial" name="stock_initial"
                                   min="0" required
                                   value="<?= isset($editProduct['stock_initial']) ? (int) $editProduct['stock_initial'] : 0 ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <?= $editProduct ? 'Mettre à jour' : 'Ajouter' ?>
                            </button>
                            <?php if ($editProduct): ?>
                                <a href="produits.php" class="btn btn-outline-secondary">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h2 class="h4 mb-3">Liste des produits</h2>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if (empty($produits)): ?>
                        <p class="text-muted mb-0">Aucun produit pour le moment.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Numéro de série</th>
                                    <th>Type</th>
                                    <th>Stock initial</th>
                                    <th>Stock actuel</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($produits as $p): ?>
                                    <tr>
                                        <td><?= (int) $p['id'] ?></td>
                                        <td><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($p['num_serie'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($p['type'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) $p['stock_initial'] ?></td>
                                        <td><?= (int) $p['stock_actuel'] ?></td>
                                        <td>
                                            <a href="produits.php?edit=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                Modifier
                                            </a>
                                            <a href="produits.php?delete=<?= (int) $p['id'] ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Supprimer ce produit ? Cette action est définitive.');">
                                                Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>

