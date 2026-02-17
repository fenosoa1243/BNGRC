<?php
$title      = 'Villes';
$subtitle   = 'Gestion des villes';
$active_nav = 'villes';
$action_btn = ['url' => BASE_URL . '/villes/nouveau', 'label' => '➕ Nouvelle Ville'];

ob_start();
?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ <?= ['create'=>'Ville créée','update'=>'Ville modifiée','delete'=>'Ville supprimée'][$_GET['success']] ?? 'Succès' ?> avec succès !
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏙️ Villes enregistrées</span>
        <a href="<?= BASE_URL ?>/villes/nouveau" class="btn btn-primary btn-sm">➕ Nouvelle Ville</a>
    </div>

    <?php if(empty($villes)): ?>
        <div class="empty-state"><div class="empty-icon">🏙️</div><p>Aucune ville enregistrée.</p></div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Nom de la Ville</th><th>Région</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($villes as $v): ?>
                <tr>
                    <td><?= $v['id_ville'] ?></td>
                    <td><strong><?= htmlspecialchars($v['nom_ville']) ?></strong></td>
                    <td><?= htmlspecialchars($v['region']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?= BASE_URL ?>/villes/modifier/<?= $v['id_ville'] ?>" class="btn btn-info btn-sm">✏️ Modifier</a>
                            <a href="<?= BASE_URL ?>/villes/supprimer/<?= $v['id_ville'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer cette ville ?')">🗑️ Supprimer</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
