/**
 * Gestionnaire du mode sombre pour AromaVibe by Jas
 * Gère la persistance et la synchronisation du thème
 */




class DarkModeManager {
    constructor() {
        this.init();
    }

    init() {
        // Appliquer le thème au chargement de la page
        this.applyStoredTheme();

        // Écouter les changements de préférence système
        this.watchSystemPreference();

        // Écouter les changements dans d'autres onglets
        this.watchStorageChanges();
    }

    applyStoredTheme() {
        const storedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        let theme;
        if (storedTheme) {
            theme = storedTheme;
        } else {
            theme = systemPrefersDark ? 'dark' : 'light';
        }

        this.setTheme(theme);
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);

        // Mettre à jour les meta tags pour les navigateurs mobiles
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', theme === 'dark' ? '#1a1a1a' : '#ffffff');
        } else {
            const meta = document.createElement('meta');
            meta.name = 'theme-color';
            meta.content = theme === 'dark' ? '#1a1a1a' : '#ffffff';
            document.head.appendChild(meta);
        }

        // Émettre un événement personnalisé
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        this.setTheme(newTheme);
        localStorage.setItem('theme', newTheme);

        return newTheme;
    }

    watchSystemPreference() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        mediaQuery.addEventListener('change', (e) => {
            // Ne changer que si l'utilisateur n'a pas de préférence stockée
            if (!localStorage.getItem('theme')) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    watchStorageChanges() {
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                this.setTheme(e.newValue || 'light');
            }
        });
    }

    getCurrentTheme() {
        return document.documentElement.getAttribute('data-bs-theme');
    }

    isDarkMode() {
        return this.getCurrentTheme() === 'dark';
    }
}

// Initialiser le gestionnaire de mode sombre
const darkModeManager = new DarkModeManager();

// Exposer globalement pour utilisation dans d'autres scripts
window.DarkModeManager = darkModeManager;

// Fonctions utilitaires pour les autres scripts
window.toggleDarkMode = () => darkModeManager.toggleTheme();
window.isDarkMode = () => darkModeManager.isDarkMode();

// Écouter les changements de thème pour mettre à jour les composants
window.addEventListener('themeChanged', (e) => {
    const theme = e.detail.theme;

    // Mettre à jour les graphiques ou autres éléments sensibles au thème
    if (window.updateChartsTheme) {
        window.updateChartsTheme(theme);
    }

    // Mettre à jour l'assistant IA si nécessaire
    if (window.GeminiAssistant) {
        window.GeminiAssistant.updateTheme(theme);
    }

    // Animation de transition douce
    document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    setTimeout(() => {
        document.body.style.transition = '';
    }, 300);
});

// Préchargement des images pour éviter le flash lors du changement de thème
const preloadThemeImages = () => {
    const images = [
        'assets/images/logo-light.png',
        'assets/images/logo-dark.png'
    ];

    images.forEach(src => {
        const img = new Image();
        img.src = src;
    });
};

// Précharger au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', preloadThemeImages);
} else {
    preloadThemeImages();
}

// Raccourci clavier pour basculer le mode sombre (Ctrl/Cmd + Shift + D)
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        const newTheme = darkModeManager.toggleTheme();

        // Afficher une notification
        if (window.AromaVibe && window.AromaVibe.showToast) {
            window.AromaVibe.showToast(
                `Mode ${newTheme === 'dark' ? 'sombre' : 'clair'} activé`,
                'info'
            );
        }
    }
});

// Détection automatique de la préférence utilisateur basée sur l'heure
const autoDetectPreference = () => {
    const hour = new Date().getHours();
    const isNightTime = hour < 7 || hour > 19;

    // Suggérer le mode sombre la nuit si aucune préférence n'est stockée
    if (!localStorage.getItem('theme') && isNightTime) {
        // Attendre un peu avant de suggérer pour ne pas être intrusif
        setTimeout(() => {
            if (confirm('Il fait nuit ! Voulez-vous activer le mode sombre pour un confort visuel optimal ?')) {
                darkModeManager.setTheme('dark');
                localStorage.setItem('theme', 'dark');
            }
        }, 3000);
    }
};

// Activer la détection automatique seulement pour les nouveaux visiteurs
if (!localStorage.getItem('theme') && !sessionStorage.getItem('themePromptShown')) {
    sessionStorage.setItem('themePromptShown', 'true');
    autoDetectPreference();
}