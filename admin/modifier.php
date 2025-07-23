<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

$parfum = getParfumById($pdo, $id);
if (!$parfum) {
    header("Location: dashboard.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $marque = trim($_POST['marque']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    
    if (!empty($nom) && !empty($marque) && $prix > 0) {
        $currentImages = json_decode($parfum['images'], true) ?: [];
        $finalImages = $currentImages;
        
        // Gestion des nouvelles images
        if (!empty($_FILES['images']['name'][0])) {
            $newImages = uploadImages($_FILES['images']);
            $finalImages = array_merge($finalImages, $newImages);
        }
        
        // Suppression d'images sélectionnées
        if (!empty($_POST['delete_images'])) {
            $imagesToDelete = $_POST['delete_images'];
            $finalImages = array_diff($finalImages, $imagesToDelete);
            
            // Supprimer physiquement les fichiers
            foreach ($imagesToDelete as $imageToDelete) {
                $filePath = "../assets/images/" . $imageToDelete;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        $imagesJson = json_encode(array_values($finalImages));
        
        try {
            $stmt = $pdo->prepare("UPDATE parfums SET nom=?, marque=?, description=?, prix=?, stock=?, images=? WHERE id=?");
            $stmt->execute([$nom, $marque, $description, $prix, $stock, $imagesJson, $id]);
            
            $success = "Parfum modifié avec succès !";
            
            // Recharger les données
            $parfum = getParfumById($pdo, $id);
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires";
    }
}

$marques = getAllMarques($pdo);
$currentImages = json_decode($parfum['images'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier <?= htmlspecialchars($parfum['nom']) ?> - AromaVibe by Jas</title>
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
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
                </a>
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
                            <a class="nav-link" href="dashboard.php">
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
                    <h1 class="h2">Modifier "<?= htmlspecialchars($parfum['nom']) ?>"</h1>
                    <div class="btn-toolbar">
                        <a href="../parfum.php?id=<?= $parfum['id'] ?>" class="btn btn-outline-info btn-sm" target="_blank">
                            <i class="bi bi-eye me-1"></i>Voir sur le site
                        </a>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">Informations du parfum</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data" id="parfumForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nom" class="form-label">Nom du parfum *</label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="<?= htmlspecialchars($parfum['nom']) ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="marque" class="form-label">Marque *</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="marque" name="marque" 
                                                       value="<?= htmlspecialchars($parfum['marque']) ?>" 
                                                       list="marquesList" required>
                                                <datalist id="marquesList">
                                                    <?php foreach ($marques as $marque): ?>
                                                        <option value="<?= htmlspecialchars($marque) ?>">
                                                    <?php endforeach; ?>
                                                </datalist>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($parfum['description']) ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="prix" class="form-label">Prix (€) *</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="prix" name="prix" 
                                                       step="0.01" min="0" value="<?= $parfum['prix'] ?>" required>
                                                <span class="input-group-text">€</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="stock" class="form-label">Stock</label>
                                            <input type="number" class="form-control" id="stock" name="stock" 
                                                   min="0" value="<?= $parfum['stock'] ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Images actuelles -->
                                    <?php if (!empty($currentImages)): ?>
                                        <div class="mb-4">
                                            <label class="form-label">Images actuelles</label>
                                            <div class="row g-2" id="currentImages">
                                                <?php foreach ($currentImages as $index => $image): ?>
                                                    <div class="col-md-3 col-sm-4 col-6">
                                                        <div class="card">
                                                            <img src="../assets/images/<?= htmlspecialchars($image) ?>" 
                                                                 class="card-img-top" style="height: 120px; object-fit: cover;">
                                                            <div class="card-body p-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" 
                                                                           name="delete_images[]" value="<?= htmlspecialchars($image) ?>" 
                                                                           id="delete_<?= $index ?>">
                                                                    <label class="form-check-label small text-danger" for="delete_<?= $index ?>">
                                                                        Supprimer
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Nouvelles images -->
                                    <div class="mb-4">
                                        <label for="images" class="form-label">Ajouter de nouvelles images</label>
                                        <input type="file" class="form-control" id="images" name="images[]" 
                                               multiple accept="image/*">
                                        <div class="form-text">
                                            Sélectionnez des images supplémentaires (JPEG, PNG, WebP). Taille max : 5MB par image.
                                        </div>
                                        <div id="imagePreview" class="mt-3 row g-2"></div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                                        </button>
                                        <a href="dashboard.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Annuler
                                        </a>
                                        <a href="supprimer.php?id=<?= $parfum['id'] ?>" 
                                           class="btn btn-danger ms-auto"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce parfum ?')">
                                            <i class="bi bi-trash me-1"></i>Supprimer le parfum
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Statistiques</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Créé le</small>
                                    <div><?= date('d/m/Y à H:i', strtotime($parfum['date_creation'])) ?></div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Dernière modification</small>
                                    <div><?= date('d/m/Y à H:i', strtotime($parfum['date_modification'])) ?></div>
                                </div>
                                <div>
                                    <small class="text-muted">Nombre d'images</small>
                                    <div><?= count($currentImages) ?> image(s)</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="mb-0">Actions rapides</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="../parfum.php?id=<?= $parfum['id'] ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                        <i class="bi bi-eye me-1"></i>Voir sur le site
                                    </a>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="copyProductUrl()">
                                        <i class="bi bi-link me-1"></i>Copier le lien
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Prévisualisation des nouvelles images
        document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            Array.from(e.target.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-3 col-sm-4 col-6';
                        col.innerHTML = `
                            <div class="card border-success">
                                <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-success">Nouvelle: ${file.name}</small>
                                </div>
                            </div>
                        `;
                        preview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // Copier l'URL du produit
        function copyProductUrl() {
            const url = `${window.location.origin}/../parfum.php?id=<?= $parfum['id'] ?>`;
            navigator.clipboard.writeText(url).then(() => {
                alert('Lien copié dans le presse-papiers !');
            });
        }
        
        // Validation du formulaire
        document.getElementById('parfumForm').addEventListener('submit', function(e) {
            const nom = document.getElementById('nom').value.trim();
            const marque = document.getElementById('marque').value.trim();
            const prix = parseFloat(document.getElementById('prix').value);
            
            if (!nom || !marque || prix <= 0) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }
        });
    </script>
    
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