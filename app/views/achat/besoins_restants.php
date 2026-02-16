<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achats - Besoins Restants - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Acheter les Besoins Restants avec l'Argent Disponible</p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
            <a href="<?= BASE_URL ?>/achats" class="active">🛒 Achats</a>
            <a href="<?= BASE_URL ?>/recap">📈 Récapitulatif</a>
        </nav>

        <div class="section">
            <h2>💰 Argent Disponible : <span id="argent-disponible" style="color: #667eea;"><?= number_format($argent_disponible, 0, ',', ' ') ?> Ar</span></h2>
            <p style="color: #718096;">Frais d'achat appliqués : <strong><?= $frais_pourcentage ?>%</strong></p>

            <div id="alert-container"></div>

            <div class="simulation-panel">
                <h3>🎭 Simulation d'Achats</h3>
                <div id="simulation-list"></div>
                <div class="form-actions-center" id="simulation-actions" style="display: none;">
                    <button onclick="annulerSimulation()" class="btn btn-secondary">❌ Annuler</button>
                    <button onclick="validerAchats()" class="btn btn-success btn-lg">✅ Valider les Achats</button>
                </div>
            </div>

            <h3>📋 Besoins Restants (Nature et Matériaux)</h3>
            
            <?php if(empty($besoins)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Aucun besoin restant</strong><br>
                    Tous les besoins ont été satisfaits ou il n'y a pas encore de besoins saisis.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité Restante</th>
                                <th>Prix Unitaire</th>
                                <th>Valeur Restante</th>
                                <th>Avec Frais (<?= $frais_pourcentage ?>%)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($besoins as $besoin): ?>
                                <?php 
                                $valeur_avec_frais = $besoin['valeur_restante'] * (1 + $frais_pourcentage / 100);
                                ?>
                                <tr id="besoin-row-<?= $besoin['id_besoin'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($besoin['nom_ville']) ?></strong><br>
                                        <small style="color: #718096;"><?= htmlspecialchars($besoin['region']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($besoin['nom_type']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $besoin['categorie'] ?>">
                                            <?= ucfirst($besoin['categorie']) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= number_format($besoin['quantite_restante'], 2, ',', ' ') ?></strong> <?= $besoin['unite'] ?></td>
                                    <td><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format($besoin['valeur_restante'], 0, ',', ' ') ?> Ar</td>
                                    <td><strong><?= number_format($valeur_avec_frais, 0, ',', ' ') ?> Ar</strong></td>
                                    <td>
                                        <button onclick="acheterBesoin(<?= $besoin['id_besoin'] ?>, <?= $besoin['quantite_restante'] ?>)" 
                                                class="btn btn-primary btn-small" 
                                                id="btn-acheter-<?= $besoin['id_besoin'] ?>">
                                            🛒 Simuler Achat
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

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const FRAIS_POURCENTAGE = <?= $frais_pourcentage ?>;
        let argentDisponible = <?= $argent_disponible ?>;

        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'error' ? 'alert-danger' : (type === 'success' ? 'alert-success' : 'alert-info');
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function acheterBesoin(idBesoin, quantite) {
            fetch(BASE_URL + '/achats/simuler', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_besoin=${idBesoin}&quantite=${quantite}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlert('✅ ' + data.message, 'success');
                    document.getElementById('btn-acheter-' + idBesoin).disabled = true;
                    document.getElementById('btn-acheter-' + idBesoin).textContent = '✓ Simulé';
                    chargerSimulation();
                } else {
                    showAlert('❌ ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('❌ Une erreur est survenue', 'error');
            });
        }

        function chargerSimulation() {
            // Recharger la page pour afficher la simulation
            location.reload();
        }

        function annulerSimulation() {
            if(!confirm('⚠️ Êtes-vous sûr de vouloir annuler la simulation ?')) {
                return;
            }

            fetch(BASE_URL + '/achats/annuler-simulation', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlert('✅ ' + data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('❌ ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('❌ Une erreur est survenue', 'error');
            });
        }

        function validerAchats() {
            if(!confirm('⚠️ Êtes-vous sûr de vouloir valider tous les achats simulés ?\n\nCette action créera des dons correspondants et déduira l\'argent nécessaire.')) {
                return;
            }

            fetch(BASE_URL + '/achats/valider', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlert('✅ ' + data.message, 'success');
                    setTimeout(() => window.location.href = BASE_URL + '/achats', 2000);
                } else {
                    showAlert('❌ ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('❌ Une erreur est survenue', 'error');
            });
        }
    </script>
</body>
</html>
