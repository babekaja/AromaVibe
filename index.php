<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Récupération des parfums et marques pour les filtres
$parfums = getAllParfums($pdo);
$marques = getAllMarques($pdo);

// Génération des données pour l'IA Gemini (generateGeminiData already returns JSON string)
$geminiData = generateGeminiData($pdo);
// Corrected: No double json_encode here.
file_put_contents(__DIR__ . '/assets/js/gemini-data.json', $geminiData);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AromaVibe by Jas - Parfums de Luxe</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Meta tags pour SEO et partage social -->
    <meta name="description" content="Découvrez notre collection exclusive de parfums de luxe chez AromaVibe by Jas">
    <meta property="og:title" content="AromaVibe by Jas - Parfums de Luxe">
    <meta property="og:description" content="Collection exclusive de parfums de marque">
    <meta property="og:type" content="website">
</head>
<body>
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">
                    <i class="bi bi-flower1 me-2"></i>AromaVibe by Jas
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#parfums">Parfums</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center">
                        <!-- Toggle mode sombre -->
                        <dark-mode-toggle></dark-mode-toggle>
                        <a href="admin/login.php" class="btn btn-outline-light btn-sm ms-3">
                            <i class="bi bi-person-lock"></i> Admin
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center min-vh-50">
                    <div class="col-lg-6">
                        <div class="hero-content" data-aos="fade-right">
                            <h1 class="display-4 fw-bold mb-4">
                                Découvrez l'Art de la <span class="text-primary">Parfumerie</span>
                            </h1>
                            <p class="lead mb-4">
                                Une collection exclusive de parfums de luxe soigneusement sélectionnés pour révéler votre personnalité unique.
                            </p>
                            <a href="#parfums" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-down-circle me-2"></i>Découvrir nos parfums
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image" data-aos="fade-left">
                            <img src="https://images.pexels.com/photos/1961795/pexels-photo-1961795.jpeg?auto=compress&cs=tinysrgb&w=800"
                                    alt="Collection de parfums" class="img-fluid rounded-4 shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filtres et recherche -->
        <section class="filters-section py-4 bg-light">
            <div class="container">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Rechercher un parfum..."
                                       v-model="searchQuery" @input="filterParfums">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" v-model="selectedMarque" @change="filterParfums">
                            <option value="">Toutes les marques</option>
                            <?php foreach ($marques as $marque): ?>
                                <option value="<?= htmlspecialchars($marque) ?>"><?= htmlspecialchars($marque) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" placeholder="Prix min"
                                       v-model="prixMin" @input="filterParfums">
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" placeholder="Prix max"
                                       v-model="prixMax" @input="filterParfums">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-secondary w-100" @click="resetFilters">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section des parfums -->
        <section id="parfums" class="parfums-section py-5">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="display-5 fw-bold">Notre Collection</h2>
                    <p class="lead text-muted">Des fragrances exceptionnelles pour tous les goûts</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 col-md-6" v-for="parfum in filteredParfums" :key="parfum.id" data-aos="fade-up">
                        <div class="card parfum-card h-100 shadow-sm">
                            <div class="card-img-container position-relative">
                                <img :src="getFirstImage(parfum.images)"
                                            :alt="parfum.nom"
                                            class="card-img-top parfum-image">
                                <div class="card-overlay">
                                    <a :href="'parfum.php?id=' + parfum.id" class="btn btn-primary">
                                        <i class="bi bi-eye me-1"></i>Voir détails
                                    </a>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-secondary">{{ parfum.marque }}</span>
                                </div>
                                <h5 class="card-title">{{ parfum.nom }}</h5>
                                <p class="card-text text-muted flex-grow-1">
                                    {{ parfum.description.substring(0, 100) }}...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary mb-0">{{ parfum.prix }}€</span>
                                    <share-buttons :parfum="parfum"></share-buttons>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message si aucun résultat -->
                <div v-if="filteredParfums.length === 0" class="text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="mt-3">Aucun parfum trouvé</h3>
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-dark text-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <h5><i class="bi bi-flower1 me-2"></i>AromaVibe by Jas</h5>
                        <p class="text-muted">Votre destination pour les parfums de luxe et les fragrances exclusives.</p>
                    </div>
                    <div class="col-lg-4">
                        <h6>Contact</h6>
                        <p class="text-muted mb-1"><i class="bi bi-envelope me-2"></i>contact@aromavibe.com</p>
                        <p class="text-muted mb-1"><i class="bi bi-phone me-2"></i>+33 1 23 45 67 89</p>
                        <p class="text-muted"><i class="bi bi-geo-alt me-2"></i>Paris, France</p>
                    </div>
                    <div class="col-lg-4">
                        <h6>Suivez-nous</h6>
                        <div class="social-links">
                            <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="text-center">
                    <p class="mb-0">&copy; 2024 AromaVibe by Jas. Tous droits réservés.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Assistant IA flottant -->
    <div id="gemini-assistant"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- IMPORTANT: Define parfumsData BEFORE app.js -->
    <script>
        // Initialisation des données pour Vue.js
        const parfumsData = <?= json_encode($parfums, JSON_UNESCAPED_UNICODE) ?>;

        // Basic Vue.js app setup to display perfumes
        const app = Vue.createApp({
            data() {
                return {
                    parfums: parfumsData, // All perfumes from PHP
                    searchQuery: '',
                    selectedMarque: '',
                    prixMin: 0,
                    prixMax: 1000, // Default max price, adjust as needed
                };
            },
            computed: {
                filteredParfums() {
                    let filtered = this.parfums;

                    // Filter by search query
                    if (this.searchQuery) {
                        const searchLower = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(parfum =>
                            parfum.nom.toLowerCase().includes(searchLower) ||
                            parfum.description.toLowerCase().includes(searchLower) ||
                            parfum.marque.toLowerCase().includes(searchLower)
                        );
                    }

                    // Filter by brand
                    if (this.selectedMarque) {
                        filtered = filtered.filter(parfum => parfum.marque === this.selectedMarque);
                    }

                    // Filter by price range
                    filtered = filtered.filter(parfum => {
                        const price = parseFloat(parfum.prix);
                        return price >= parseFloat(this.prixMin || 0) && price <= parseFloat(this.prixMax || 999999);
                    });

                    return filtered;
                }
            },
            methods: {
                getFirstImage(imagesJson) {
                    try {
                        const images = JSON.parse(imagesJson);
                        if (images && images.length > 0) {
                            return 'assets/images/' + images[0];
                        }
                    } catch (e) {
                        console.error('Error parsing images JSON:', e);
                    }
                    return 'https://placehold.co/400x300/e0e0e0/000000?text=No+Image'; // Fallback image
                },
                filterParfums() {
                    // Re-computation of filteredParfums is automatic due to reactivity
                },
                resetFilters() {
                    this.searchQuery = '';
                    this.selectedMarque = '';
                    this.prixMin = 0;
                    this.prixMax = 1000; // Reset to default max
                }
            },
            components: {
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

    <script src="assets/js/darkmode.js"></script>
    <script src="assets/js/gemini.js"></script>

    <script>
        // Initialisation AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>
