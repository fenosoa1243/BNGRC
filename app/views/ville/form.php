<?php
$is_edit    = !empty($ville_data);
$title      = $is_edit ? 'Modifier Ville' : 'Nouvelle Ville';
$subtitle   = $is_edit ? 'Modifier les informations de la ville' : 'Ajouter une nouvelle ville';
$active_nav = 'villes';
$action_btn = ['url' => BASE_URL . '/villes', 'label' => '← Retour'];

ob_start();
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= $is_edit ? '✏️ Modifier la Ville' : '➕ Nouvelle Ville' ?></span>
    </div>

    <div class="form-container">
        <form method="POST" action="<?= BASE_URL ?>/villes/enregistrer">
            <?php if($is_edit): ?>
                <input type="hidden" name="id_ville" value="<?= $ville_data['id_ville'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">🏙️ Nom de la Ville *</label>
                <input type="text" name="nom_ville" class="form-control" required
                       placeholder="Ex: Antananarivo"
                       value="<?= htmlspecialchars($ville_data['nom_ville'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">📍 Région *</label>
                <input type="text" name="region" class="form-control" required
                       placeholder="Ex: Analamanga"
                       value="<?= htmlspecialchars($ville_data['region'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $is_edit ? '💾 Enregistrer les modifications' : '➕ Créer la ville' ?>
                </button>
                <a href="<?= BASE_URL ?>/villes" class="btn btn-secondary">✖ Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
