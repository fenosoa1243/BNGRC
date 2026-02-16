# BNGRC - Documentation des Nouvelles Fonctionnalités

## 1. Système d'Achat avec Dons en Argent

### Description
Le système permet d'acheter des besoins en nature et en matériaux en utilisant les dons en argent disponibles, avec un frais d'achat configurable.

### Fonctionnalités Principales

#### 1.1 Page de Saisie des Achats (`/achats/nouveau`)
- **Liste des besoins restants** : Affiche tous les besoins non satisfaits filtrés par ville
- **Calcul automatique** : Montant base + frais d'achat (configurable à 10% par défaut)
- **Validation** : Vérifie les fonds disponibles et empêche l'achat si le besoin existe déjà dans les dons
- **Modes** : 
  - **Simulation** : Prévisualisation avant validation
  - **Validation directe** : Crée immédiatement le don et la distribution

#### 1.2 Liste des Achats (`/achats`)
- **Filtrage par ville** : Dropdown permettant de filtrer les achats par ville
- **Affichage détaillé** : Date, ville, type, quantité, montants, frais, statut
- **Totaux** : Calcul automatique du montant total des achats

#### 1.3 Page de Simulation (`/achats/simuler`)
- **Aperçu des achats simulés** : Visualisation de tous les achats en mode simulation
- **Statistiques** : Nombre d'achats, montant total, argent disponible
- **Validation/Annulation** : 
  - Bouton pour valider tous les achats simulés en une fois
  - Bouton pour annuler la simulation
  - Vérification des fonds suffisants

### 1.4 Validation des Achats
Quand un achat est validé :
1. Déduit le montant (avec frais) des dons en argent disponibles (FIFO)
2. Crée un don du type acheté
3. Crée une distribution vers la ville concernée
4. Marque le don comme distribué

### 1.5 Configuration
- **Table `configuration`** : Stocke les paramètres système
- **Frais d'achat** : Configurable via la clé `frais_achat_pourcentage` (défaut: 10%)

## 2. Page de Récapitulation (`/recap`)

### Description
Page de synthèse affichant les statistiques globales avec actualisation Ajax.

### Fonctionnalités

#### 2.1 Statistiques Principales
- **Besoins Totaux** : Montant total de tous les besoins enregistrés
- **Besoins Satisfaits** : Montant des besoins couverts par les distributions
- **Besoins Restants** : Montant des besoins non satisfaits
- **Taux de Satisfaction** : Pourcentage de besoins satisfaits avec barre de progression

#### 2.2 Récapitulation par Ville
Tableau détaillé montrant pour chaque ville :
- Nom et région
- Besoin total en Ar
- Montant satisfait en Ar
- Montant restant en Ar
- Taux de satisfaction en %

#### 2.3 Récapitulation par Type de Besoin
Tableau détaillé montrant pour chaque type :
- Nom et catégorie (nature, matériau, argent)
- Quantités : besoin, satisfait, restant
- Valeurs : besoin, satisfait

#### 2.4 Actualisation Ajax
- **Bouton "Actualiser"** : Recharge les données sans recharger la page
- **API REST** : Endpoint `/api/recap/stats` retournant JSON
- **Interface dynamique** : Mise à jour instantanée des statistiques

## 3. Améliorations CSS

### Style Professionnel
Le CSS a été complètement refondu pour un rendu professionnel :

#### 3.1 Variables CSS
- Utilisation de variables CSS (`:root`) pour une maintenance facile
- Palette de couleurs cohérente
- Transitions et animations fluides

#### 3.2 Composants Modernisés

**Header**
- Dégradé avec motif de grille subtil
- Typographie hiérarchisée

**Navigation**
- Effet de transition au survol
- Indicateur visuel pour la page active
- Responsive avec breakpoints

**Cards (Statistiques)**
- Ombres élégantes avec niveaux (sm, md, lg)
- Bordure supérieure colorée
- Animation au survol (translateY)

**Boutons**
- Dégradés pour chaque type (primary, success, warning, etc.)
- Effet de ripple au clic
- Ombres colorées au survol
- États disabled avec opacité

**Tables**
- Header avec dégradé sombre
- Survol de lignes avec transition
- Bordures subtiles

**Badges**
- Dégradés par catégorie
- Texte en majuscules avec letterspacing
- Coins arrondis

**Alertes**
- Animation de slide-in
- Bordure gauche colorée
- Dégradés subtils

#### 3.3 Responsive Design
- Breakpoints à 768px et 480px
- Navigation verticale sur mobile
- Grilles qui s'adaptent
- Boutons full-width sur petit écran

#### 3.4 Animations
- Fade-in pour les éléments
- Pulse pour les indicateurs de chargement
- Transitions fluides (cubic-bezier)

#### 3.5 Accessibilité
- Contrastes suffisants
- Tailles de police lisibles
- Focus states visibles
- Print styles pour l'impression

## 4. Structure de Base de Données

### Nouvelles Tables

#### 4.1 Table `configuration`
```sql
CREATE TABLE configuration (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur TEXT NOT NULL,
    description VARCHAR(255),
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 4.2 Table `achat`
```sql
CREATE TABLE achat (
    id_achat INT PRIMARY KEY AUTO_INCREMENT,
    id_ville INT NOT NULL,
    id_type INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    frais_achat DECIMAL(5,2) NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('simule', 'valide') DEFAULT 'simule',
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville) ON DELETE CASCADE,
    FOREIGN KEY (id_type) REFERENCES type_besoin(id_type) ON DELETE CASCADE
);
```

## 5. Architecture MVC

### Models
- **`Achat.php`** : Gestion des achats
- **`Configuration.php`** : Gestion des paramètres système

### Controllers
- **`AchatController.php`** : 
  - `liste()` : Liste des achats avec filtre
  - `form()` : Formulaire d'achat
  - `enregistrer()` : Enregistrement d'un achat
  - `simuler()` : Page de simulation
  - `valider()` : Validation des achats simulés
  - `annulerSimulation()` : Annulation de la simulation
  
- **`RecapController.php`** :
  - `index()` : Page de récapitulation
  - `getStats()` : API REST pour les statistiques

### Views
- **`app/views/achat/`**
  - `form.php` : Formulaire d'achat
  - `liste.php` : Liste des achats
  - `simuler.php` : Page de simulation
  
- **`app/views/recap/`**
  - `index.php` : Page de récapitulation

### Routes
```php
// Achats
Flight::route('GET /achats', [AchatController::class, 'liste']);
Flight::route('GET /achats/nouveau', [AchatController::class, 'form']);
Flight::route('POST /achats/enregistrer', [AchatController::class, 'enregistrer']);
Flight::route('GET /achats/simuler', [AchatController::class, 'simuler']);
Flight::route('POST /achats/valider', [AchatController::class, 'valider']);
Flight::route('POST /achats/annuler', [AchatController::class, 'annulerSimulation']);

// Récapitulation
Flight::route('GET /recap', [RecapController::class, 'index']);
Flight::route('GET /api/recap/stats', [RecapController::class, 'getStats']);
```

## 6. Utilisation

### Workflow d'Achat

1. **Consultation des besoins restants**
   - Aller sur `/achats/nouveau`
   - Voir la liste des besoins non satisfaits

2. **Création d'un achat**
   - Cliquer sur "Acheter" pour un besoin
   - Saisir la quantité
   - Choisir le mode (Simuler ou Valider)
   - Voir le calcul automatique avec frais

3. **Simulation (optionnel)**
   - Créer plusieurs achats en mode "Simuler"
   - Aller sur `/achats/simuler`
   - Vérifier les montants et la disponibilité des fonds
   - Valider ou annuler

4. **Validation**
   - Les achats validés créent automatiquement des dons et distributions
   - Les montants sont déduits des dons en argent

### Consultation de la Récapitulation

1. **Accéder à la page**
   - Aller sur `/recap`
   - Les statistiques se chargent automatiquement

2. **Actualisation**
   - Cliquer sur "Actualiser" pour rafraîchir les données
   - Pas de rechargement de page (Ajax)

3. **Analyse**
   - Vue d'ensemble : totaux et taux de satisfaction
   - Détail par ville : identifier les villes prioritaires
   - Détail par type : voir les besoins les plus urgents

## 7. Sécurité et Validations

### Validations Côté Serveur
- Vérification des fonds disponibles
- Vérification de l'existence dans les dons restants
- Validation des montants et quantités
- Protection contre les injections SQL (prepared statements)

### Transactions
- Utilisation de transactions pour la validation des achats
- Rollback en cas d'erreur

### Gestion d'Erreurs
- Messages d'erreur explicites
- Redirections avec paramètres GET
- Try-catch pour les exceptions

## 8. Performance

### Optimisations
- Requêtes SQL optimisées avec JOINs
- Index sur les clés étrangères
- Utilisation de PDO pour les requêtes préparées
- Ajax pour éviter les rechargements complets

### Cache
- Possibilité d'ajouter du cache pour les statistiques
- Auto-refresh optionnel (commenté dans le code)

## 9. Extensions Futures

### Suggestions d'Améliorations
1. **Gestion des utilisateurs** : Authentification et rôles
2. **Historique** : Traçabilité des actions
3. **Rapports PDF** : Génération de rapports
4. **Notifications** : Email/SMS pour les distributions
5. **Dashboard avancé** : Graphiques interactifs
6. **Export** : CSV/Excel des données
7. **Multi-devise** : Support de plusieurs devises
8. **API complète** : RESTful API pour intégrations

## 10. Technologies Utilisées

- **Backend** : PHP 7.4+
- **Framework** : FlightPHP 3.x
- **Base de données** : MySQL 5.7+ / MariaDB
- **Frontend** : HTML5, CSS3, JavaScript vanilla
- **Architecture** : MVC
- **Pattern** : Active Record (Models)
