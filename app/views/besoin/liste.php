<?php
$title      = 'Besoins';
$subtitle   = 'Gestion des besoins par ville';
$active_nav = 'besoins';
$action_btn = ['url' => BASE_URL . '/besoins/nouveau', 'label' => '➕ Nouveau Besoin'];

ob_start();
?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ <?= ['create'=>'Besoin enregistré','update'=>'Besoin modifié','delete'=>'Besoin supprimé'][$_GET['success']] ?? 'Succès' ?> avec succès !
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Liste des Besoins</span>
        <a href="<?= BASE_URL ?>/besoins/nouveau" class="btn btn-primary btn-sm">➕ Nouveau Besoin</a>
    </div>

    <?php if(empty($besoins)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p>Aucun besoin enregistré. Commencez par ajouter des besoins.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Ville</th><th>Région</th><th>Type</th><th>Catégorie</th>
                    <th>Quantité</th><th>Prix Unitaire</th><th>Valeur Totale</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($besoins as $b): ?>
                <tr>
                    <td><?= $b['id_besoin'] ?></td>
                    <td><strong><?= htmlspecialchars($b['nom_ville']) ?></strong></td>
                    <td><?= htmlspecialchars($b['region']) ?></td>
                    <td><?= htmlspecialchars($b['nom_type']) ?></td>
                    <td><span class="badge badge-<?= $b['categorie'] ?>"><?= ucfirst($b['categorie']) ?></span></td>
                    <td><?= number_format($b['quantite'], 2, ',', ' ') ?> <?= $b['unite'] ?></td>
                    <td><?= number_format($b['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                    <td><strong><?= number_format($b['valeur_totale'], 0, ',', ' ') ?> Ar</strong></td>
                    <td><?= date('d/m/Y', strtotime($b['date_saisie'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?= BASE_URL ?>/besoins/modifier/<?= $b['id_besoin'] ?>" class="btn btn-info btn-sm">✏️</a>
                            <a href="<?= BASE_URL ?>/besoins/supprimer/<?= $b['id_besoin'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer ce besoin ?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right"><strong>Total :</strong></td>
                    <td colspan="3"><strong><?= number_format(array_sum(array_column($besoins,'valeur_totale')), 0, ',', ' ') ?> Ar</strong></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
