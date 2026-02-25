<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$pdo = getPDO();

// Statistiques simples : total de produits, total entrées, total sorties
$totalProduits = (int) $pdo->query('SELECT COUNT(*) AS c FROM produits')->fetch()['c'];

$stmtEntrees = $pdo->query("SELECT COALESCE(SUM(quantite), 0) AS total FROM mouvements WHERE type = 'entrée'");
$totalEntrees = (int) $stmtEntrees->fetch()['total'];

$stmtSorties = $pdo->query("SELECT COALESCE(SUM(quantite), 0) AS total FROM mouvements WHERE type = 'sortie'");
$totalSorties = (int) $stmtSorties->fetch()['total'];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Dashboard - Gestion de stock</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Icônes Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
                    <a class="nav-link active" aria-current="page" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="produits.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="mouvements.php">Mouvements</a>
                </li>
            </ul>
            <span class="navbar-text me-3 d-flex align-items-center gap-1">
                <i class="bi bi-person-circle me-1"></i>
                Bienvenue,
                <strong><?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container my-4">
    <h1 class="h3 mb-4">Tableau de bord</h1>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">Total Produits</h5>
                        <p class="display-6 mb-0"><?= $totalProduits ?></p>
                    </div>
                    <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center"
                          style="width: 3rem; height: 3rem;">
                        <i class="bi bi-box-seam fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">Total Entrées</h5>
                        <p class="display-6 text-success mb-0"><?= $totalEntrees ?></p>
                    </div>
                    <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center"
                          style="width: 3rem; height: 3rem;">
                        <i class="bi bi-box-arrow-in-down fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">Total Sorties</h5>
                        <p class="display-6 text-danger mb-0"><?= $totalSorties ?></p>
                    </div>
                    <span class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center"
                          style="width: 3rem; height: 3rem;">
                        <i class="bi bi-box-arrow-up fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Aperçu rapide</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-light">
                                <div class="text-muted small">Nombre de produits enregistrés</div>
                                <div class="fs-4 fw-semibold"><?= $totalProduits ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border border-success-subtle bg-success-subtle">
                                <div class="text-muted small">Flux total d'entrées</div>
                                <div class="fs-4 fw-semibold text-success"><?= $totalEntrees ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border border-danger-subtle bg-danger-subtle">
                                <div class="text-muted small">Flux total de sorties</div>
                                <div class="fs-4 fw-semibold text-danger"><?= $totalSorties ?></div>
                            </div>
                        </div>
                    </div>
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