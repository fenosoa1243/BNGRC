<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - BNGRC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Tableau de Bord des Collectes et Distributions</p>
        </header>

        <nav class="main-nav">
            <a href="/dashboard" class="active">📊 Dashboard</a>
            <a href="/villes">🏙️ Villes</a>
            <a href="/besoins">📋 Besoins</a>
            <a href="/dons">🎁 Dons</a>
            <a href="/distributions">📦 Distributions</a>
            <a href="/achats">🛒 Achats</a>
            <a href="/recap">📈 Récapitulation</a>
        </nav>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">🏙️</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['nb_villes']; ?></div>
                    <div class="stat-label">Villes</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['valeur_besoins'], 0, ',', ' '); ?> Ar</div>
                    <div class="stat-label">Valeur Besoins</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🎁</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['valeur_dons'], 0, ',', ' '); ?> Ar</div>
                    <div class="stat-label">Valeur Dons</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['valeur_distribuee'], 0, ',', ' '); ?> Ar</div>
                    <div class="stat-label">Valeur Distribuée</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['taux_couverture'], 1); ?>%</div>
                    <div class="stat-label">Taux de Couverture</div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="/besoins/nouveau" class="btn btn-primary">➕ Saisir un Besoin</a>
            <a href="/dons/nouveau" class="btn btn-success">🎁 Enregistrer un Don</a>
            <a href="/achats/nouveau" class="btn btn-info">🛒 Nouvel Achat</a>
            <a href="/distributions/simuler" class="btn btn-warning">⚡ Simuler Dispatch</a>
            <a href="/recap" class="btn btn-secondary">📈 Récapitulation</a>
        </div>

        <div class="section">
            <h2>📊 Besoins et Dons par Ville</h2>
            
            <?php if(empty($villes_data)): ?>
                <div class="alert alert-info">Aucune donnée disponible. Commencez par saisir des besoins et des dons.</div>
            <?php else: ?>
                <?php foreach($villes_data as $id_ville => $ville): ?>
                    <div class="ville-card">
                        <h3>🏙️ <?php echo htmlspecialchars($ville['nom_ville']); ?> 
                            <span class="region">(<?php echo htmlspecialchars($ville['region']); ?>)</span>
                        </h3>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Catégorie</th>
                                    <th>Besoin</th>
                                    <th>Don Reçu</th>
                                    <th>Reste</th>
                                    <th>Valeur Besoin</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ville['besoins'] as $besoin): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($besoin['nom_type']); ?></td>
                                        <td><span class="badge badge-<?php echo $besoin['categorie']; ?>"><?php echo ucfirst($besoin['categorie']); ?></span></td>
                                        <td><?php echo number_format($besoin['besoin_total'], 2); ?> <?php echo $besoin['unite']; ?></td>
                                        <td><?php echo number_format($besoin['don_recu'], 2); ?> <?php echo $besoin['unite']; ?></td>
                                        <td><strong><?php echo number_format($besoin['besoin_restant'], 2); ?></strong> <?php echo $besoin['unite']; ?></td>
                                        <td><?php echo number_format($besoin['valeur_besoin'], 0, ',', ' '); ?> Ar</td>
                                        <td>
                                            <?php 
                                            if($besoin['besoin_restant'] <= 0) {
                                                echo '<span class="status status-complet">✓ Complet</span>';
                                            } elseif($besoin['don_recu'] > 0) {
                                                $pourcent = ($besoin['don_recu'] / $besoin['besoin_total']) * 100;
                                                echo '<span class="status status-partiel">' . number_format($pourcent, 0) . '%</span>';
                                            } else {
                                                echo '<span class="status status-vide">⚠ Vide</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>
</body>
</html>