<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Distributions - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Historique des Distributions</p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions" class="active">📦 Distributions</a>
            <a href="<?= BASE_URL ?>/achats">🛒 Achats</a>
            <a href="<?= BASE_URL ?>/recap">📈 Récapitulatif</a>
        </nav>

        <?php if(isset($_GET['success']) && $_GET['success'] == 'dispatch'): ?>
            <div class="alert alert-success">
                ✅ Distribution automatique exécutée avec succès ! 
                <?php if(isset($_GET['nb'])): ?>
                    <strong><?php echo $_GET['nb']; ?></strong> distribution(s) effectuée(s).
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h2>📦 Historique des Distributions</h2>
                <a href="<?= BASE_URL ?>/distributions/simuler" class="btn btn-warning">⚡ Simuler Distribution</a>
            </div>

            <?php if(empty($distributions)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Aucune distribution effectuée</strong><br>
                    Les distributions sont créées automatiquement lors de l'exécution du dispatch. Cliquez sur "Simuler Distribution" pour lancer le processus de distribution automatique des dons aux villes selon leurs besoins.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ville</th>
                                <th>Région</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité Distribuée</th>
                                <th>Valeur Distribuée</th>
                                <th>Donateur</th>
                                <th>Date de Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($distributions as $dist): ?>
                                <tr>
                                    <td><?php echo $dist['id_distribution']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($dist['nom_ville']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($dist['region']); ?></td>
                                    <td><?php echo htmlspecialchars($dist['nom_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $dist['categorie']; ?>">
                                            <?php echo ucfirst($dist['categorie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($dist['quantite_distribuee'], 2, ',', ' '); ?> <?php echo $dist['unite']; ?></td>
                                    <td><strong><?php echo number_format($dist['valeur_distribuee'], 0, ',', ' '); ?> Ar</strong></td>
                                    <td>
                                        <?php if(!empty($dist['donateur'])): ?>
                                            <?php echo htmlspecialchars($dist['donateur']); ?>
                                        <?php else: ?>
                                            <em style="color: #718096;">Anonyme</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($dist['date_distribution'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right;"><strong>Total Distribué :</strong></td>
                                <td colspan="3">
                                    <strong>
                                        <?php 
                                        $total = array_sum(array_column($distributions, 'valeur_distribuee'));
                                        echo number_format($total, 0, ',', ' '); 
                                        ?> Ar
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="stats-summary">
                    <div class="summary-item">
                        <span class="summary-label">Nombre total de distributions :</span>
                        <span class="summary-value"><?php echo count($distributions); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Villes bénéficiaires :</span>
                        <span class="summary-value">
                            <?php echo count(array_unique(array_column($distributions, 'id_ville'))); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Types de produits distribués :</span>
                        <span class="summary-value">
                            <?php echo count(array_unique(array_column($distributions, 'nom_type'))); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Valeur totale distribuée :</span>
                        <span class="summary-value" style="color: #667eea;">
                            <?php echo number_format($total, 0, ',', ' '); ?> Ar
                        </span>
                    </div>
                </div>

                <!-- Statistiques par ville -->
                <div class="section-subsection">
                    <h3>📊 Distributions par Ville</h3>
                    <div class="distribution-cards">
                        <?php
                        // Regrouper par ville
                        $par_ville = [];
                        foreach($distributions as $dist) {
                            $ville = $dist['nom_ville'];
                            if(!isset($par_ville[$ville])) {
                                $par_ville[$ville] = [
                                    'region' => $dist['region'],
                                    'nb_distributions' => 0,
                                    'valeur_totale' => 0,
                                    'produits' => []
                                ];
                            }
                            $par_ville[$ville]['nb_distributions']++;
                            $par_ville[$ville]['valeur_totale'] += $dist['valeur_distribuee'];
                            $par_ville[$ville]['produits'][] = $dist['nom_type'];
                        }
                        
                        foreach($par_ville as $ville => $stats):
                        ?>
                            <div class="distribution-card">
                                <h4>🏙️ <?php echo htmlspecialchars($ville); ?></h4>
                                <p class="region-label"><?php echo htmlspecialchars($stats['region']); ?></p>
                                <div class="card-stats">
                                    <div class="card-stat">
                                        <span class="card-stat-label">Distributions :</span>
                                        <span class="card-stat-value"><?php echo $stats['nb_distributions']; ?></span>
                                    </div>
                                    <div class="card-stat">
                                        <span class="card-stat-label">Valeur reçue :</span>
                                        <span class="card-stat-value"><?php echo number_format($stats['valeur_totale'], 0, ',', ' '); ?> Ar</span>
                                    </div>
                                    <div class="card-stat">
                                        <span class="card-stat-label">Produits différents :</span>
                                        <span class="card-stat-value"><?php echo count(array_unique($stats['produits'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>
</body>
</html>