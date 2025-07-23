/**
 * Assistant IA Gemini pour AromaVibe by Jas
 * Interface conversationnelle pour aider les clients
 */

class GeminiAssistant {
  constructor() {
    this.isOpen = false;
    this.messages = [];
    this.parfumsData = null;
    this.isTyping = false;
    
    this.init();
  }
  
  async init() {
    await this.loadData();
    this.createInterface();
    this.bindEvents();
    this.addWelcomeMessage();
  }
  
  async loadData() {
    try {
      const response = await fetch('assets/js/gemini-data.json');
      this.parfumsData = await response.json();
    } catch (error) {
      console.warn('Impossible de charger les données Gemini:', error);
      this.parfumsData = { parfums: [], marques: [] };
    }
  }
  
  createInterface() {
    const assistantHtml = `
      <div class="gemini-assistant">
        <button class="gemini-toggle" id="geminiToggle" title="Assistant IA">
          <i class="bi bi-robot"></i>
        </button>
        
        <div class="gemini-chat" id="geminiChat">
          <div class="gemini-header">
            <div class="d-flex align-items-center">
              <i class="bi bi-robot me-2"></i>
              <div>
                <h6 class="mb-0">Assistant AromaVibe</h6>
                <small class="opacity-75">Powered by IA</small>
              </div>
            </div>
            <button class="btn btn-sm btn-outline-light" id="geminiClose">
              <i class="bi bi-x"></i>
            </button>
          </div>
          
          <div class="gemini-messages" id="geminiMessages">
            <!-- Messages seront ajoutés ici -->
          </div>
          
          <div class="gemini-input">
            <div class="input-group">
              <input type="text" class="form-control" id="geminiInput" 
                     placeholder="Posez votre question..." maxlength="500">
              <button class="btn btn-primary" id="geminiSend">
                <i class="bi bi-send"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.getElementById('gemini-assistant').innerHTML = assistantHtml;
  }
  
  bindEvents() {
    const toggle = document.getElementById('geminiToggle');
    const close = document.getElementById('geminiClose');
    const send = document.getElementById('geminiSend');
    const input = document.getElementById('geminiInput');
    const chat = document.getElementById('geminiChat');
    
    toggle.addEventListener('click', () => this.toggleChat());
    close.addEventListener('click', () => this.closeChat());
    send.addEventListener('click', () => this.sendMessage());
    
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        this.sendMessage();
      }
    });
    
    // Fermer en cliquant à l'extérieur
    document.addEventListener('click', (e) => {
      if (!chat.contains(e.target) && !toggle.contains(e.target) && this.isOpen) {
        this.closeChat();
      }
    });
  }
  
  toggleChat() {
    this.isOpen = !this.isOpen;
    const chat = document.getElementById('geminiChat');
    chat.classList.toggle('active', this.isOpen);
    
    if (this.isOpen) {
      document.getElementById('geminiInput').focus();
    }
  }
  
  closeChat() {
    this.isOpen = false;
    document.getElementById('geminiChat').classList.remove('active');
  }
  
  addWelcomeMessage() {
    const welcomeMessage = `Bonjour ! 👋 Je suis votre assistant personnel AromaVibe. Je peux vous aider à :

• Trouver le parfum parfait selon vos goûts
• Comparer les prix et marques
• Vous donner des conseils sur les fragrances
• Répondre à vos questions sur nos produits

Comment puis-je vous aider aujourd'hui ?`;
    
    this.addMessage(welcomeMessage, 'assistant');
  }
  
  async sendMessage() {
    const input = document.getElementById('geminiInput');
    const message = input.value.trim();
    
    if (!message || this.isTyping) return;
    
    // Ajouter le message utilisateur
    this.addMessage(message, 'user');
    input.value = '';
    
    // Afficher l'indicateur de frappe
    this.showTyping();
    
    // Générer la réponse
    const response = await this.generateResponse(message);
    
    // Masquer l'indicateur et afficher la réponse
    this.hideTyping();
    this.addMessage(response, 'assistant');
  }
  
  addMessage(content, sender) {
    const messagesContainer = document.getElementById('geminiMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `gemini-message ${sender}`;
    messageDiv.innerHTML = this.formatMessage(content);
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    this.messages.push({ content, sender, timestamp: new Date() });
  }
  
  formatMessage(content) {
    // Convertir les liens en HTML
    content = content.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
    
    // Convertir les retours à la ligne
    content = content.replace(/\n/g, '<br>');
    
    // Convertir les listes à puces
    content = content.replace(/^• (.+)$/gm, '<li>$1</li>');
    content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    
    return content;
  }
  
  showTyping() {
    this.isTyping = true;
    const typingDiv = document.createElement('div');
    typingDiv.className = 'gemini-message assistant typing';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
      <div class="typing-dots">
        <span></span><span></span><span></span>
      </div>
    `;
    
    document.getElementById('geminiMessages').appendChild(typingDiv);
    document.getElementById('geminiMessages').scrollTop = document.getElementById('geminiMessages').scrollHeight;
  }
  
  hideTyping() {
    this.isTyping = false;
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
      typingIndicator.remove();
    }
  }
  
  async generateResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
    // Simulation d'un délai de réponse
    await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 2000));
    
    // Analyse du message et génération de réponse contextuelle
    if (this.containsKeywords(message, ['bonjour', 'salut', 'hello', 'bonsoir'])) {
      return this.getGreetingResponse();
    }
    
    if (this.containsKeywords(message, ['prix', 'coût', 'tarif', 'combien'])) {
      return this.getPriceResponse(message);
    }
    
    if (this.containsKeywords(message, ['parfum', 'fragrance', 'senteur', 'odeur'])) {
      return this.getParfumResponse(message);
    }
    
    if (this.containsKeywords(message, ['marque', 'brand'])) {
      return this.getBrandResponse(message);
    }
    
    if (this.containsKeywords(message, ['recommandation', 'conseil', 'suggère', 'propose'])) {
      return this.getRecommendationResponse(message);
    }
    
    if (this.containsKeywords(message, ['stock', 'disponible', 'disponibilité'])) {
      return this.getStockResponse(message);
    }
    
    if (this.containsKeywords(message, ['livraison', 'expédition', 'délai'])) {
      return this.getShippingResponse();
    }
    
    if (this.containsKeywords(message, ['contact', 'téléphone', 'email', 'whatsapp'])) {
      return this.getContactResponse();
    }
    
    // Réponse par défaut
    return this.getDefaultResponse();
  }
  
  containsKeywords(message, keywords) {
    return keywords.some(keyword => message.includes(keyword));
  }
  
  getGreetingResponse() {
    const greetings = [
      "Bonjour ! Ravi de vous aider dans votre recherche de parfum. Que puis-je faire pour vous ?",
      "Salut ! Je suis là pour vous guider dans notre collection de parfums. Comment puis-je vous assister ?",
      "Bonsoir ! Prêt à découvrir votre prochaine fragrance favorite ? Dites-moi ce que vous cherchez !"
    ];
    return greetings[Math.floor(Math.random() * greetings.length)];
  }
  
  getPriceResponse(message) {
    if (!this.parfumsData.parfums.length) {
      return "Nos parfums sont proposés à des prix compétitifs. Consultez notre catalogue pour voir les tarifs détaillés !";
    }
    
    const prices = this.parfumsData.parfums.map(p => parseFloat(p.prix));
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);
    const avgPrice = (prices.reduce((a, b) => a + b, 0) / prices.length).toFixed(2);
    
    return `Nos parfums sont proposés entre ${minPrice}€ et ${maxPrice}€, avec un prix moyen de ${avgPrice}€. 

Voici quelques exemples :
${this.parfumsData.parfums.slice(0, 3).map(p => `• ${p.nom} (${p.marque}) : ${p.prix}€`).join('\n')}

Souhaitez-vous voir une gamme de prix particulière ?`;
  }
  
  getParfumResponse(message) {
    if (!this.parfumsData.parfums.length) {
      return "Nous avons une belle collection de parfums de marque. Consultez notre catalogue pour découvrir toutes nos fragrances !";
    }
    
    // Recherche de parfums mentionnés
    const mentionedParfums = this.parfumsData.parfums.filter(p => 
      message.includes(p.nom.toLowerCase()) || 
      message.includes(p.marque.toLowerCase())
    );
    
    if (mentionedParfums.length > 0) {
      const parfum = mentionedParfums[0];
      return `${parfum.nom} de ${parfum.marque} est un excellent choix ! 

${parfum.description}

Prix : ${parfum.prix}€
Stock : ${parfum.stock > 0 ? `${parfum.stock} disponible(s)` : 'Rupture de stock'}

Voulez-vous plus d'informations sur ce parfum ou voir des alternatives similaires ?`;
    }
    
    return `Nous avons ${this.parfumsData.parfums.length} parfums dans notre collection ! 

Nos marques populaires : ${this.parfumsData.marques.slice(0, 5).join(', ')}

Quel type de fragrance vous intéresse ? (florale, boisée, fraîche, orientale...)`;
  }
  
  getBrandResponse(message) {
    if (!this.parfumsData.marques.length) {
      return "Nous travaillons avec les plus grandes marques de parfumerie. Consultez notre catalogue pour voir toute notre sélection !";
    }
    
    return `Nous proposons ${this.parfumsData.marques.length} marques prestigieuses :

${this.parfumsData.marques.map(marque => {
      const count = this.parfumsData.parfums.filter(p => p.marque === marque).length;
      return `• ${marque} (${count} parfum${count > 1 ? 's' : ''})`;
    }).join('\n')}

Quelle marque vous intéresse particulièrement ?`;
  }
  
  getRecommendationResponse(message) {
    if (!this.parfumsData.parfums.length) {
      return "Je serais ravi de vous recommander des parfums ! Pouvez-vous me dire quel type de fragrance vous préférez ?";
    }
    
    const recommendations = this.parfumsData.parfums
      .sort((a, b) => b.stock - a.stock)
      .slice(0, 3);
    
    return `Voici mes recommandations du moment :

${recommendations.map((p, i) => 
      `${i + 1}. **${p.nom}** de ${p.marque}
   ${p.description.substring(0, 80)}...
   Prix : ${p.prix}€`
    ).join('\n\n')}

Pour une recommandation plus personnalisée, dites-moi :
• Préférez-vous les parfums plutôt frais ou intenses ?
• Pour quelle occasion ? (quotidien, soirée, bureau...)
• Avez-vous des marques préférées ?`;
  }
  
  getStockResponse(message) {
    if (!this.parfumsData.parfums.length) {
      return "Consultez notre catalogue pour voir la disponibilité en temps réel de nos parfums !";
    }
    
    const inStock = this.parfumsData.parfums.filter(p => p.stock > 0);
    const outOfStock = this.parfumsData.parfums.filter(p => p.stock === 0);
    
    return `État des stocks :
• ${inStock.length} parfums disponibles
• ${outOfStock.length} parfums en rupture

${outOfStock.length > 0 ? 
  `Parfums temporairement indisponibles :\n${outOfStock.slice(0, 3).map(p => `• ${p.nom} (${p.marque})`).join('\n')}` : 
  'Tous nos parfums sont actuellement en stock ! 🎉'
}

Recherchez-vous un parfum en particulier ?`;
  }
  
  getShippingResponse() {
    return `🚚 **Informations de livraison :**

• **Délai standard** : 2-3 jours ouvrés
• **Livraison express** : 24h (disponible)
• **Frais de port** : Gratuits dès 50€ d'achat
• **Zones desservies** : France métropolitaine

📦 Tous nos parfums sont soigneusement emballés pour garantir leur intégrité.

Avez-vous d'autres questions sur la livraison ?`;
  }
  
  getContactResponse() {
    return `📞 **Nous contacter :**

• **WhatsApp** : +33 1 23 45 67 89
• **Email** : contact@aromavibe.com
• **Horaires** : Lun-Ven 9h-18h, Sam 10h-16h

💬 Vous pouvez aussi continuer notre conversation ici, je suis disponible 24h/24 !

Pour une commande rapide, utilisez notre bouton WhatsApp sur les fiches produits.`;
  }
  
  getDefaultResponse() {
    const responses = [
      "Je ne suis pas sûr de comprendre votre question. Pouvez-vous la reformuler ? Je peux vous aider avec nos parfums, prix, marques, ou conseils !",
      "Hmm, pouvez-vous être plus précis ? Je suis spécialisé dans les parfums et peux vous renseigner sur notre collection, les prix, ou vous donner des conseils personnalisés.",
      "Je n'ai pas bien saisi. Essayez de me demander des informations sur nos parfums, marques, prix, ou demandez-moi des recommandations !"
    ];
    return responses[Math.floor(Math.random() * responses.length)];
  }
  
  updateTheme(theme) {
    // Mettre à jour l'apparence de l'assistant selon le thème
    const chat = document.getElementById('geminiChat');
    if (chat) {
      chat.setAttribute('data-theme', theme);
    }
  }
}

// Styles CSS pour l'indicateur de frappe
const typingStyles = `
<style>
.typing-dots {
  display: flex;
  align-items: center;
  gap: 4px;
}

.typing-dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #6c757d;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
  0%, 80%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

.gemini-message.typing {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
}

[data-bs-theme="dark"] .gemini-message.typing {
  background-color: #404040;
  border-color: #555;
}

[data-bs-theme="dark"] .typing-dots span {
  background-color: #adb5bd;
}
</style>
`;

// Ajouter les styles au document
document.head.insertAdjacentHTML('beforeend', typingStyles);

// Initialiser l'assistant quand le DOM est prêt
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.GeminiAssistant = new GeminiAssistant();
  });
} else {
  window.GeminiAssistant = new GeminiAssistant();
}