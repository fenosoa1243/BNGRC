<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Achats - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Liste des Achats</p>
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
            <div class="section-header">
                <h2>🛒 Liste des Achats (Frais: <?= $frais_pourcentage ?>%)</h2>
                <a href="<?= BASE_URL ?>/achats/besoins-restants" class="btn btn-primary">➕ Faire un Achat</a>
            </div>

            <!-- Filtre par ville -->
            <div class="filter-section">
                <form method="GET" action="<?= BASE_URL ?>/achats" class="filter-form">
                    <label for="ville">Filtrer par ville :</label>
                    <select name="ville" id="ville" onchange="this.form.submit()" class="form-control">
                        <option value="">Toutes les villes</option>
                        <?php foreach($villes as $ville): ?>
                            <option value="<?= $ville['id_ville'] ?>" <?= ($ville_filtre == $ville['id_ville']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom_ville']) ?> (<?= htmlspecialchars($ville['region']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if(empty($achats)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Aucun achat enregistré</strong><br>
                    Vous pouvez acheter des besoins en nature et matériaux avec l'argent disponible en cliquant sur "Faire un Achat".
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ville</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité</th>
                                <th>Prix Unitaire</th>
                                <th>Montant Base</th>
                                <th>Frais (<?= $frais_pourcentage ?>%)</th>
                                <th>Montant Total</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($achats as $achat): ?>
                                <tr>
                                    <td><?= $achat['id_achat'] ?></td>
                                    <td><strong><?= htmlspecialchars($achat['nom_ville']) ?></strong><br>
                                        <small style="color: #718096;"><?= htmlspecialchars($achat['region']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($achat['nom_type']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $achat['categorie'] ?>">
                                            <?= ucfirst($achat['categorie']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($achat['quantite'], 2, ',', ' ') ?> <?= $achat['unite'] ?></td>
                                    <td><?= number_format($achat['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format($achat['montant_base'], 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format($achat['frais_achat'], 0, ',', ' ') ?> Ar</td>
                                    <td><strong><?= number_format($achat['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                                    <td>
                                        <?php if($achat['statut'] == 'valide'): ?>
                                            <span class="status status-complet">✓ Validé</span>
                                        <?php else: ?>
                                            <span class="status status-partiel">⏳ Simulation</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($achat['date_achat'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right;"><strong>Total :</strong></td>
                                <td><strong><?= number_format(array_sum(array_column($achats, 'montant_base')), 0, ',', ' ') ?> Ar</strong></td>
                                <td><strong><?= number_format(array_sum(array_column($achats, 'frais_achat')), 0, ',', ' ') ?> Ar</strong></td>
                                <td colspan="3"><strong><?= number_format(array_sum(array_column($achats, 'montant_total')), 0, ',', ' ') ?> Ar</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="stats-summary">
                    <div class="summary-item">
                        <span class="summary-label">Nombre d'achats :</span>
                        <span class="summary-value"><?= count($achats) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Achats validés :</span>
                        <span class="summary-value" style="color: #48bb78;">
                            <?= count(array_filter($achats, function($a) { return $a['statut'] == 'valide'; })) ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">En simulation :</span>
                        <span class="summary-value" style="color: #ed8936;">
                            <?= count(array_filter($achats, function($a) { return $a['statut'] == 'simulation'; })) ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Villes concernées :</span>
                        <span class="summary-value">
                            <?= count(array_unique(array_column($achats, 'id_ville'))) ?>
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
