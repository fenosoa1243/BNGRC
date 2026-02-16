# Guide de Test - BNGRC Nouvelles Fonctionnalités

## Prérequis

1. **Base de données MySQL/MariaDB** installée et démarrée
2. **PHP 7.4+** installé
3. **Serveur web** (Apache/Nginx ou PHP built-in)
4. Base de données créée avec le fichier `database.sql`

## Installation et Configuration

### 1. Créer la base de données

```bash
# Connexion à MySQL
mysql -u root -p

# Création de la base
CREATE DATABASE IF NOT EXISTS bngrc_dons CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bngrc_dons;
SOURCE /path/to/database.sql;

# Vérifier les tables
SHOW TABLES;
```

Vous devriez voir ces tables :
- `ville`
- `type_besoin`
- `besoin`
- `don`
- `distribution`
- `achat` (nouvelle)
- `configuration` (nouvelle)
- `v_dashboard` (vue)

### 2. Configuration de l'application

Vérifier que `app/config/config.php` contient les bonnes informations de connexion :

```php
'database' => [
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => 'bngrc_dons',
    'user'     => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
]
```

### 3. Démarrer le serveur

```bash
# Depuis le dossier public/
cd public
php -S localhost:8000
```

Accéder à : http://localhost:8000/dashboard

## Tests des Fonctionnalités

### Test 1 : Dashboard et Navigation

**Objectif** : Vérifier que toutes les pages sont accessibles

**Étapes** :
1. Accéder à http://localhost:8000/dashboard
2. Vérifier que la navigation contient 7 liens :
   - 📊 Dashboard
   - 🏙️ Villes
   - 📋 Besoins
   - 🎁 Dons
   - 📦 Distributions
   - 🛒 Achats (nouveau)
   - 📈 Récapitulation (nouveau)
3. Cliquer sur chaque lien et vérifier qu'il n'y a pas d'erreur 404

**Résultat attendu** : 
- Tous les liens fonctionnent
- Le design est professionnel avec dégradés et animations

### Test 2 : Saisie de Données de Base

**Objectif** : Créer des données pour tester les achats

**Étapes** :

#### 2.1 Vérifier les villes (déjà insérées)
```sql
SELECT * FROM ville;
```
Devrait retourner 5 villes : Antananarivo, Toamasina, Antsirabe, Mahajanga, Toliara

#### 2.2 Créer des besoins
1. Aller sur /besoins/nouveau
2. Créer un besoin :
   - Ville : Antananarivo
   - Type : Riz (nature)
   - Quantité : 100
3. Créer un autre besoin :
   - Ville : Toamasina
   - Type : Tôle (matériau)
   - Quantité : 20

#### 2.3 Créer des dons en argent
1. Aller sur /dons/nouveau
2. Créer un don :
   - Type : Argent
   - Quantité : 500000 (500,000 Ar)
   - Donateur : Test Donateur

**Résultat attendu** :
- Besoins créés avec succès
- Don en argent disponible pour les achats

### Test 3 : Système d'Achat - Mode Simulation

**Objectif** : Tester le workflow complet d'achat en mode simulation

**Étapes** :

1. **Accéder au formulaire d'achat**
   - Aller sur /achats/nouveau
   - Vérifier que les dons en argent disponibles s'affichent (500,000 Ar)
   - Vérifier que les besoins restants apparaissent dans le tableau

2. **Effectuer un achat simulé**
   - Cliquer sur "🛒 Acheter" pour le besoin de Riz à Antananarivo
   - Modal s'ouvre avec les détails
   - Saisir quantité : 50 kg
   - Vérifier le calcul :
     - Montant base = 50 × 2000 = 100,000 Ar
     - Frais (10%) = 10,000 Ar
     - Montant total = 110,000 Ar
   - Sélectionner mode : "Simuler (prévisualisation)"
   - Cliquer sur "✅ Confirmer l'Achat"

3. **Vérifier la liste des achats**
   - Aller sur /achats
   - L'achat doit apparaître avec statut "⏳ Simulé"
   - Vérifier le filtre par ville fonctionne

4. **Consulter la simulation**
   - Aller sur /achats/simuler
   - Vérifier les statistiques :
     - Achats Simulés : 1
     - Montant Total : 110,000 Ar
     - Argent Disponible : 500,000 Ar
   - Message de succès : "Les fonds sont suffisants"

5. **Ajouter un deuxième achat simulé**
   - Retour sur /achats/nouveau
   - Acheter 10 tôles pour Toamasina
   - Montant base : 250,000 Ar
   - Avec frais : 275,000 Ar
   - Mode : Simuler

6. **Vérifier la simulation mise à jour**
   - Retour sur /achats/simuler
   - Achats Simulés : 2
   - Montant Total : 385,000 Ar

**Résultat attendu** :
- Tous les calculs sont corrects
- Les achats sont enregistrés avec statut "simule"
- Aucune modification des tables don et distribution

### Test 4 : Validation des Achats Simulés

**Objectif** : Valider les achats et vérifier la création des dons/distributions

**Étapes** :

1. **Avant validation - Vérifier l'état actuel**
   ```sql
   SELECT COUNT(*) FROM don WHERE donateur = 'Achat via dons argent';
   -- Devrait retourner 0
   
   SELECT COUNT(*) FROM distribution;
   -- Compter le nombre actuel
   ```

2. **Valider les achats**
   - Sur /achats/simuler
   - Cliquer sur "✅ Valider Tous les Achats"
   - Confirmer dans la boîte de dialogue
   - Redirection vers /achats avec message de succès

3. **Après validation - Vérifier les changements**
   ```sql
   -- Vérifier les nouveaux dons créés
   SELECT * FROM don WHERE donateur = 'Achat via dons argent';
   -- Devrait retourner 2 dons (Riz et Tôle)
   
   -- Vérifier les distributions
   SELECT d.*, v.nom_ville, t.nom_type 
   FROM distribution d
   JOIN don dn ON d.id_don = dn.id_don
   JOIN ville v ON d.id_ville = v.id_ville
   JOIN type_besoin t ON dn.id_type = t.id_type
   WHERE dn.donateur = 'Achat via dons argent';
   -- Devrait retourner 2 distributions
   
   -- Vérifier la déduction des dons en argent
   SELECT quantite_restante FROM don 
   WHERE id_type = (SELECT id_type FROM type_besoin WHERE categorie = 'argent');
   -- Devrait retourner 115,000 Ar (500,000 - 385,000)
   ```

4. **Vérifier la liste des achats**
   - Sur /achats
   - Les achats ont maintenant le statut "✅ Validé"

**Résultat attendu** :
- Dons créés automatiquement
- Distributions créées vers les bonnes villes
- Dons en argent diminués du montant total avec frais
- Statut des achats changé à "valide"

### Test 5 : Validations et Erreurs

**Objectif** : Tester les validations d'erreur

**Étapes** :

1. **Test des fonds insuffisants**
   - Argent restant : 115,000 Ar
   - Essayer d'acheter pour 200,000 Ar (avec frais = 220,000 Ar)
   - Message d'erreur attendu : "Fonds insuffisants"

2. **Test d'achat existant dans les dons**
   - Créer un don de Riz : 50 kg
   - Essayer d'acheter du Riz alors qu'il existe dans les dons restants
   - Message d'erreur attendu : "Ce type de besoin existe déjà dans les dons restants"

3. **Test de validation de quantité**
   - Essayer de saisir une quantité négative ou nulle
   - Le formulaire HTML5 devrait empêcher la soumission

**Résultat attendu** :
- Toutes les validations fonctionnent correctement
- Messages d'erreur clairs et explicites

### Test 6 : Annulation de Simulation

**Objectif** : Vérifier que l'annulation supprime bien les achats simulés

**Étapes** :

1. **Créer des achats simulés**
   - Créer 2-3 achats en mode "Simuler"
   - Aller sur /achats/simuler

2. **Annuler la simulation**
   - Cliquer sur "❌ Annuler la Simulation"
   - Confirmer l'annulation

3. **Vérifier la suppression**
   ```sql
   SELECT COUNT(*) FROM achat WHERE statut = 'simule';
   -- Devrait retourner 0
   ```
   - Aller sur /achats/simuler
   - Message : "Aucun achat simulé"

**Résultat attendu** :
- Achats simulés supprimés
- Achats validés toujours présents

### Test 7 : Page de Récapitulation

**Objectif** : Tester les statistiques et l'actualisation Ajax

**Étapes** :

1. **Accéder à la page**
   - Aller sur /recap
   - Les statistiques se chargent automatiquement

2. **Vérifier les statistiques principales**
   - Besoins Totaux : Somme de tous les besoins
   - Besoins Satisfaits : Somme des distributions
   - Besoins Restants : Différence
   - Taux de Satisfaction : Pourcentage avec barre de progression

3. **Vérifier les tableaux**
   - Récapitulation par Ville :
     - Toutes les villes avec des besoins
     - Montants en Ar
     - Taux de satisfaction
   - Récapitulation par Type :
     - Tous les types avec des besoins
     - Quantités et valeurs

4. **Tester l'actualisation Ajax**
   - Dans un autre onglet, créer un nouveau besoin ou don
   - Retour sur /recap
   - Cliquer sur "🔄 Actualiser"
   - Les données doivent se mettre à jour sans rechargement de page

5. **Vérifier l'API**
   - Accéder directement à /api/recap/stats
   - Devrait retourner du JSON avec toutes les statistiques

**Résultat attendu** :
- Statistiques correctes et à jour
- Actualisation Ajax fonctionne
- Tableaux bien formatés et lisibles

### Test 8 : Filtre par Ville

**Objectif** : Tester le filtre de la liste des achats

**Étapes** :

1. **Créer des achats pour différentes villes**
   - Achat 1 : Antananarivo
   - Achat 2 : Toamasina
   - Achat 3 : Mahajanga

2. **Tester le filtre**
   - Sur /achats
   - Sélectionner "Antananarivo" dans le dropdown
   - Seuls les achats d'Antananarivo s'affichent
   - URL change : /achats?id_ville=1

3. **Revenir à tous**
   - Sélectionner "Toutes les villes"
   - Tous les achats s'affichent

**Résultat attendu** :
- Filtre fonctionne correctement
- URL reflète le filtre
- Transition fluide entre les vues

### Test 9 : Design et Responsive

**Objectif** : Vérifier le design professionnel et le responsive

**Étapes** :

1. **Tester sur desktop (> 768px)**
   - Ouvrir le navigateur en plein écran
   - Vérifier que la navigation est horizontale
   - Les cartes statistiques sont en grille
   - Les tableaux s'affichent correctement

2. **Tester sur tablet (480px - 768px)**
   - Réduire la largeur du navigateur à environ 700px
   - Vérifier que la grille s'ajuste
   - Navigation toujours horizontale mais peut wrap

3. **Tester sur mobile (< 480px)**
   - Réduire à environ 400px
   - Navigation devient verticale
   - Boutons deviennent full-width
   - Cartes s'empilent en une colonne

4. **Tester les animations**
   - Survoler les boutons : ombres colorées
   - Survoler les cartes : légère élévation
   - Cliquer sur un bouton : effet de ripple

5. **Accéder à la preview**
   - Ouvrir design-preview.html dans le navigateur
   - Vérifier tous les composants :
     - Boutons (6 types, 3 tailles)
     - Cartes statistiques
     - Tableaux élégants
     - Badges et status
     - Alertes (4 types)
     - Formulaires

**Résultat attendu** :
- Design moderne et professionnel
- Animations fluides
- Responsive sur toutes les tailles
- Cohérence visuelle sur toutes les pages

### Test 10 : Performance et Sécurité

**Objectif** : Vérifier la performance et la sécurité

**Étapes** :

1. **Test de charge**
   - Créer 50+ besoins
   - Créer 50+ achats
   - Aller sur /recap
   - L'actualisation doit se faire en < 2 secondes

2. **Test SQL Injection**
   - Essayer d'injecter du SQL dans les formulaires
   - Ex: `'; DROP TABLE achat; --`
   - Les prepared statements doivent protéger

3. **Test de validation**
   - Essayer de soumettre des données invalides
   - Quantités négatives
   - Types inexistants
   - Toutes doivent être rejetées

4. **Test de transaction**
   - Simuler une erreur pendant la validation d'achat
   - Vérifier que le rollback fonctionne
   - Aucune donnée partielle en base

**Résultat attendu** :
- Bonnes performances même avec beaucoup de données
- Protection contre les injections SQL
- Validations strictes
- Intégrité des données garantie

## Checklist Complète

- [ ] Base de données créée avec toutes les tables
- [ ] Configuration de connexion correcte
- [ ] Serveur démarré et accessible
- [ ] Navigation avec 7 liens fonctionnels
- [ ] Dashboard affiche les statistiques
- [ ] Formulaire d'achat affiche les besoins restants
- [ ] Calcul automatique avec frais d'achat (10%)
- [ ] Achats en mode simulation créés
- [ ] Page de simulation affiche les achats
- [ ] Validation des achats fonctionne
- [ ] Dons et distributions créés automatiquement
- [ ] Dons en argent déduits correctement
- [ ] Validation des fonds insuffisants
- [ ] Validation des achats existants
- [ ] Annulation de simulation fonctionne
- [ ] Page de récapitulation affiche les stats
- [ ] Actualisation Ajax fonctionne
- [ ] Filtre par ville fonctionne
- [ ] Design professionnel sur desktop
- [ ] Design responsive sur tablet
- [ ] Design responsive sur mobile
- [ ] Animations et transitions fluides
- [ ] Pas de vulnérabilités SQL
- [ ] Performances acceptables

## Résolution de Problèmes

### Problème : Page blanche
**Solution** : 
- Vérifier les logs PHP
- Activer l'affichage des erreurs dans config.php
- Vérifier que toutes les classes sont correctement chargées

### Problème : Erreur de connexion MySQL
**Solution** :
- Vérifier que MySQL est démarré
- Vérifier les credentials dans config.php
- Vérifier que la base existe

### Problème : Routes ne fonctionnent pas
**Solution** :
- Vérifier que mod_rewrite est activé (Apache)
- Vérifier le fichier .htaccess dans public/
- Utiliser le serveur PHP built-in pour tester

### Problème : Ajax ne fonctionne pas sur /recap
**Solution** :
- Ouvrir la console du navigateur (F12)
- Vérifier les erreurs JavaScript
- Vérifier que l'API /api/recap/stats retourne du JSON

### Problème : Design cassé
**Solution** :
- Vérifier que style.css est bien chargé
- Vérifier le chemin : /assets/css/style.css
- Vider le cache du navigateur (Ctrl+F5)

## Support

Pour toute question ou problème :
1. Consulter la documentation dans DOCUMENTATION_NOUVELLES_FONCTIONNALITES.md
2. Vérifier les logs d'erreur PHP
3. Consulter les logs MySQL
4. Ouvrir la console du navigateur pour les erreurs JavaScript

## Conclusion

Si tous les tests passent, l'implémentation est complète et fonctionnelle. Le système offre :
- Une gestion complète des achats avec simulation
- Une page de récapitulation dynamique
- Un design professionnel et responsive
- Une sécurité renforcée avec validations
- Une expérience utilisateur optimale
