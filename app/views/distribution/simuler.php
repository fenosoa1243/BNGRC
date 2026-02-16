<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simuler Distribution - BNGRC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Simuler la Distribution Automatique</p>
        </header>

        <nav class="main-nav">
            <a href="/dashboard">📊 Dashboard</a>
            <a href="/villes">🏙️ Villes</a>
            <a href="/besoins">📋 Besoins</a>
            <a href="/dons">🎁 Dons</a>
            <a href="/distributions" class="active">📦 Distributions</a>
            <a href="/achats">🛒 Achats</a>
            <a href="/recap">📈 Récapitulation</a>
        </nav>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'dispatch'): ?>
            <div class="alert alert-danger">
                <strong>❌ Erreur :</strong> <?php echo htmlspecialchars($_GET['message'] ?? 'Une erreur est survenue lors de la distribution'); ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>⚡ Distribution Automatique des Dons</h2>
            
            <div class="info-box">
                <h3>🤖 Comment fonctionne la distribution automatique ?</h3>
                <p>Le système de distribution automatique (dispatch) suit ces règles :</p>
                <ol>
                    <li><strong>Priorité chronologique</strong> : Les dons les plus anciens sont distribués en premier (FIFO - First In, First Out)</li>
                    <li><strong>Correspondance par type</strong> : Chaque don est distribué uniquement aux villes ayant des besoins du même type</li>
                    <li><strong>Distribution équitable</strong> : Les villes sont servies dans l'ordre de leur demande</li>
                    <li><strong>Optimisation</strong> : Un don peut être divisé entre plusieurs villes selon leurs besoins</li>
                    <li><strong>Transparence</strong> : Toutes les distributions sont enregistrées et traçables</li>
                </ol>
            </div>

            <div class="stats-preview">
                <div class="stat-card">
                    <div class="stat-icon">🎁</div>
                    <div class="stat-content">
                        <div class="stat-label">Dons Disponibles</div>
                        <div class="stat-value" id="dons-disponibles">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Non Satisfaits</div>
                        <div class="stat-value" id="besoins-restants">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-label">Valeur à Distribuer</div>
                        <div class="stat-value" id="valeur-disponible">-</div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <strong>⚠️ Attention :</strong> Cette action va distribuer automatiquement tous les dons disponibles aux villes selon leurs besoins. Cette opération est irréversible.
            </div>

            <form method="POST" action="/distributions/executer" id="dispatch-form">
                <div class="form-actions-center">
                    <a href="/distributions" class="btn btn-secondary">↩️ Annuler</a>
                    <button type="submit" class="btn btn-warning btn-lg" id="execute-btn">
                        ⚡ Exécuter la Distribution
                    </button>
                </div>
            </form>
        </div>

        <div class="section">
            <h3>📊 Aperçu des Dons et Besoins</h3>
            <div id="preview-content">
                <p class="loading">⏳ Chargement des données...</p>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <script>
        // Charger les statistiques
        fetch('/api/dispatch/preview')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('dons-disponibles').textContent = data.stats.nb_dons_disponibles || 0;
                    document.getElementById('besoins-restants').textContent = data.stats.nb_besoins_restants || 0;
                    document.getElementById('valeur-disponible').textContent = 
                        (data.stats.valeur_disponible || 0).toLocaleString('fr-FR', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }) + ' Ar';

                    // Afficher l'aperçu
                    let html = '';
                    if(data.preview && data.preview.length > 0) {
                        html = '<table class="table"><thead><tr><th>Type</th><th>Don Disponible</th><th>Besoin Restant</th><th>Villes Concernées</th></tr></thead><tbody>';
                        data.preview.forEach(item => {
                            html += `<tr>
                                <td>${item.nom_type} (${item.categorie})</td>
                                <td>${parseFloat(item.quantite_disponible).toFixed(2)} ${item.unite}</td>
                                <td>${parseFloat(item.quantite_besoin).toFixed(2)} ${item.unite}</td>
                                <td>${item.nb_villes} ville(s)</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                    } else {
                        html = '<div class="alert alert-info">Aucune distribution possible pour le moment. Vérifiez qu\'il y a des dons disponibles et des besoins non satisfaits.</div>';
                        document.getElementById('execute-btn').disabled = true;
                    }
                    document.getElementById('preview-content').innerHTML = html;
                } else {
                    document.getElementById('preview-content').innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des données</div>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('preview-content').innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des données</div>';
            });

        // Confirmation avant exécution
        document.getElementById('dispatch-form').addEventListener('submit', function(e) {
            if(!confirm('⚠️ Êtes-vous sûr de vouloir exécuter la distribution automatique ?\n\nCette action est irréversible et distribuera tous les dons disponibles selon les besoins.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>