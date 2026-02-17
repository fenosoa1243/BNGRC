<?php
$title      = 'Dons';
$subtitle   = 'Gestion des dons reçus';
$active_nav = 'dons';
$action_btn = ['url' => BASE_URL . '/dons/nouveau', 'label' => '➕ Nouveau Don'];

ob_start();
?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ Don enregistré avec succès !</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">🎁 Liste des Dons</span>
        <a href="<?= BASE_URL ?>/dons/nouveau" class="btn btn-success btn-sm">➕ Nouveau Don</a>
    </div>

    <?php if(empty($dons)): ?>
        <div class="empty-state">
            <div class="empty-icon">🎁</div>
            <p>Aucun don enregistré.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Donateur</th><th>Type</th><th>Catégorie</th>
                    <th>Qté Initiale</th><th>Qté Restante</th><th>Distribué</th>
                    <th>Valeur Totale</th><th>Valeur Restante</th><th>Statut</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dons as $don):
                    $dist = $don['quantite'] - $don['quantite_restante'];
                    $pct  = $don['quantite'] > 0 ? ($dist / $don['quantite']) * 100 : 0;
                ?>
                <tr>
                    <td><?= $don['id_don'] ?></td>
                    <td><?= !empty($don['donateur']) ? '<strong>'.htmlspecialchars($don['donateur']).'</strong>' : '<em style="color:#94a3b8">Anonyme</em>' ?></td>
                    <td><?= htmlspecialchars($don['nom_type']) ?></td>
                    <td><span class="badge badge-<?= $don['categorie'] ?>"><?= ucfirst($don['categorie']) ?></span></td>
                    <td><?= number_format($don['quantite'], 2, ',', ' ') ?> <?= $don['unite'] ?></td>
                    <td><strong style="color:<?= $don['quantite_restante'] > 0 ? '#10b981' : '#94a3b8' ?>">
                        <?= number_format($don['quantite_restante'], 2, ',', ' ') ?> <?= $don['unite'] ?></strong></td>
                    <td><?= number_format($dist, 2, ',', ' ') ?> <small style="color:#94a3b8">(<?= number_format($pct, 0) ?>%)</small></td>
                    <td><?= number_format($don['valeur_totale'], 0, ',', ' ') ?> Ar</td>
                    <td><strong style="color:<?= $don['quantite_restante'] > 0 ? '#667eea' : '#94a3b8' ?>">
                        <?= number_format($don['valeur_restante'], 0, ',', ' ') ?> Ar</strong></td>
                    <td>
                        <?php if($don['statut'] == 'disponible'): ?>
                            <span class="status status-complet">✓ Disponible</span>
                        <?php elseif($don['statut'] == 'partiel'): ?>
                            <span class="status status-partiel">⚡ Partiel</span>
                        <?php else: ?>
                            <span class="badge badge-success">✓ Distribué</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($don['date_saisie'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right"><strong>Total :</strong></td>
                    <td><strong><?= number_format(array_sum(array_column($dons,'valeur_totale')), 0, ',', ' ') ?> Ar</strong></td>
                    <td><strong style="color:#667eea"><?= number_format(array_sum(array_column($dons,'valeur_restante')), 0, ',', ' ') ?> Ar</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
