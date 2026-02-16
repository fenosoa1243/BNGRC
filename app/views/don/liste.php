<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Dons - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Liste des Dons Reçus</p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons" class="active">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
        </nav>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if($_GET['success'] == 'create'): ?>
                    ✅ Le don a été enregistré avec succès ! Il est maintenant disponible pour la distribution.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h2>🎁 Liste des Dons</h2>
                <a href="<?= BASE_URL ?>/dons/nouveau" class="btn btn-success">➕ Nouveau Don</a>
            </div>

            <?php if(empty($dons)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Aucun don enregistré</strong><br>
                    Enregistrez les dons reçus en cliquant sur le bouton "Nouveau Don" ci-dessus. Ces dons seront ensuite distribués automatiquement aux villes selon leurs besoins.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Donateur</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité Initiale</th>
                                <th>Quantité Restante</th>
                                <th>Distribué</th>
                                <th>Valeur Totale</th>
                                <th>Valeur Restante</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dons as $don): ?>
                                <?php 
                                $quantite_distribuee = $don['quantite'] - $don['quantite_restante'];
                                $pourcentage_distribue = ($don['quantite'] > 0) ? ($quantite_distribuee / $don['quantite']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo $don['id_don']; ?></td>
                                    <td>
                                        <?php if(!empty($don['donateur'])): ?>
                                            <strong><?php echo htmlspecialchars($don['donateur']); ?></strong>
                                        <?php else: ?>
                                            <em style="color: #718096;">Anonyme</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($don['nom_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $don['categorie']; ?>">
                                            <?php echo ucfirst($don['categorie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($don['quantite'], 2, ',', ' '); ?> <?php echo $don['unite']; ?></td>
                                    <td>
                                        <strong style="color: <?php echo $don['quantite_restante'] > 0 ? '#48bb78' : '#718096'; ?>">
                                            <?php echo number_format($don['quantite_restante'], 2, ',', ' '); ?> <?php echo $don['unite']; ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php echo number_format($quantite_distribuee, 2, ',', ' '); ?> <?php echo $don['unite']; ?>
                                        <small style="color: #718096;">(<?php echo number_format($pourcentage_distribue, 0); ?>%)</small>
                                    </td>
                                    <td><?php echo number_format($don['valeur_totale'], 0, ',', ' '); ?> Ar</td>
                                    <td>
                                        <strong style="color: <?php echo $don['quantite_restante'] > 0 ? '#667eea' : '#718096'; ?>">
                                            <?php echo number_format($don['valeur_restante'], 0, ',', ' '); ?> Ar
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if($don['statut'] == 'disponible'): ?>
                                            <span class="status status-disponible">✓ Disponible</span>
                                        <?php elseif($don['statut'] == 'partiel'): ?>
                                            <span class="status status-partiel">⚠ Partiel</span>
                                        <?php else: ?>
                                            <span class="status status-distribue">✓ Distribué</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($don['date_saisie'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: right;"><strong>Total :</strong></td>
                                <td>
                                    <strong>
                                        <?php 
                                        $total = array_sum(array_column($dons, 'valeur_totale'));
                                        echo number_format($total, 0, ',', ' '); 
                                        ?> Ar
                                    </strong>
                                </td>
                                <td>
                                    <strong style="color: #667eea;">
                                        <?php 
                                        $total_restant = array_sum(array_column($dons, 'valeur_restante'));
                                        echo number_format($total_restant, 0, ',', ' '); 
                                        ?> Ar
                                    </strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="stats-summary">
                    <div class="summary-item">
                        <span class="summary-label">Nombre total de dons :</span>
                        <span class="summary-value"><?php echo count($dons); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Dons disponibles :</span>
                        <span class="summary-value" style="color: #48bb78;">
                            <?php echo count(array_filter($dons, function($d) { return $d['quantite_restante'] > 0; })); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Dons entièrement distribués :</span>
                        <span class="summary-value" style="color: #718096;">
                            <?php echo count(array_filter($dons, function($d) { return $d['statut'] == 'distribue'; })); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Valeur restante totale :</span>
                        <span class="summary-value" style="color: #667eea;">
                            <?php echo number_format($total_restant, 0, ',', ' '); ?> Ar
                        </span>
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