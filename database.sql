-- Base de données pour le projet BNGRC
CREATE DATABASE IF NOT EXISTS bngrc_dons CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bngrc_dons;

-- Table des villes
CREATE TABLE ville (
    id_ville INT PRIMARY KEY AUTO_INCREMENT,
    nom_ville VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des types de besoins
CREATE TABLE type_besoin (
    id_type INT PRIMARY KEY AUTO_INCREMENT,
    nom_type VARCHAR(50) NOT NULL,
    categorie ENUM('nature', 'materiau', 'argent') NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    unite VARCHAR(20) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des besoins par ville
CREATE TABLE besoin (
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    id_ville INT NOT NULL,
    id_type INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville) ON DELETE CASCADE,
    FOREIGN KEY (id_type) REFERENCES type_besoin(id_type) ON DELETE CASCADE
);

-- Table des dons
CREATE TABLE don (
    id_don INT PRIMARY KEY AUTO_INCREMENT,
    id_type INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    quantite_restante DECIMAL(10,2) NOT NULL,
    donateur VARCHAR(100),
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('disponible', 'distribue', 'partiel') DEFAULT 'disponible',
    FOREIGN KEY (id_type) REFERENCES type_besoin(id_type) ON DELETE CASCADE
);

-- Table des distributions
CREATE TABLE distribution (
    id_distribution INT PRIMARY KEY AUTO_INCREMENT,
    id_don INT NOT NULL,
    id_ville INT NOT NULL,
    quantite_distribuee DECIMAL(10,2) NOT NULL,
    date_distribution DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_don) REFERENCES don(id_don) ON DELETE CASCADE,
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville) ON DELETE CASCADE
);

-- Table des configurations
CREATE TABLE IF NOT EXISTS configuration (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur TEXT NOT NULL,
    description VARCHAR(255),
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des achats (purchases)
CREATE TABLE IF NOT EXISTS achat (
    id_achat INT PRIMARY KEY AUTO_INCREMENT,
    id_ville INT NOT NULL,
    id_type INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    frais_achat DECIMAL(5,2) NOT NULL COMMENT 'Pourcentage des frais d''achat',
    montant_total DECIMAL(10,2) NOT NULL COMMENT 'Montant avec frais inclus',
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('simule', 'valide') DEFAULT 'simule',
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville) ON DELETE CASCADE,
    FOREIGN KEY (id_type) REFERENCES type_besoin(id_type) ON DELETE CASCADE
);

-- Insertion des villes
INSERT INTO ville (nom_ville, region) VALUES
('Antananarivo', 'Analamanga'),
('Toamasina', 'Atsinanana'),
('Antsirabe', 'Vakinankaratra'),
('Mahajanga', 'Boeny'),
('Toliara', 'Atsimo Andrefana');

-- Insertion des types de besoins - NATURE
INSERT INTO type_besoin (nom_type, categorie, prix_unitaire, unite) VALUES
('Riz', 'nature', 2000.00, 'kg'),
('Huile', 'nature', 8000.00, 'L'),
('Sucre', 'nature', 3000.00, 'kg'),
('Haricot', 'nature', 2500.00, 'kg'),
('Eau', 'nature', 500.00, 'L');

-- Insertion des types de besoins - MATÉRIAUX
INSERT INTO type_besoin (nom_type, categorie, prix_unitaire, unite) VALUES
('Tôle', 'materiau', 25000.00, 'unité'),
('Clou', 'materiau', 5000.00, 'kg'),
('Bois', 'materiau', 15000.00, 'unité'),
('Ciment', 'materiau', 35000.00, 'sac'),
('Bâche', 'materiau', 20000.00, 'unité');

-- Insertion des types de besoins - ARGENT
INSERT INTO type_besoin (nom_type, categorie, prix_unitaire, unite) VALUES
('Argent', 'argent', 1.00, 'Ar');

-- Insertion de la configuration par défaut
INSERT INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '10', 'Pourcentage des frais d''achat appliqués aux achats (ex: 10 pour 10%)')
ON DUPLICATE KEY UPDATE valeur = valeur;

-- Vue: Tableau de bord
CREATE OR REPLACE VIEW v_dashboard AS
SELECT 
    v.id_ville,
    v.nom_ville,
    v.region,
    tb.id_type,
    tb.nom_type,
    tb.categorie,
    tb.unite,
    tb.prix_unitaire,
    COALESCE(SUM(b.quantite), 0) as besoin_total,
    COALESCE(SUM(dist.quantite_distribuee), 0) as don_recu,
    COALESCE(SUM(b.quantite), 0) - COALESCE(SUM(dist.quantite_distribuee), 0) as besoin_restant,
    COALESCE(SUM(b.quantite * tb.prix_unitaire), 0) as valeur_besoin,
    COALESCE(SUM(dist.quantite_distribuee * tb.prix_unitaire), 0) as valeur_recue
FROM ville v
CROSS JOIN type_besoin tb
LEFT JOIN besoin b ON v.id_ville = b.id_ville AND tb.id_type = b.id_type
LEFT JOIN distribution dist ON v.id_ville = dist.id_ville 
    AND EXISTS (SELECT 1 FROM don d WHERE d.id_don = dist.id_don AND d.id_type = tb.id_type)
GROUP BY v.id_ville, v.nom_ville, v.region, tb.id_type, tb.nom_type, tb.categorie, tb.unite, tb.prix_unitaire
HAVING besoin_total > 0 OR don_recu > 0;
