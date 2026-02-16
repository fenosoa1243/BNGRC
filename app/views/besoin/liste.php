<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Besoins - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Liste des Besoins</p>
        </header>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/dashboard">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/villes">🏙️ Villes</a>
            <a href="<?= BASE_URL ?>/besoins" class="active">📋 Besoins</a>
            <a href="<?= BASE_URL ?>/dons">🎁 Dons</a>
            <a href="<?= BASE_URL ?>/distributions">📦 Distributions</a>
            <a href="<?= BASE_URL ?>/achats">🛒 Achats</a>
            <a href="<?= BASE_URL ?>/recap">📈 Récapitulatif</a>
        </nav>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if($_GET['success'] == 'create'): ?>
                    ✅ Le besoin a été enregistré avec succès !
                <?php elseif($_GET['success'] == 'update'): ?>
                    ✅ Le besoin a été modifié avec succès !
                <?php elseif($_GET['success'] == 'delete'): ?>
                    ✅ Le besoin a été supprimé avec succès !
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h2>📋 Liste des Besoins</h2>
                <a href="<?= BASE_URL ?>/besoins/nouveau" class="btn btn-primary">➕ Nouveau Besoin</a>
            </div>

            <?php if(empty($besoins)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Aucun besoin enregistré</strong><br>
                    Commencez par saisir les besoins des villes en cliquant sur le bouton "Nouveau Besoin" ci-dessus.
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
                                <th>Quantité</th>
                                <th>Prix Unitaire</th>
                                <th>Valeur Totale</th>
                                <th>Date de Saisie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($besoins as $besoin): ?>
                                <tr>
                                    <td><?php echo $besoin['id_besoin']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($besoin['nom_ville']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($besoin['region']); ?></td>
                                    <td><?php echo htmlspecialchars($besoin['nom_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $besoin['categorie']; ?>">
                                            <?php echo ucfirst($besoin['categorie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($besoin['quantite'], 2, ',', ' '); ?> <?php echo $besoin['unite']; ?></td>
                                    <td><?php echo number_format($besoin['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                                    <td><strong><?php echo number_format($besoin['valeur_totale'], 0, ',', ' '); ?> Ar</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($besoin['date_saisie'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= BASE_URL ?>/besoins/modifier/<?php echo $besoin['id_besoin']; ?>" 
                                               class="btn btn-info btn-small" 
                                               title="Modifier">
                                                ✏️
                                            </a>
                                            <a href="<?= BASE_URL ?>/besoins/supprimer/<?php echo $besoin['id_besoin']; ?>" 
                                               class="btn btn-danger btn-small" 
                                               title="Supprimer"
                                               onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce besoin ?')">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: right;"><strong>Total :</strong></td>
                                <td colspan="3">
                                    <strong>
                                        <?php 
                                        $total = array_sum(array_column($besoins, 'valeur_totale'));
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
                        <span class="summary-label">Nombre total de besoins :</span>
                        <span class="summary-value"><?php echo count($besoins); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Villes concernées :</span>
                        <span class="summary-value">
                            <?php echo count(array_unique(array_column($besoins, 'id_ville'))); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Types de besoins :</span>
                        <span class="summary-value">
                            <?php echo count(array_unique(array_column($besoins, 'id_type'))); ?>
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