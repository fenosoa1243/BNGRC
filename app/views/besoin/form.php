<?php
$is_edit    = !empty($besoin_data);
$title      = $is_edit ? 'Modifier Besoin' : 'Nouveau Besoin';
$subtitle   = $is_edit ? 'Modifier les informations du besoin' : 'Enregistrer un nouveau besoin';
$active_nav = 'besoins';
$action_btn = ['url' => BASE_URL . '/besoins', 'label' => '← Retour'];

ob_start();
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= $is_edit ? '✏️ Modifier le Besoin' : '➕ Nouveau Besoin' ?></span>
    </div>

    <div class="form-container">
        <form method="POST" action="<?= BASE_URL ?>/besoins/enregistrer">
            <?php if($is_edit): ?>
                <input type="hidden" name="id_besoin" value="<?= $besoin_data['id_besoin'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">🏙️ Ville *</label>
                <select name="id_ville" required class="form-control">
                    <option value="">-- Sélectionnez une ville --</option>
                    <?php foreach($villes as $v): ?>
                        <option value="<?= $v['id_ville'] ?>" <?= ($is_edit && $besoin_data['id_ville'] == $v['id_ville']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['nom_ville']) ?> (<?= htmlspecialchars($v['region']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">📦 Type de Besoin *</label>
                <select name="id_type" id="id_type" required class="form-control">
                    <option value="">-- Sélectionnez un type --</option>
                    <?php
                    $currentCategorie = null;
                    foreach($types_besoins as $type):
                        if($currentCategorie != $type['categorie']):
                            if($currentCategorie !== null) echo '</optgroup>';
                            $currentCategorie = $type['categorie'];
                            echo '<optgroup label="' . ucfirst($currentCategorie) . '">';
                        endif;
                    ?>
                        <option value="<?= $type['id_type'] ?>"
                                data-unite="<?= $type['unite'] ?>"
                                data-prix="<?= $type['prix_unitaire'] ?>"
                                <?= ($is_edit && $besoin_data['id_type'] == $type['id_type']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['nom_type']) ?> (<?= number_format($type['prix_unitaire'], 0, ',', ' ') ?> Ar/<?= $type['unite'] ?>)
                        </option>
                    <?php endforeach; if($currentCategorie !== null) echo '</optgroup>'; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">🔢 Quantité *</label>
                <input type="number" name="quantite" id="quantite" step="0.01" min="0.01" required
                       class="form-control" placeholder="Ex: 100"
                       value="<?= $is_edit ? $besoin_data['quantite'] : '' ?>">
                <div class="form-text" id="unite-display"></div>
            </div>

            <div class="form-group" id="valeur-group" style="display:none">
                <label class="form-label">💰 Valeur Estimée</label>
                <div class="alert alert-info" style="margin:0">
                    <strong id="valeur-display">0 Ar</strong>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $is_edit ? '💾 Mettre à jour' : '➕ Enregistrer' ?>
                </button>
                <a href="<?= BASE_URL ?>/besoins" class="btn btn-secondary">✖ Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
const typeSelect = document.getElementById('id_type');
const quantiteInput = document.getElementById('quantite');
function updateCalc() {
    const opt = typeSelect.options[typeSelect.selectedIndex];
    const unite = opt.getAttribute('data-unite');
    const prix = parseFloat(opt.getAttribute('data-prix'));
    const qty = parseFloat(quantiteInput.value) || 0;
    if(unite && prix) {
        document.getElementById('unite-display').textContent = 'Unité : ' + unite;
        document.getElementById('valeur-display').textContent = (qty * prix).toLocaleString('fr-FR') + ' Ar';
        document.getElementById('valeur-group').style.display = 'block';
    } else {
        document.getElementById('valeur-group').style.display = 'none';
    }
}
typeSelect.addEventListener('change', updateCalc);
quantiteInput.addEventListener('input', updateCalc);
if(typeSelect.value && quantiteInput.value) updateCalc();
</script>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
