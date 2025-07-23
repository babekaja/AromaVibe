<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$parfums = getAllParfums($pdo);
$totalParfums = count($parfums);
$totalStock = array_sum(array_column($parfums, 'stock'));
$marques = getAllMarques($pdo);
$totalMarques = count($marques);

// Statistiques
$parfumsEnStock = count(array_filter($parfums, fn($p) => $p['stock'] > 0));
$parfumsRupture = $totalParfums - $parfumsEnStock;
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - AromaVibe by Jas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Admin AromaVibe
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['admin']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../index.php" target="_blank">
                            <i class="bi bi-globe me-2"></i>Voir le site
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-house me-2"></i>Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ajouter.php">
                                <i class="bi bi-plus-circle me-2"></i>Ajouter un parfum
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenu principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tableau de bord</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="ajouter.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Nouveau parfum
                        </a>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Parfums
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalParfums ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-flower1 display-4 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            En Stock
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $parfumsEnStock ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-check-circle display-4 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Rupture Stock
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $parfumsRupture ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-exclamation-triangle display-4 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Marques
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalMarques ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-tags display-4 text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des parfums -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Gestion des Parfums</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Marque</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parfums as $parfum): ?>
                                        <?php 
                                        $images = json_decode($parfum['images'], true) ?: [];
                                        $firstImage = !empty($images) ? $images[0] : 'placeholder.jpg';
                                        ?>
                                        <tr>
                                            <td>
                                                <img src="../assets/images/<?= htmlspecialchars($firstImage) ?>" 
                                                     alt="<?= htmlspecialchars($parfum['nom']) ?>" 
                                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            </td>
                                            <td><?= htmlspecialchars($parfum['nom']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($parfum['marque']) ?></span>
                                            </td>
                                            <td><strong><?= number_format($parfum['prix'], 2) ?>€</strong></td>
                                            <td>
                                                <?php if ($parfum['stock'] > 0): ?>
                                                    <span class="badge bg-success"><?= $parfum['stock'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rupture</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($parfum['date_creation'])) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="../parfum.php?id=<?= $parfum['id'] ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank" title="Voir">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="modifier.php?id=<?= $parfum['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Modifier">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="supprimer.php?id=<?= $parfum['id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce parfum ?')" 
                                                       title="Supprimer">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
        }

        .sidebar .nav-link.active {
            color: #007bff;
        }

        .border-left-primary {
            border-left: 0.25rem solid #007bff !important;
        }

        .border-left-success {
            border-left: 0.25rem solid #28a745 !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #ffc107 !important;
        }

        .border-left-info {
            border-left: 0.25rem solid #17a2b8 !important;
        }

        .text-xs {
            font-size: 0.7rem;
        }

        main {
            margin-left: 0;
        }

        @media (min-width: 768px) {
            main {
                margin-left: 240px;
            }
        }
    </style>
</body>
</html>