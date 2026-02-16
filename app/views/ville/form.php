<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($ville_data) ? 'Modifier' : 'Nouvelle'; ?> Ville - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle"><?php echo isset($ville_data) ? 'Modifier une Ville' : 'Nouvelle Ville'; ?></p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes" class="active">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
        </nav>

        <div class="section">
            <div class="form-container">
                <h2>🏙️ <?php echo isset($ville_data) ? 'Modifier la Ville' : 'Nouvelle Ville'; ?></h2>
                
                <form method="POST" action="<?= BASE_URL ?>/villes/enregistrer" class="form">
                    <?php if(isset($ville_data)): ?>
                        <input type="hidden" name="id_ville" value="<?php echo $ville_data['id_ville']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nom_ville">🏙️ Nom de la Ville *</label>
                        <input type="text" 
                               name="nom_ville" 
                               id="nom_ville" 
                               required 
                               class="form-control"
                               value="<?php echo isset($ville_data) ? htmlspecialchars($ville_data['nom_ville']) : ''; ?>"
                               placeholder="Ex: Antananarivo"
                               maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="region">📍 Région *</label>
                        <input type="text" 
                               name="region" 
                               id="region" 
                               required 
                               class="form-control"
                               value="<?php echo isset($ville_data) ? htmlspecialchars($ville_data['region']) : ''; ?>"
                               placeholder="Ex: Analamanga"
                               maxlength="100">
                    </div>

                    <div class="alert alert-info">
                        <strong>ℹ️ Information :</strong> Les villes enregistrées pourront être utilisées lors de la saisie des besoins.
                    </div>

                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/villes" class="btn btn-secondary">↩️ Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <?php echo isset($ville_data) ? '💾 Mettre à jour' : '➕ Enregistrer'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>
</body>
</html>