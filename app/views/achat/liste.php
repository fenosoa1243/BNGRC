<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Achats - BNGRC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Liste des Achats</p>
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

        <?php if(isset($_GET['success'])): ?>
            <?php if($_GET['success'] == 'create'): ?>
                <div class="alert alert-success">
                    <strong>✅ Succès :</strong> Achat 
                    <?php echo ($_GET['mode'] ?? 'simule') == 'simule' ? 'simulé' : 'validé'; ?> avec succès !
                </div>
            <?php elseif($_GET['success'] == 'validation'): ?>
                <div class="alert alert-success">
                    <strong>✅ Succès :</strong> <?php echo $_GET['nb'] ?? 0; ?> achat(s) validé(s) avec succès !
                </div>
            <?php elseif($_GET['success'] == 'cancelled'): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Info :</strong> Simulation annulée avec succès.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'no_simulated'): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erreur :</strong> Aucun achat simulé à valider.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h2>🛒 Liste des Achats</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="/achats/nouveau" class="btn btn-primary">➕ Nouvel Achat</a>
                    <a href="/achats/simuler" class="btn btn-info">⚡ Voir Simulation</a>
                </div>
            </div>

            <!-- Filtre par ville -->
            <div class="form-group" style="max-width: 400px; margin-bottom: 20px;">
                <label for="ville-filtre">Filtrer par Ville :</label>
                <select id="ville-filtre" class="form-control" onchange="filtrerParVille()">
                    <option value="">Toutes les villes</option>
                    <?php foreach($villes as $ville): ?>
                        <option value="<?php echo $ville['id_ville']; ?>" 
                                <?php echo ($id_ville_filtre == $ville['id_ville']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ville['nom_ville']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if(empty($achats)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Information :</strong> Aucun achat enregistré pour le moment.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ville</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Quantité</th>
                                <th>Prix Unit.</th>
                                <th>Montant Base</th>
                                <th>Frais (%)</th>
                                <th>Montant Total</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($achats as $achat): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($achat['date_achat'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($achat['nom_ville']); ?></strong>
                                        <br><small style="color: #718096;"><?php echo htmlspecialchars($achat['region']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($achat['nom_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $achat['categorie']; ?>">
                                            <?php echo ucfirst($achat['categorie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($achat['quantite'], 2, ',', ' '); ?> <?php echo $achat['unite']; ?></td>
                                    <td><?php echo number_format($achat['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                                    <td><?php echo number_format($achat['montant_base'], 0, ',', ' '); ?> Ar</td>
                                    <td><?php echo number_format($achat['frais_achat'], 1); ?>%</td>
                                    <td><strong><?php echo number_format($achat['montant_total'], 0, ',', ' '); ?> Ar</strong></td>
                                    <td>
                                        <?php if($achat['statut'] == 'valide'): ?>
                                            <span class="status status-complet">✅ Validé</span>
                                        <?php else: ?>
                                            <span class="status status-partiel">⏳ Simulé</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f7fafc; font-weight: bold;">
                                <td colspan="8" style="text-align: right;">Total :</td>
                                <td>
                                    <?php 
                                    $total = array_sum(array_column($achats, 'montant_total'));
                                    echo number_format($total, 0, ',', ' ') . ' Ar';
                                    ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© 2026 BNGRC - Projet Final S3</p>
    </footer>

    <script>
        function filtrerParVille() {
            const villeId = document.getElementById('ville-filtre').value;
            if (villeId) {
                window.location.href = '/achats?id_ville=' + villeId;
            } else {
                window.location.href = '/achats';
            }
        }
    </script>
</body>
</html>
