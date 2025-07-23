/**
 * Application Vue.js principale pour AromaVibe by Jas
 */

const { createApp } = Vue;

// Composant Toggle Mode Sombre
const DarkModeToggle = {
  template: `
    <button class="dark-mode-toggle" @click="toggleDarkMode">
      <i :class="isDark ? 'bi bi-sun' : 'bi bi-moon'"></i>
      <span class="ms-2 d-none d-md-inline">{{ isDark ? 'Clair' : 'Sombre' }}</span>
    </button>
  `,
  data() {
    return {
      isDark: false
    };
  },
  mounted() {
    // Vérifier la préférence stockée ou celle du système
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    this.isDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark);
    this.applyTheme();
    
    // Écouter les changements de préférence système
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      if (!localStorage.getItem('theme')) {
        this.isDark = e.matches;
        this.applyTheme();
      }
    });
  },
  methods: {
    toggleDarkMode() {
      this.isDark = !this.isDark;
      this.applyTheme();
      localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
    },
    applyTheme() {
      document.documentElement.setAttribute('data-bs-theme', this.isDark ? 'dark' : 'light');
    }
  }
};

// Composant Carrousel d'images
const Carrousel = {
  props: ['images', 'alt'],
  template: `
    <div class="image-carousel">
      <div v-if="images && images.length > 0" id="parfumCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators" v-if="images.length > 1">
          <button 
            v-for="(image, index) in images" 
            :key="index"
            type="button" 
            data-bs-target="#parfumCarousel" 
            :data-bs-slide-to="index"
            :class="{ active: index === 0 }"
          ></button>
        </div>
        
        <div class="carousel-inner">
          <div 
            v-for="(image, index) in images" 
            :key="index"
            class="carousel-item"
            :class="{ active: index === 0 }"
          >
            <img :src="'assets/images/' + image" :alt="alt" class="d-block w-100">
          </div>
        </div>
        
        <button v-if="images.length > 1" class="carousel-control-prev" type="button" data-bs-target="#parfumCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button v-if="images.length > 1" class="carousel-control-next" type="button" data-bs-target="#parfumCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      
      <div v-else class="placeholder-image d-flex align-items-center justify-content-center bg-light" style="height: 400px;">
        <i class="bi bi-image display-1 text-muted"></i>
      </div>
    </div>
  `
};

// Composant Boutons de partage
const ShareButtons = {
  props: ['parfum', 'detailed'],
  template: `
    <div class="share-buttons">
      <a :href="facebookUrl" target="_blank" class="share-btn facebook" title="Partager sur Facebook">
        <i class="bi bi-facebook"></i>
      </a>
      <a :href="twitterUrl" target="_blank" class="share-btn twitter" title="Partager sur Twitter">
        <i class="bi bi-twitter"></i>
      </a>
      <a :href="whatsappUrl" target="_blank" class="share-btn whatsapp" title="Partager sur WhatsApp">
        <i class="bi bi-whatsapp"></i>
      </a>
      <a :href="emailUrl" class="share-btn email" title="Partager par email">
        <i class="bi bi-envelope"></i>
      </a>
    </div>
  `,
  computed: {
    currentUrl() {
      return window.location.href;
    },
    shareText() {
      return `Découvrez ${this.parfum.nom} de ${this.parfum.marque} à ${this.parfum.prix}€ sur AromaVibe by Jas`;
    },
    facebookUrl() {
      return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.currentUrl)}`;
    },
    twitterUrl() {
      return `https://twitter.com/intent/tweet?text=${encodeURIComponent(this.shareText)}&url=${encodeURIComponent(this.currentUrl)}`;
    },
    whatsappUrl() {
      return `https://wa.me/?text=${encodeURIComponent(this.shareText + ' ' + this.currentUrl)}`;
    },
    emailUrl() {
      const subject = `${this.parfum.nom} - AromaVibe by Jas`;
      const body = `${this.shareText}\n\n${this.parfum.description}\n\nVoir le produit : ${this.currentUrl}`;
      return `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    }
  }
};

// Application principale
const app = createApp({
  data() {
    return {
      // Données des parfums (injectées depuis PHP)
      parfums: window.parfumsData || [],
      filteredParfums: [],
      
      // Filtres
      searchQuery: '',
      selectedMarque: '',
      prixMin: '',
      prixMax: '',
      
      // État de l'application
      loading: false,
      
      // Données du parfum (pour la page détail)
      parfumData: window.parfumData || null,
      images: window.images || []
    };
  },
  
  mounted() {
    this.filteredParfums = [...this.parfums];
    this.initializeAnimations();
  },
  
  methods: {
    // Filtrage des parfums
    filterParfums() {
      this.loading = true;
      
      // Simulation d'un délai pour l'effet de chargement
      setTimeout(() => {
        this.filteredParfums = this.parfums.filter(parfum => {
          const matchesSearch = !this.searchQuery || 
            parfum.nom.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            parfum.description.toLowerCase().includes(this.searchQuery.toLowerCase());
          
          const matchesMarque = !this.selectedMarque || parfum.marque === this.selectedMarque;
          
          const matchesPrix = (!this.prixMin || parfum.prix >= parseFloat(this.prixMin)) &&
                             (!this.prixMax || parfum.prix <= parseFloat(this.prixMax));
          
          return matchesSearch && matchesMarque && matchesPrix;
        });
        
        this.loading = false;
      }, 300);
    },
    
    // Réinitialiser les filtres
    resetFilters() {
      this.searchQuery = '';
      this.selectedMarque = '';
      this.prixMin = '';
      this.prixMax = '';
      this.filteredParfums = [...this.parfums];
    },
    
    // Obtenir la première image d'un parfum
    getFirstImage(imagesJson) {
      try {
        const images = JSON.parse(imagesJson);
        return images && images.length > 0 ? `assets/images/${images[0]}` : 'https://images.pexels.com/photos/1961795/pexels-photo-1961795.jpeg?auto=compress&cs=tinysrgb&w=400';
      } catch (e) {
        return 'https://images.pexels.com/photos/1961795/pexels-photo-1961795.jpeg?auto=compress&cs=tinysrgb&w=400';
      }
    },
    
    // Contact WhatsApp (pour la page détail)
    contactWhatsApp() {
      if (!this.parfumData) return;
      
      const message = `Bonjour ! Je suis intéressé(e) par le parfum ${this.parfumData.nom} de ${this.parfumData.marque} à ${this.parfumData.prix}€. Pouvez-vous me donner plus d'informations ?`;
      const whatsappUrl = `https://wa.me/33123456789?text=${encodeURIComponent(message)}`;
      window.open(whatsappUrl, '_blank');
    },
    
    // Initialiser les animations
    initializeAnimations() {
      // Observer pour les animations au scroll
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('fade-in-up');
          }
        });
      }, observerOptions);
      
      // Observer tous les éléments avec data-aos
      document.querySelectorAll('[data-aos]').forEach(el => {
        observer.observe(el);
      });
    }
  },
  
  // Watchers pour les filtres
  watch: {
    searchQuery: 'filterParfums',
    selectedMarque: 'filterParfums',
    prixMin: 'filterParfums',
    prixMax: 'filterParfums'
  }
});

// Enregistrement des composants
app.component('dark-mode-toggle', DarkModeToggle);
app.component('carrousel', Carrousel);
app.component('share-buttons', ShareButtons);

// Montage de l'application
app.mount('#app');

// Fonctions utilitaires globales
window.AromaVibe = {
  // Formater le prix
  formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(price);
  },
  
  // Générer une URL de partage WhatsApp
  generateWhatsAppUrl(parfum) {
    const message = `Découvrez ${parfum.nom} de ${parfum.marque} à ${parfum.prix}€ sur AromaVibe by Jas ! ${window.location.origin}/parfum.php?id=${parfum.id}`;
    return `https://wa.me/?text=${encodeURIComponent(message)}`;
  },
  
  // Notification toast
  showToast(message, type = 'success') {
    // Créer un toast Bootstrap
    const toastHtml = `
      <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">${message}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    `;
    
    // Ajouter le toast au DOM
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
      document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialiser et afficher le toast
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Supprimer le toast après fermeture
    toastElement.addEventListener('hidden.bs.toast', () => {
      toastElement.remove();
    });
  }
};