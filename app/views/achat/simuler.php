<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation des Achats - BNGRC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Simulation des Achats</p>
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

        <?php if(isset($_GET['error']) && $_GET['error'] == 'validation'): ?>
            <div class="alert alert-danger">
                <strong>❌ Erreur :</strong> <?php echo htmlspecialchars($_GET['message'] ?? 'Une erreur est survenue lors de la validation'); ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>⚡ Aperçu de la Simulation</h2>
            
            <div class="info-box">
                <h3>🤖 À propos de la Simulation</h3>
                <p>La simulation vous permet de :</p>
                <ul style="margin-left: 20px; color: #4a5568;">
                    <li>Prévisualiser vos achats avant de les valider</li>
                    <li>Vérifier les montants et les disponibilités</li>
                    <li>Ajuster vos achats si nécessaire</li>
                    <li>Valider tous les achats simulés en une seule fois</li>
                </ul>
            </div>

            <div class="stats-preview">
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-content">
                        <div class="stat-label">Achats Simulés</div>
                        <div class="stat-value"><?php echo count($achats_simules); ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-label">Montant Total</div>
                        <div class="stat-value"><?php echo number_format($total_montant, 0, ',', ' '); ?> Ar</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💵</div>
                    <div class="stat-content">
                        <div class="stat-label">Argent Disponible</div>
                        <div class="stat-value"><?php echo number_format($argent_disponible, 0, ',', ' '); ?> Ar</div>
                    </div>
                </div>
            </div>

            <?php if(empty($achats_simules)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Information :</strong> Aucun achat simulé pour le moment. 
                    Commencez par créer des achats en mode simulation.
                </div>
                <div class="form-actions-center">
                    <a href="/achats" class="btn btn-secondary">↩️ Retour à la liste</a>
                    <a href="/achats/nouveau" class="btn btn-primary">➕ Nouvel Achat</a>
                </div>
            <?php else: ?>
                <?php if($total_montant > $argent_disponible): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Attention :</strong> Le montant total des achats simulés (<?php echo number_format($total_montant, 0, ',', ' '); ?> Ar) 
                        dépasse les dons en argent disponibles (<?php echo number_format($argent_disponible, 0, ',', ' '); ?> Ar). 
                        Vous devez annuler certains achats ou attendre de nouveaux dons en argent.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <strong>✅ OK :</strong> Les fonds sont suffisants pour valider tous les achats simulés.
                    </div>
                <?php endif; ?>

                <h3>📋 Détail des Achats Simulés</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date Simulation</th>
                                <th>Ville</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Montant Base</th>
                                <th>Frais</th>
                                <th>Montant Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($achats_simules as $achat): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($achat['date_achat'])); ?></td>
                                    <td><?php echo htmlspecialchars($achat['nom_ville'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($achat['nom_type'] ?? 'N/A'); ?></td>
                                    <td><?php echo number_format($achat['quantite'], 2, ',', ' '); ?></td>
                                    <td><?php echo number_format($achat['quantite'] * $achat['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                                    <td><?php echo number_format($achat['frais_achat'], 1); ?>%</td>
                                    <td><strong><?php echo number_format($achat['montant_total'], 0, ',', ' '); ?> Ar</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f7fafc; font-weight: bold;">
                                <td colspan="6" style="text-align: right;">Total :</td>
                                <td><?php echo number_format($total_montant, 0, ',', ' '); ?> Ar</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="form-actions-center" style="margin-top: 30px;">
                    <form method="POST" action="/achats/annuler" style="display: inline;">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler la simulation ?')">
                            ❌ Annuler la Simulation
                        </button>
                    </form>
                    
                    <a href="/achats/nouveau" class="btn btn-info">➕ Ajouter un Achat</a>
                    
                    <form method="POST" action="/achats/valider" style="display: inline;" id="validation-form">
                        <button type="submit" class="btn btn-success btn-lg" 
                                <?php echo ($total_montant > $argent_disponible) ? 'disabled' : ''; ?>>
                            ✅ Valider Tous les Achats
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <script>
        document.getElementById('validation-form')?.addEventListener('submit', function(e) {
            if(!confirm('⚠️ Êtes-vous sûr de vouloir valider tous les achats simulés ?\n\n' +
                        'Cette action va :\n' +
                        '- Déduire <?php echo number_format($total_montant, 0, ',', ' '); ?> Ar des dons en argent\n' +
                        '- Créer <?php echo count($achats_simules); ?> don(s) et distribution(s)\n' +
                        '- Cette action est irréversible')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
