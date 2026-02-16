<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Récapitulatif Global</p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
            <a href="<?= BASE_URL ?>/achats">🛒 Achats</a>
            <a href="<?= BASE_URL ?>/recap" class="active">📈 Récapitulatif</a>
        </nav>

        <div class="section">
            <div class="section-header">
                <h2>📈 Récapitulatif Global des Besoins et Dons</h2>
                <button onclick="actualiserDonnees()" class="btn btn-primary" id="btn-actualiser">
                    🔄 Actualiser
                </button>
            </div>

            <div id="loading" class="loading" style="display: none;">
                ⏳ Chargement des données...
            </div>

            <!-- Statistiques globales -->
            <div class="stats-preview" id="stats-globales">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Totaux</div>
                        <div class="stat-value" id="besoins-total">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Satisfaits</div>
                        <div class="stat-value" id="besoins-satisfaits" style="color: #48bb78;">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Restants</div>
                        <div class="stat-value" id="besoins-restants" style="color: #f56565;">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-label">Taux de Satisfaction</div>
                        <div class="stat-value" id="taux-satisfaction">-</div>
                    </div>
                </div>
            </div>

            <!-- Détails par source de satisfaction -->
            <div class="info-box">
                <h3>📦 Détails par Source de Satisfaction</h3>
                <div class="stats-summary">
                    <div class="summary-item">
                        <span class="summary-label">Montant Distribué (Dons directs)</span>
                        <span class="summary-value" id="montant-distribue" style="color: #667eea;">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Montant Acheté (via argent)</span>
                        <span class="summary-value" id="montant-achete" style="color: #48bb78;">-</span>
                    </div>
                </div>
            </div>

            <!-- Détails par catégorie -->
            <div class="section-subsection">
                <h3>📂 Récapitulatif par Catégorie</h3>
                <div class="table-responsive">
                    <table class="table" id="table-categories">
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th>Besoin Total</th>
                                <th>Montant Satisfait</th>
                                <th>Montant Restant</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="loading">⏳ Chargement...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Détails par ville -->
            <div class="section-subsection">
                <h3>🏙️ Récapitulatif par Ville</h3>
                <div class="table-responsive">
                    <table class="table" id="table-villes">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Région</th>
                                <th>Besoin Total</th>
                                <th>Montant Satisfait</th>
                                <th>Montant Restant</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="loading">⏳ Chargement...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';

        function formatMontant(montant) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(montant) + ' Ar';
        }

        function actualiserDonnees() {
            const btnActualiser = document.getElementById('btn-actualiser');
            btnActualiser.disabled = true;
            btnActualiser.textContent = '⏳ Actualisation...';

            fetch(BASE_URL + '/recap/data')
                .then(response => response.json())
                .then(result => {
                    if(result.success) {
                        const data = result.data;

                        // Statistiques globales
                        document.getElementById('besoins-total').textContent = formatMontant(data.besoins_total);
                        document.getElementById('besoins-satisfaits').textContent = formatMontant(data.besoins_satisfaits);
                        document.getElementById('besoins-restants').textContent = formatMontant(data.besoins_restants);
                        document.getElementById('taux-satisfaction').textContent = 
                            data.pourcentage_satisfaction.toFixed(1) + '%';

                        document.getElementById('montant-distribue').textContent = formatMontant(data.montant_distribue);
                        document.getElementById('montant-achete').textContent = formatMontant(data.montant_achete);

                        // Tableau par catégories
                        const tbodyCategories = document.querySelector('#table-categories tbody');
                        if(data.details_categories && data.details_categories.length > 0) {
                            let htmlCategories = '';
                            data.details_categories.forEach(cat => {
                                if(cat.besoin_total > 0) {
                                    htmlCategories += `
                                        <tr>
                                            <td><span class="badge badge-${cat.categorie}">${cat.categorie.charAt(0).toUpperCase() + cat.categorie.slice(1)}</span></td>
                                            <td>${formatMontant(cat.besoin_total)}</td>
                                            <td style="color: #48bb78;"><strong>${formatMontant(cat.montant_satisfait)}</strong></td>
                                            <td style="color: #f56565;"><strong>${formatMontant(cat.montant_restant)}</strong></td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: ${cat.pourcentage}%;"></div>
                                                    <span class="progress-text">${cat.pourcentage.toFixed(0)}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                }
                            });
                            tbodyCategories.innerHTML = htmlCategories || '<tr><td colspan="5">Aucune donnée</td></tr>';
                        } else {
                            tbodyCategories.innerHTML = '<tr><td colspan="5">Aucune donnée</td></tr>';
                        }

                        // Tableau par villes
                        const tbodyVilles = document.querySelector('#table-villes tbody');
                        if(data.details_villes && data.details_villes.length > 0) {
                            let htmlVilles = '';
                            data.details_villes.forEach(ville => {
                                htmlVilles += `
                                    <tr>
                                        <td><strong>${ville.nom_ville}</strong></td>
                                        <td>${ville.region}</td>
                                        <td>${formatMontant(ville.besoin_total)}</td>
                                        <td style="color: #48bb78;"><strong>${formatMontant(ville.montant_satisfait)}</strong></td>
                                        <td style="color: #f56565;"><strong>${formatMontant(ville.montant_restant)}</strong></td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: ${ville.pourcentage}%;"></div>
                                                <span class="progress-text">${ville.pourcentage.toFixed(0)}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            tbodyVilles.innerHTML = htmlVilles;
                        } else {
                            tbodyVilles.innerHTML = '<tr><td colspan="6">Aucune donnée</td></tr>';
                        }

                        btnActualiser.disabled = false;
                        btnActualiser.textContent = '🔄 Actualiser';
                    } else {
                        alert('Erreur: ' + result.error);
                        btnActualiser.disabled = false;
                        btnActualiser.textContent = '🔄 Actualiser';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des données');
                    btnActualiser.disabled = false;
                    btnActualiser.textContent = '🔄 Actualiser';
                });
        }

        // Charger les données au chargement de la page
        document.addEventListener('DOMContentLoaded', actualiserDonnees);
    </script>
</body>
</html>
