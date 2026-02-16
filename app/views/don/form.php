<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer un Don - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Enregistrer un Don</p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons" class="active">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
        </nav>

        <div class="section">
            <div class="form-container">
                <h2>🎁 Nouveau Don</h2>
                
                <form method="POST" action="<?= BASE_URL ?>/dons/enregistrer" class="form">
                    <div class="form-group">
                        <label for="donateur">👤 Nom du Donateur</label>
                        <input type="text" 
                               name="donateur" 
                               id="donateur" 
                               class="form-control"
                               placeholder="Ex: Croix Rouge, Jean Dupont, Entreprise ABC..."
                               maxlength="100">
                        <small class="form-text">Facultatif - Laissez vide pour un don anonyme</small>
                    </div>

                    <div class="form-group">
                        <label for="id_type">📦 Type de Don *</label>
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
                                    data-categorie="<?php echo $type['categorie']; ?>">
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
                               placeholder="Ex: 100">
                        <small id="unite-display" class="form-text"></small>
                    </div>

                    <div class="form-group" id="valeur-group" style="display: none;">
                        <label>💰 Valeur Estimée</label>
                        <div class="valeur-display">
                            <strong id="valeur-display">0 Ar</strong>
                        </div>
                        <small class="form-text">Cette valeur est calculée automatiquement selon les prix de référence</small>
                    </div>

                    <div class="alert alert-info">
                        <strong>ℹ️ Information :</strong> Ce don sera automatiquement disponible pour la distribution aux villes ayant des besoins correspondants.
                    </div>

                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/dons" class="btn btn-secondary">↩️ Annuler</a>
                        <button type="submit" class="btn btn-success">🎁 Enregistrer le Don</button>
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
    </script>
</body>
</html>