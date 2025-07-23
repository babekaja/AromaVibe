-- Base de données AromaVibe by Jas
CREATE DATABASE IF NOT EXISTS aromavibe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aromavibe;

-- Table des parfums avec support multi-images
CREATE TABLE parfums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    marque VARCHAR(100) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    images JSON, -- Stockage des images en JSON
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion d'un admin par défaut
INSERT INTO admins (username, password, email) 
VALUES ('admin', SHA2('admin123', 256), 'admin@aromavibe.com');

-- Insertion de parfums d'exemple
INSERT INTO parfums (nom, marque, description, prix, stock, images) VALUES
('Sauvage', 'Dior', 'Un parfum masculin frais et épicé aux notes de bergamote et poivre', 89.99, 15, '["sauvage1.jpg", "sauvage2.jpg", "sauvage3.jpg"]'),
('Chanel N°5', 'Chanel', 'Le parfum féminin iconique aux notes florales aldéhydées', 129.99, 8, '["chanel5_1.jpg", "chanel5_2.jpg"]'),
('Black Opium', 'Yves Saint Laurent', 'Parfum féminin gourmand aux notes de café et vanille', 95.99, 12, '["blackopium1.jpg", "blackopium2.jpg", "blackopium3.jpg"]'),
('Acqua di Gio', 'Giorgio Armani', 'Fraîcheur marine masculine aux notes aquatiques', 79.99, 20, '["acqua1.jpg", "acqua2.jpg"]'),
('La Vie Est Belle', 'Lancôme', 'Parfum féminin gourmand aux notes de praline et iris', 99.99, 10, '["lavieestbelle1.jpg", "lavieestbelle2.jpg", "lavieestbelle3.jpg"]');