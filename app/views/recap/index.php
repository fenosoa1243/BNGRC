<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulation - BNGRC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Récapitulation des Besoins et Dons</p>
        </header>

        <nav class="main-nav">
            <a href="/dashboard">📊 Dashboard</a>
            <a href="/villes">🏙️ Villes</a>
            <a href="/besoins">📋 Besoins</a>
            <a href="/dons">🎁 Dons</a>
            <a href="/distributions">📦 Distributions</a>
            <a href="/achats">🛒 Achats</a>
            <a href="/recap" class="active">📈 Récapitulation</a>
        </nav>

        <div class="section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h2>📈 Vue d'Ensemble</h2>
                <button onclick="actualiserDonnees()" class="btn btn-info" id="refresh-btn">
                    🔄 Actualiser
                </button>
            </div>

            <div id="loading-indicator" style="display: none; text-align: center; padding: 40px;">
                <p class="loading">⏳ Chargement des données...</p>
            </div>

            <!-- Statistiques Principales -->
            <div id="stats-principales" class="stats-preview" style="display: none;">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Totaux</div>
                        <div class="stat-value" id="besoins-totaux">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Satisfaits</div>
                        <div class="stat-value" id="besoins-satisfaits">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-label">Besoins Restants</div>
                        <div class="stat-value" id="besoins-restants">-</div>
                    </div>
                </div>
            </div>

            <!-- Barre de progression -->
            <div id="progression-container" style="display: none; margin: 30px 0;">
                <div style="background: #f7fafc; padding: 20px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-weight: bold; color: #2d3748;">Taux de Satisfaction</span>
                        <span style="font-weight: bold; color: #667eea;" id="pourcentage-satisfait">0%</span>
                    </div>
                    <div style="background: #e2e8f0; border-radius: 20px; height: 30px; overflow: hidden;">
                        <div id="barre-progression" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 100%; width: 0%; transition: width 0.5s ease;">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.9em; color: #718096;">
                        <span><strong id="nb-besoins">0</strong> besoins</span>
                        <span><strong id="nb-distributions">0</strong> distributions</span>
                    </div>
                </div>
            </div>

            <!-- Statistiques par Ville -->
            <div id="stats-ville-section" style="display: none; margin-top: 40px;">
                <h3>🏙️ Récapitulation par Ville</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Région</th>
                                <th>Besoins (Ar)</th>
                                <th>Satisfaits (Ar)</th>
                                <th>Restants (Ar)</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody id="stats-ville-body">
                            <!-- Rempli dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistiques par Type -->
            <div id="stats-type-section" style="display: none; margin-top: 40px;">
                <h3>📦 Récapitulation par Type de Besoin</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité Besoin</th>
                                <th>Quantité Satisfait</th>
                                <th>Quantité Restant</th>
                                <th>Valeur Besoin</th>
                                <th>Valeur Satisfait</th>
                            </tr>
                        </thead>
                        <tbody id="stats-type-body">
                            <!-- Rempli dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="error-message" style="display: none;">
                <div class="alert alert-danger">
                    <strong>❌ Erreur :</strong> Impossible de charger les données. 
                    <button onclick="actualiserDonnees()" class="btn btn-small btn-danger" style="margin-left: 10px;">
                        Réessayer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <script>
        let isLoading = false;

        function actualiserDonnees() {
            if (isLoading) return;
            
            isLoading = true;
            document.getElementById('refresh-btn').disabled = true;
            document.getElementById('refresh-btn').textContent = '⏳ Chargement...';
            
            // Afficher l'indicateur de chargement
            document.getElementById('loading-indicator').style.display = 'block';
            document.getElementById('error-message').style.display = 'none';
            
            fetch('/api/recap/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        afficherStatistiques(data);
                    } else {
                        afficherErreur();
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    afficherErreur();
                })
                .finally(() => {
                    isLoading = false;
                    document.getElementById('loading-indicator').style.display = 'none';
                    document.getElementById('refresh-btn').disabled = false;
                    document.getElementById('refresh-btn').textContent = '🔄 Actualiser';
                });
        }

        function afficherStatistiques(data) {
            const stats = data.stats;
            
            // Afficher les stats principales
            document.getElementById('besoins-totaux').textContent = 
                formatMontant(stats.besoins_totaux_montant) + ' Ar';
            document.getElementById('besoins-satisfaits').textContent = 
                formatMontant(stats.besoins_satisfaits_montant) + ' Ar';
            document.getElementById('besoins-restants').textContent = 
                formatMontant(stats.besoins_restants_montant) + ' Ar';
            
            document.getElementById('stats-principales').style.display = 'grid';
            
            // Afficher la progression
            document.getElementById('pourcentage-satisfait').textContent = 
                stats.pourcentage_satisfait + '%';
            document.getElementById('barre-progression').style.width = 
                stats.pourcentage_satisfait + '%';
            document.getElementById('nb-besoins').textContent = stats.nombre_besoins;
            document.getElementById('nb-distributions').textContent = stats.nombre_distributions;
            
            document.getElementById('progression-container').style.display = 'block';
            
            // Afficher les stats par ville
            if (data.stats_par_ville && data.stats_par_ville.length > 0) {
                const tbody = document.getElementById('stats-ville-body');
                tbody.innerHTML = '';
                
                data.stats_par_ville.forEach(ville => {
                    const taux = ville.besoin_ville > 0 
                        ? Math.round((ville.satisfait_ville / ville.besoin_ville) * 100) 
                        : 0;
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${htmlEscape(ville.nom_ville)}</strong></td>
                        <td>${htmlEscape(ville.region)}</td>
                        <td>${formatMontant(ville.besoin_ville)} Ar</td>
                        <td><span style="color: #48bb78;">${formatMontant(ville.satisfait_ville)} Ar</span></td>
                        <td><span style="color: #ed8936;">${formatMontant(ville.restant_ville)} Ar</span></td>
                        <td>
                            <span class="badge ${taux >= 75 ? 'badge-nature' : (taux >= 25 ? 'badge-argent' : 'badge-materiau')}">
                                ${taux}%
                            </span>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                document.getElementById('stats-ville-section').style.display = 'block';
            }
            
            // Afficher les stats par type
            if (data.stats_par_type && data.stats_par_type.length > 0) {
                const tbody = document.getElementById('stats-type-body');
                tbody.innerHTML = '';
                
                data.stats_par_type.forEach(type => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${htmlEscape(type.nom_type)}</td>
                        <td>
                            <span class="badge badge-${type.categorie}">
                                ${type.categorie.charAt(0).toUpperCase() + type.categorie.slice(1)}
                            </span>
                        </td>
                        <td>${formatQuantite(type.quantite_besoin)} ${htmlEscape(type.unite)}</td>
                        <td><span style="color: #48bb78;">${formatQuantite(type.quantite_satisfait)} ${htmlEscape(type.unite)}</span></td>
                        <td><span style="color: #ed8936;">${formatQuantite(type.quantite_restante)} ${htmlEscape(type.unite)}</span></td>
                        <td>${formatMontant(type.valeur_besoin)} Ar</td>
                        <td>${formatMontant(type.valeur_satisfait)} Ar</td>
                    `;
                    tbody.appendChild(tr);
                });
                
                document.getElementById('stats-type-section').style.display = 'block';
            }
        }

        function afficherErreur() {
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('stats-principales').style.display = 'none';
            document.getElementById('progression-container').style.display = 'none';
            document.getElementById('stats-ville-section').style.display = 'none';
            document.getElementById('stats-type-section').style.display = 'none';
        }

        function formatMontant(montant) {
            return parseFloat(montant || 0).toLocaleString('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function formatQuantite(quantite) {
            return parseFloat(quantite || 0).toLocaleString('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function htmlEscape(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Charger les données au chargement de la page
        window.addEventListener('DOMContentLoaded', () => {
            actualiserDonnees();
        });

        // Auto-refresh toutes les 30 secondes (optionnel)
        // setInterval(actualiserDonnees, 30000);
    </script>
</body>
</html>
