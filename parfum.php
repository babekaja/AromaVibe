<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
$parfum = $id ? getParfumById($pdo, $id) : null;

if (!$parfum) {
    header("Location: index.php");
    exit;
}

// Décodage des images JSON
$images = json_decode($parfum['images'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($parfum['nom']) ?> - AromaVibe by Jas</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.js" rel="stylesheet">

    <!-- Meta tags pour partage social -->
    <meta name="description" content="<?= htmlspecialchars($parfum['description']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($parfum['nom']) ?> - <?= htmlspecialchars($parfum['marque']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($parfum['description']) ?>">
    <meta property="og:image" content="<?= !empty($images) ? 'assets/images/' . htmlspecialchars($images[0]) : '' ?>">
    <meta property="og:type" content="product">
    <meta property="product:price:amount" content="<?= $parfum['prix'] ?>">
    <meta property="product:price:currency" content="EUR">
</head>
<body>
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">
                    <i class="bi bi-flower1 me-2"></i>AromaVibe by Jas
                </a>
                <div class="d-flex align-items-center">
                    <dark-mode-toggle></dark-mode-toggle>
                    <a href="index.php" class="btn btn-outline-light btn-sm ms-3">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
        </nav>

        <!-- Détails du parfum -->
        <section class="parfum-details py-5">
            <div class="container">
                <div class="row g-5">
                    <!-- Galerie d'images -->
                    <div class="col-lg-6">
                        <!-- The Vue.js components will use the global `parfumData` and `images` variables -->
                        <carrousel :images="images" :alt="parfumData.nom"></carrousel>
                    </div>

                    <!-- Informations du parfum -->
                    <div class="col-lg-6">
                        <div class="parfum-info" data-aos="fade-left">
                            <div class="mb-3">
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($parfum['marque']) ?></span>
                            </div>

                            <h1 class="display-5 fw-bold mb-4"><?= htmlspecialchars($parfum['nom']) ?></h1>

                            <div class="price-section mb-4">
                                <span class="h2 text-primary fw-bold"><?= number_format($parfum['prix'], 2) ?>€</span>
                                <?php if ($parfum['stock'] > 0): ?>
                                    <span class="badge bg-success ms-3">
                                        <i class="bi bi-check-circle me-1"></i>En stock (<?= $parfum['stock'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger ms-3">
                                        <i class="bi bi-x-circle me-1"></i>Rupture de stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="description mb-4">
                                <h5>Description</h5>
                                <p class="lead text-muted"><?= nl2br(htmlspecialchars($parfum['description'])) ?></p>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="action-buttons mb-4">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <button class="btn btn-success btn-lg w-100" @click="contactWhatsApp">
                                            <i class="bi bi-whatsapp me-2"></i>Commander sur WhatsApp
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons de partage -->
                            <div class="share-section">
                                <h6 class="mb-3">Partager ce parfum</h6>
                                <share-buttons :parfum="parfumData" :detailed="true"></share-buttons>
                            </div>

                            <!-- Informations supplémentaires -->
                            <div class="additional-info mt-5">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="info-card text-center p-3 bg-light rounded">
                                            <i class="bi bi-truck display-6 text-primary"></i>
                                            <h6 class="mt-2">Livraison rapide</h6>
                                            <small class="text-muted">2-3 jours ouvrés</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-card text-center p-3 bg-light rounded">
                                            <i class="bi bi-shield-check display-6 text-primary"></i>
                                            <h6 class="mt-2">Authenticité garantie</h6>
                                            <small class="text-muted">100% authentique</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Assistant IA flottant -->
    <div id="gemini-assistant"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- IMPORTANT: Define parfumData and images BEFORE app.js -->
    <script>
        // Données du parfum pour Vue.js
        const parfumData = <?= json_encode($parfum) ?>;
        const images = <?= json_encode($images) ?>;

        // Basic Vue.js app setup to display perfume details
        const app = Vue.createApp({
            data() {
                return {
                    parfumData: parfumData, // The single perfume object
                    images: images, // Array of image filenames
                };
            },
            methods: {
                contactWhatsApp() {
                    const phoneNumber = '+243999319517'; // Replace with your WhatsApp number
                    // Get the current page URL
                    const currentUrl = window.location.href;
                    const message = `Bonjour, je suis intéressé(e) par le parfum "${this.parfumData.nom}" (${this.parfumData.marque}) au prix de ${this.parfumData.prix}€. Est-il disponible ? Lien du produit : ${currentUrl}`;
                    const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
                    window.open(whatsappUrl, '_blank');
                },
                // Helper to get the full image path
                getImagePath(imageFileName) {
                    if (imageFileName) {
                        return 'assets/images/' + imageFileName;
                    }
                    return 'https://placehold.co/400x300/e0e0e0/000000?text=No+Image'; // Fallback image
                }
            },
            // Define the carrousel component inline for demonstration
            components: {
                'carrousel': {
                    props: ['images', 'alt'],
                    template: `
                        <div id="parfumCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                            <div class="carousel-inner rounded-4 shadow-lg">
                                <div v-if="images.length === 0" class="carousel-item active">
                                    <img src="https://placehold.co/600x400/e0e0e0/000000?text=No+Image" class="d-block w-100" :alt="alt">
                                </div>
                                <div v-for="(image, index) in images" :key="index" :class="['carousel-item', { active: index === 0 }]">
                                    <img :src="getImagePath(image)" class="d-block w-100" :alt="alt">
                                </div>
                            </div>
                            <button v-if="images.length > 1" class="carousel-control-prev" type="button" data-bs-target="#parfumCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button v-if="images.length > 1" class="carousel-control-next" type="button" data-bs-target="#parfumCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    `,
                    methods: {
                        getImagePath(imageFileName) {
                            // This method is duplicated from the main app for self-containment of the component
                            // In a real app, you'd import it or pass it down.
                            if (imageFileName) {
                                return 'assets/images/' + imageFileName;
                            }
                            return 'https://placehold.co/600x400/e0e0e0/000000?text=No+Image'; // Fallback for carousel
                        }
                    }
                },
                // Placeholder for share-buttons component, assuming it's defined in app.js or similar
                'share-buttons': {
                    props: ['parfum', 'detailed'],
                    template: `
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm" @click.prevent="share('facebook')">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info btn-sm" @click.prevent="share('twitter')">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm" @click.prevent="share('whatsapp')">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    `,
                    methods: {
                        share(platform) {
                            const url = window.location.href;
                            const text = `Découvrez le parfum "${this.parfum.nom}" de ${this.parfum.marque} sur AromaVibe by Jas !`;
                            if (platform === 'facebook') {
                                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                            } else if (platform === 'twitter') {
                                window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`, '_blank');
                            } else if (platform === 'whatsapp') {
                                window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`, '_blank');
                            }
                        }
                    }
                }
            }
        });

        // Mount the app to the #app element
        app.mount('#app');
    </script>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/darkmode.js"></script>
    <script src="assets/js/gemini.js"></script>

    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>
