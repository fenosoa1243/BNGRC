# BNGRC - Système de Gestion des Dons

Projet de gestion des besoins et dons pour le BNGRC (Bureau National de Gestion des Risques et des Catastrophes).

## Installation

### 1. Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache avec mod_rewrite activé ou Nginx)

### 2. Configuration de la base de données

1. Créez une base de données MySQL :
```sql
CREATE DATABASE bngrc_gestion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importez le fichier `database.sql` :
```bash
mysql -u votre_utilisateur -p bngrc_gestion < database.sql
```

### 3. Configuration de l'application

1. Copiez le fichier de configuration :
```bash
cd app/config
cp config.example.php config.php
```

2. Éditez `app/config/config.php` et modifiez les paramètres de connexion à la base de données :
```php
'database' => [
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'bngrc_gestion',
    'user' => 'votre_utilisateur',
    'password' => 'votre_mot_de_passe',
    'charset' => 'utf8mb4'
]
```

### 4. Configuration du serveur web

#### Apache

Assurez-vous que mod_rewrite est activé :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Le fichier `.htaccess` dans le dossier `public/` est déjà configuré.

Configurez votre VirtualHost pour pointer vers le dossier `public/` :
```apache
<VirtualHost *:8000>
    DocumentRoot "/chemin/vers/BNGRC-main/public"
    <Directory "/chemin/vers/BNGRC-main/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

Exemple de configuration Nginx :
```nginx
server {
    listen 8000;
    root /chemin/vers/BNGRC-main/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### Serveur PHP intégré (développement uniquement)

Pour tester rapidement en local :
```bash
cd public
php -S localhost:8000
```

### 5. Accéder à l'application

Ouvrez votre navigateur et accédez à :
```
http://localhost:8000/dashboard
```

## Structure du projet

```
BNGRC-main/
├── app/
│   ├── config/          # Configuration de l'application
│   │   ├── bootstrap.php
│   │   ├── routes.php
│   │   ├── services.php
│   │   ├── config.example.php
│   │   └── config.php
│   ├── controllers/     # Contrôleurs
│   │   ├── DashboardController.php
│   │   ├── VilleController.php
│   │   ├── BesoinController.php
│   │   ├── DonController.php
│   │   └── DistributionController.php
│   ├── models/          # Modèles
│   └── views/           # Vues
│       ├── dashboard/
│       ├── ville/
│       ├── besoin/
│       ├── don/
│       └── distribution/
├── public/              # Point d'entrée web
│   ├── index.php
│   ├── .htaccess
│   └── assets/          # CSS, JS, images
├── vendor/              # Dépendances (FlightPHP, Tracy)
├── database.sql         # Script SQL
└── README.md
```

## Fonctionnalités

- **Dashboard** : Vue d'ensemble des statistiques
- **Gestion des villes** : CRUD des villes
- **Gestion des besoins** : Enregistrement des besoins par ville et type
- **Gestion des dons** : Enregistrement des dons reçus
- **Distribution** : Simulation et exécution du dispatch automatique des dons selon les besoins

## Technologies utilisées

- **Framework** : FlightPHP 3.x
- **Base de données** : MySQL/MariaDB
- **Debugger** : Tracy
- **Frontend** : HTML5, CSS3, JavaScript vanilla

## Dépannage

### Erreur 404 sur les routes
- Vérifiez que mod_rewrite est activé (Apache)
- Vérifiez que le fichier `.htaccess` est présent dans `public/`
- Vérifiez la configuration du DocumentRoot

### Erreur de connexion à la base de données
- Vérifiez que la base de données existe
- Vérifiez les paramètres dans `app/config/config.php`
- Vérifiez que l'utilisateur MySQL a les permissions nécessaires

### Page blanche ou erreur 500
- Activez l'affichage des erreurs dans `config.php`
- Consultez les logs du serveur web
- Vérifiez les permissions des fichiers

## Contact

Projet Final S3 - 2026 BNGRC
