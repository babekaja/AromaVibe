<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';
// Ensure this file exists and contains the corrected uploadImages function
require_once '../includes/functions.php';

$success = '';
$error = '';

// The uploadImages function is now expected to be defined in includes/functions.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $marque = trim($_POST['marque']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);

    if (!empty($nom) && !empty($marque) && $prix > 0) {
        // Gestion de l'upload des images
        $uploadedImages = [];

        // Check if files were actually uploaded
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            // Call the uploadImages function from includes/functions.php
            $uploadedImages = uploadImages($_FILES['images']);
        }

        if (empty($uploadedImages)) {
            $error = "Veuillez ajouter au moins une image.";
        } else {
            $imagesJson = json_encode($uploadedImages);

            try {
                $stmt = $pdo->prepare("INSERT INTO parfums (nom, marque, description, prix, stock, images) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $marque, $description, $prix, $stock, $imagesJson]);

                $success = "Parfum ajouté avec succès !";

                // Réinitialiser le formulaire en vidant $_POST
                $_POST = [];
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
                // Optionally delete uploaded images if DB insertion fails
                foreach ($uploadedImages as $imageName) {
                    $filePath = __DIR__ . '/../assets/images/' . $imageName; // Path relative to ajouter.php
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

$marques = getAllMarques($pdo);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un parfum - AromaVibe by Jas</title>
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
                            <a class="nav-link active" href="ajouter.php">
                                <i class="bi bi-plus-circle me-2"></i>Ajouter un parfum
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenu principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ajouter un nouveau parfum</h1>
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
                                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="marque" class="form-label">Marque *</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="marque" name="marque"
                                                           value="<?= htmlspecialchars($_POST['marque'] ?? '') ?>"
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
                                        <textarea class="form-control" id="description" name="description" rows="4"
                                                          placeholder="Décrivez les notes olfactives, l'occasion d'usage..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="prix" class="form-label">Prix (€) *</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="prix" name="prix"
                                                          step="0.01" min="0" value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" required>
                                                <span class="input-group-text">€</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="stock" class="form-label">Stock</label>
                                            <input type="number" class="form-control" id="stock" name="stock"
                                                          min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="images" class="form-label">Images du parfum *</label>
                                        <input type="file" class="form-control" id="images" name="images[]"
                                                          multiple accept="image/*" required>
                                        <div class="form-text">
                                            Sélectionnez plusieurs images (JPEG, PNG, WebP). Taille max : 5MB par image.
                                        </div>
                                        <div id="imagePreview" class="mt-3 row g-2"></div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>Ajouter le parfum
                                        </button>
                                        <a href="dashboard.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Annuler
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="mb-0">Conseils</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><i class="bi bi-lightbulb text-warning me-2"></i>Images</h6>
                                    <ul class="small text-muted">
                                        <li>Utilisez des images haute qualité</li>
                                        <li>Montrez le flacon sous différents angles</li>
                                        <li>Incluez l'emballage si possible</li>
                                    </ul>
                                </div>

                                <div class="mb-3">
                                    <h6><i class="bi bi-text-paragraph text-info me-2"></i>Description</h6>
                                    <ul class="small text-muted">
                                        <li>Mentionnez les notes de tête, cœur et fond</li>
                                        <li>Indiquez le type de fragrance</li>
                                        <li>Précisez l'occasion d'usage</li>
                                    </ul>
                                </div>

                                <div>
                                    <h6><i class="bi bi-tag text-success me-2"></i>Prix</h6>
                                    <ul class="small text-muted">
                                        <li>Vérifiez les prix concurrents</li>
                                        <li>Considérez la marge bénéficiaire</li>
                                        <li>Pensez aux promotions futures</li>
                                    </ul>
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
        // Prévisualisation des images
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
                            <div class="card">
                                <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted">${file.name}</small>
                                </div>
                            </div>
                        `;
                        preview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Validation du formulaire
        document.getElementById('parfumForm').addEventListener('submit', function(e) {
            const nom = document.getElementById('nom').value.trim();
            const marque = document.getElementById('marque').value.trim();
            const prix = parseFloat(document.getElementById('prix').value);
            const images = document.getElementById('images').files;

            if (!nom || !marque || prix <= 0 || images.length === 0) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires et ajouter au moins une image.');
                return false;
            }

            // Vérifier la taille des images
            for (let file of images) {
                if (file.size > 5 * 1024 * 1024) { // 5MB
                    e.preventDefault();
                    alert(`L'image ${file.name} est trop volumineuse (max 5MB).`);
                    return false;
                }
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
