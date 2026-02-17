<?php
$title      = 'Distributions';
$subtitle   = 'Historique des distributions effectuées';
$active_nav = 'distributions';
$action_btn = ['url' => BASE_URL . '/distributions/simuler', 'label' => '⚡ Simuler Dispatch'];

ob_start();
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📦 Distributions Effectuées</span>
        <a href="<?= BASE_URL ?>/distributions/simuler" class="btn btn-warning btn-sm">⚡ Simuler Dispatch</a>
    </div>

    <?php if(empty($distributions)): ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <p>Aucune distribution effectuée. Lancez une simulation pour commencer.</p>
            <a href="<?= BASE_URL ?>/distributions/simuler" class="btn btn-warning" style="margin-top:16px">⚡ Simuler Dispatch</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Ville</th><th>Région</th><th>Type</th><th>Catégorie</th>
                    <th>Donateur</th><th>Qté Distribuée</th><th>Valeur</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach($distributions as $d): ?>
                <tr>
                    <td><?= $d['id_distribution'] ?></td>
                    <td><strong><?= htmlspecialchars($d['nom_ville']) ?></strong></td>
                    <td><?= htmlspecialchars($d['region']) ?></td>
                    <td><?= htmlspecialchars($d['nom_type']) ?></td>
                    <td><span class="badge badge-<?= $d['categorie'] ?>"><?= ucfirst($d['categorie']) ?></span></td>
                    <td><?= htmlspecialchars($d['donateur'] ?? 'Anonyme') ?></td>
                    <td><strong><?= number_format($d['quantite_distribuee'], 2, ',', ' ') ?> <?= $d['unite'] ?></strong></td>
                    <td><strong><?= number_format($d['valeur_distribuee'], 0, ',', ' ') ?> Ar</strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($d['date_distribution'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right"><strong>Total distribué :</strong></td>
                    <td><strong><?= number_format(array_sum(array_column($distributions,'valeur_distribuee')), 0, ',', ' ') ?> Ar</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
