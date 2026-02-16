<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel Achat - BNGRC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Acheter des Besoins avec les Dons en Argent</p>
        </header>

        <nav class="main-nav">
            <a href="/dashboard">📊 Dashboard</a>
            <a href="/villes">🏙️ Villes</a>
            <a href="/besoins">📋 Besoins</a>
            <a href="/dons">🎁 Dons</a>
            <a href="/distributions">📦 Distributions</a>
            <a href="/achats" class="active">🛒 Achats</a>
            <a href="/recap">📈 Récapitulation</a>
        </nav>

        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'insufficient_funds'): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erreur :</strong> Fonds insuffisants. Montant requis : <?php echo number_format($_GET['required'] ?? 0, 0, ',', ' '); ?> Ar. 
                    Disponible : <?php echo number_format($_GET['available'] ?? 0, 0, ',', ' '); ?> Ar.
                </div>
            <?php elseif($_GET['error'] == 'already_available'): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erreur :</strong> Ce type de besoin existe déjà dans les dons restants. Utilisez d'abord les dons disponibles.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="section">
            <div class="info-box">
                <h3>💰 Dons en Argent Disponibles</h3>
                <p class="valeur-display">
                    <strong><?php echo number_format($argent_disponible, 0, ',', ' '); ?> Ar</strong>
                </p>
                <p style="margin-top: 10px; color: #718096;">
                    <strong>Note :</strong> Un frais d'achat de <?php echo $frais_pourcentage; ?>% sera appliqué 
                    (ex: achat de 100 Ar = <?php echo 100 + (100 * $frais_pourcentage / 100); ?> Ar avec frais).
                </p>
            </div>

            <h2>🛒 Sélectionner un Besoin Restant</h2>
            
            <?php if(empty($besoins_restants)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Information :</strong> Aucun besoin restant à acheter pour le moment. 
                    Tous les besoins ont été satisfaits ou aucun besoin n'a été enregistré.
                </div>
                <div class="form-actions-center">
                    <a href="/achats" class="btn btn-secondary">↩️ Retour à la liste</a>
                    <a href="/besoins/nouveau" class="btn btn-primary">➕ Ajouter un Besoin</a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table" id="besoins-table">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Région</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité Restante</th>
                                <th>Prix Unitaire</th>
                                <th>Valeur Restante</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($besoins_restants as $besoin): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($besoin['nom_ville']); ?></td>
                                    <td><?php echo htmlspecialchars($besoin['region']); ?></td>
                                    <td><?php echo htmlspecialchars($besoin['nom_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $besoin['categorie']; ?>">
                                            <?php echo ucfirst($besoin['categorie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($besoin['quantite_restante'], 2, ',', ' '); ?> <?php echo $besoin['unite']; ?></td>
                                    <td><?php echo number_format($besoin['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                                    <td><strong><?php echo number_format($besoin['valeur_restante'], 0, ',', ' '); ?> Ar</strong></td>
                                    <td>
                                        <button onclick="acheter(<?php echo htmlspecialchars(json_encode($besoin)); ?>)" 
                                                class="btn btn-primary btn-small">
                                            🛒 Acheter
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <!-- Modal pour l'achat -->
    <div id="achat-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 15px; max-width: 600px; width: 90%;">
            <h2 style="color: #2d3748; margin-bottom: 20px;">🛒 Confirmer l'Achat</h2>
            
            <form method="POST" action="/achats/enregistrer" id="achat-form">
                <input type="hidden" name="id_ville" id="modal-id-ville">
                <input type="hidden" name="id_type" id="modal-id-type">
                <input type="hidden" name="prix_unitaire" id="modal-prix-unitaire">
                
                <div class="form-group">
                    <label>Ville :</label>
                    <p id="modal-ville-nom" style="font-weight: bold; color: #667eea;"></p>
                </div>
                
                <div class="form-group">
                    <label>Type de Besoin :</label>
                    <p id="modal-type-nom" style="font-weight: bold; color: #667eea;"></p>
                </div>
                
                <div class="form-group">
                    <label>Quantité Disponible :</label>
                    <p id="modal-quantite-max" style="font-weight: bold;"></p>
                </div>
                
                <div class="form-group">
                    <label for="quantite">Quantité à Acheter <span class="required">*</span></label>
                    <input type="number" id="quantite" name="quantite" class="form-control" 
                           step="0.01" min="0.01" required oninput="calculerMontant()">
                </div>
                
                <div class="form-group">
                    <label>Prix Unitaire :</label>
                    <p id="modal-prix-display" style="font-weight: bold;"></p>
                </div>
                
                <div id="calcul-details" style="background: #f7fafc; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Montant Base :</span>
                        <strong id="montant-base">0 Ar</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #ed8936;">
                        <span>Frais d'Achat (<?php echo $frais_pourcentage; ?>%) :</span>
                        <strong id="frais-montant">0 Ar</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px solid #e2e8f0; font-size: 1.2em;">
                        <span>Montant Total :</span>
                        <strong id="montant-total" style="color: #667eea;">0 Ar</strong>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mode">Mode d'Enregistrement <span class="required">*</span></label>
                    <select id="mode" name="mode" class="form-control" required>
                        <option value="simule">Simuler (prévisualisation)</option>
                        <option value="valide">Valider directement</option>
                    </select>
                    <small class="form-text">
                        La simulation permet de prévisualiser avant validation finale.
                    </small>
                </div>
                
                <div class="form-actions-center">
                    <button type="button" onclick="fermerModal()" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-success">✅ Confirmer l'Achat</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const fraisPourcentage = <?php echo $frais_pourcentage; ?>;
        let besoinActuel = null;
        
        function acheter(besoin) {
            besoinActuel = besoin;
            
            document.getElementById('modal-id-ville').value = besoin.id_ville;
            document.getElementById('modal-id-type').value = besoin.id_type;
            document.getElementById('modal-prix-unitaire').value = besoin.prix_unitaire;
            
            document.getElementById('modal-ville-nom').textContent = besoin.nom_ville + ' (' + besoin.region + ')';
            document.getElementById('modal-type-nom').textContent = besoin.nom_type + ' (' + besoin.categorie + ')';
            document.getElementById('modal-quantite-max').textContent = parseFloat(besoin.quantite_restante).toFixed(2) + ' ' + besoin.unite;
            document.getElementById('modal-prix-display').textContent = parseFloat(besoin.prix_unitaire).toLocaleString('fr-FR', {minimumFractionDigits: 0}) + ' Ar';
            
            // Définir la quantité max
            document.getElementById('quantite').max = besoin.quantite_restante;
            document.getElementById('quantite').value = '';
            
            calculerMontant();
            
            document.getElementById('achat-modal').style.display = 'flex';
        }
        
        function fermerModal() {
            document.getElementById('achat-modal').style.display = 'none';
        }
        
        function calculerMontant() {
            const quantite = parseFloat(document.getElementById('quantite').value) || 0;
            const prixUnitaire = parseFloat(document.getElementById('modal-prix-unitaire').value) || 0;
            
            const montantBase = quantite * prixUnitaire;
            const fraisMontant = montantBase * (fraisPourcentage / 100);
            const montantTotal = montantBase + fraisMontant;
            
            document.getElementById('montant-base').textContent = montantBase.toLocaleString('fr-FR', {minimumFractionDigits: 0}) + ' Ar';
            document.getElementById('frais-montant').textContent = fraisMontant.toLocaleString('fr-FR', {minimumFractionDigits: 0}) + ' Ar';
            document.getElementById('montant-total').textContent = montantTotal.toLocaleString('fr-FR', {minimumFractionDigits: 0}) + ' Ar';
        }
        
        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('achat-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModal();
            }
        });
    </script>
</body>
</html>
