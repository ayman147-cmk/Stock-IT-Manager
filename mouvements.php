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

// Récupération des produits pour la liste déroulante
$stmt = $pdo->query('SELECT id, nom, num_serie, stock_actuel FROM produits ORDER BY nom ASC');
$produits = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produitId = (int) ($_POST['produit_id'] ?? 0);
    $type      = $_POST['type'] ?? '';
    $quantite  = (int) ($_POST['quantite'] ?? 0);
    $date      = $_POST['date'] ?? null;

    if ($produitId <= 0 || $type === '' || $quantite <= 0) {
        $errors[] = 'Tous les champs sont obligatoires et la quantité doit être positive.';
    }

    if (!in_array($type, ['entrée', 'sortie'], true)) {
        $errors[] = 'Type de mouvement invalide.';
    }

    // Vérifier que le produit existe
    $stmtProd = $pdo->prepare('SELECT id, stock_actuel FROM produits WHERE id = :id');
    $stmtProd->execute([':id' => $produitId]);
    $produit = $stmtProd->fetch();

    if (!$produit) {
        $errors[] = 'Produit introuvable.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Calcul du nouveau stock actuel
            $stockActuel = (int) $produit['stock_actuel'];

            if ($type === 'entrée') {
                $nouveauStock = $stockActuel + $quantite;
            } else {
                // Sortie : vérifier qu'on ne passe pas en stock négatif
                if ($quantite > $stockActuel) {
                    throw new RuntimeException('Stock insuffisant pour effectuer cette sortie.');
                }
                $nouveauStock = $stockActuel - $quantite;
            }

            // Insertion du mouvement
            $sqlDate = $date && $date !== '' ? $date : null;

            if ($sqlDate) {
                $stmtMv = $pdo->prepare('
                    INSERT INTO mouvements (produit_id, type, quantite, date)
                    VALUES (:produit_id, :type, :quantite, :date)
                ');
                $stmtMv->execute([
                    ':produit_id' => $produitId,
                    ':type'       => $type,
                    ':quantite'   => $quantite,
                    ':date'       => $sqlDate,
                ]);
            } else {
                $stmtMv = $pdo->prepare('
                    INSERT INTO mouvements (produit_id, type, quantite)
                    VALUES (:produit_id, :type, :quantite)
                ');
                $stmtMv->execute([
                    ':produit_id' => $produitId,
                    ':type'       => $type,
                    ':quantite'   => $quantite,
                ]);
            }

            // Mise à jour du stock actuel dans la table produits
            $stmtUpdate = $pdo->prepare('
                UPDATE produits
                SET stock_actuel = :stock_actuel
                WHERE id = :id
            ');
            $stmtUpdate->execute([
                ':stock_actuel' => $nouveauStock,
                ':id'           => $produitId,
            ]);

            $pdo->commit();
            $success = 'Mouvement enregistré et stock mis à jour.';
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Une erreur est survenue lors de l\'enregistrement du mouvement.';
        }
    }
}

// Derniers mouvements pour l'affichage
$stmt = $pdo->query('
    SELECT m.id, m.type, m.quantite, m.date, p.nom, p.num_serie
    FROM mouvements m
    JOIN produits p ON p.id = m.produit_id
    ORDER BY m.date DESC, m.id DESC
    LIMIT 20
');
$mouvements = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mouvements - Gestion de stock</title>
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
                    <a class="nav-link" href="produits.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="mouvements.php">Mouvements</a>
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
            <h1 class="h4 mb-3">Enregistrer un mouvement</h1>

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
                        <div class="mb-3">
                            <label for="produit_id" class="form-label">Produit</label>
                            <select class="form-select" id="produit_id" name="produit_id" required>
                                <option value="">Sélectionner un produit...</option>
                                <?php foreach ($produits as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>">
                                        <?= htmlspecialchars($p['nom'] . ' - ' . $p['num_serie'], ENT_QUOTES, 'UTF-8') ?>
                                        (Stock: <?= (int) $p['stock_actuel'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de mouvement</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="entrée">Entrée</option>
                                <option value="sortie">Sortie</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantite" class="form-label">Quantité</label>
                            <input type="number" class="form-control" id="quantite" name="quantite"
                                   min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date (optionnelle)</label>
                            <input type="datetime-local" class="form-control" id="date" name="date">
                            <div class="form-text">Laisser vide pour utiliser la date et l'heure actuelles.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h2 class="h4 mb-3">Derniers mouvements</h2>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if (empty($mouvements)): ?>
                        <p class="text-muted mb-0">Aucun mouvement pour le moment.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produit</th>
                                    <th>Type</th>
                                    <th>Quantité</th>
                                    <th>Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($mouvements as $m): ?>
                                    <tr>
                                        <td><?= (int) $m['id'] ?></td>
                                        <td>
                                            <?= htmlspecialchars($m['nom'], ENT_QUOTES, 'UTF-8') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($m['num_serie'], ENT_QUOTES, 'UTF-8') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($m['type'] === 'entrée'): ?>
                                                <span class="badge bg-success">Entrée</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Sortie</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= (int) $m['quantite'] ?></td>
                                        <td><?= htmlspecialchars($m['date'], ENT_QUOTES, 'UTF-8') ?></td>
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

