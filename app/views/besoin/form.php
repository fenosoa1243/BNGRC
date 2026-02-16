<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisir un Besoin - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle"><?php echo isset($besoin_data) ? 'Modifier un Besoin' : 'Saisir un Besoin'; ?></p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins" class="active">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
        </nav>

        <div class="section">
            <div class="form-container">
                <h2>📋 <?php echo isset($besoin_data) ? 'Modifier le Besoin' : 'Nouveau Besoin'; ?></h2>
                
                <form method="POST" action="<?= BASE_URL ?>/besoins/enregistrer" class="form">
                    <?php if(isset($besoin_data)): ?>
                        <input type="hidden" name="id_besoin" value="<?php echo $besoin_data['id_besoin']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="id_ville">🏙️ Ville *</label>
                        <select name="id_ville" id="id_ville" required class="form-control">
                            <option value="">-- Sélectionnez une ville --</option>
                            <?php foreach($villes as $ville): ?>
                                <option value="<?php echo $ville['id_ville']; ?>"
                                    <?php echo (isset($besoin_data) && $besoin_data['id_ville'] == $ville['id_ville']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ville['nom_ville']); ?> 
                                    (<?php echo htmlspecialchars($ville['region']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_type">📦 Type de Besoin *</label>
                        <select name="id_type" id="id_type" required class="form-control">
                            <option value="">-- Sélectionnez un type --</option>
                            <?php 
                            $currentCategorie = null;
                            foreach($types_besoins as $type): 
                                if($currentCategorie != $type['categorie']):
                                    if($currentCategorie !== null) echo '</optgroup>';
                                    $currentCategorie = $type['categorie'];
                                    $categorieLabel = ucfirst($currentCategorie);
                                    echo '<optgroup label="' . $categorieLabel . '">';
                                endif;
                            ?>
                                <option value="<?php echo $type['id_type']; ?>"
                                    data-unite="<?php echo $type['unite']; ?>"
                                    data-prix="<?php echo $type['prix_unitaire']; ?>"
                                    <?php echo (isset($besoin_data) && $besoin_data['id_type'] == $type['id_type']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['nom_type']); ?> 
                                    (<?php echo number_format($type['prix_unitaire'], 0, ',', ' '); ?> Ar/<?php echo $type['unite']; ?>)
                                </option>
                            <?php 
                            endforeach; 
                            if($currentCategorie !== null) echo '</optgroup>';
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantite">🔢 Quantité *</label>
                        <input type="number" 
                               name="quantite" 
                               id="quantite" 
                               step="0.01" 
                               min="0.01" 
                               required 
                               class="form-control"
                               value="<?php echo isset($besoin_data) ? $besoin_data['quantite'] : ''; ?>"
                               placeholder="Ex: 100">
                        <small id="unite-display" class="form-text"></small>
                    </div>

                    <div class="form-group" id="valeur-group" style="display: none;">
                        <label>💰 Valeur Estimée</label>
                        <div class="valeur-display">
                            <strong id="valeur-display">0 Ar</strong>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/besoins" class="btn btn-secondary">↩️ Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <?php echo isset($besoin_data) ? '💾 Mettre à jour' : '➕ Enregistrer'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <script>
        // Calculer la valeur en temps réel
        const typeSelect = document.getElementById('id_type');
        const quantiteInput = document.getElementById('quantite');
        const uniteDisplay = document.getElementById('unite-display');
        const valeurDisplay = document.getElementById('valeur-display');
        const valeurGroup = document.getElementById('valeur-group');

        function updateCalculations() {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            const unite = selectedOption.getAttribute('data-unite');
            const prix = parseFloat(selectedOption.getAttribute('data-prix'));
            const quantite = parseFloat(quantiteInput.value) || 0;

            if (unite && prix) {
                uniteDisplay.textContent = `Unité: ${unite}`;
                const valeur = quantite * prix;
                valeurDisplay.textContent = valeur.toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }) + ' Ar';
                valeurGroup.style.display = 'block';
            } else {
                uniteDisplay.textContent = '';
                valeurGroup.style.display = 'none';
            }
        }

        typeSelect.addEventListener('change', updateCalculations);
        quantiteInput.addEventListener('input', updateCalculations);

        // Calculer au chargement si modification
        if (typeSelect.value && quantiteInput.value) {
            updateCalculations();
        }
    </script>
</body>
</html>