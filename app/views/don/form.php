<?php
$title      = 'Nouveau Don';
$subtitle   = 'Enregistrer un don reçu';
$active_nav = 'dons';
$action_btn = ['url' => BASE_URL . '/dons', 'label' => '← Retour'];

ob_start();
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">🎁 Nouveau Don</span>
    </div>

    <div class="form-container">
        <form method="POST" action="<?= BASE_URL ?>/dons/enregistrer">

            <div class="form-group">
                <label class="form-label">👤 Nom du Donateur</label>
                <input type="text" name="donateur" class="form-control"
                       placeholder="Ex: Croix Rouge, Jean Dupont... (optionnel)" maxlength="100">
                <div class="form-text">Laissez vide pour un don anonyme</div>
            </div>

            <div class="form-group">
                <label class="form-label">📦 Type de Don *</label>
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
                                data-prix="<?= $type['prix_unitaire'] ?>">
                            <?= htmlspecialchars($type['nom_type']) ?> (<?= number_format($type['prix_unitaire'], 0, ',', ' ') ?> Ar/<?= $type['unite'] ?>)
                        </option>
                    <?php endforeach; if($currentCategorie !== null) echo '</optgroup>'; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">🔢 Quantité *</label>
                <input type="number" name="quantite" id="quantite" step="0.01" min="0.01"
                       required class="form-control" placeholder="Ex: 500">
                <div class="form-text" id="unite-display"></div>
            </div>

            <div class="form-group" id="valeur-group" style="display:none">
                <label class="form-label">💰 Valeur Estimée</label>
                <div class="alert alert-info" style="margin:0">
                    <strong id="valeur-display">0 Ar</strong>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">🎁 Enregistrer le Don</button>
                <a href="<?= BASE_URL ?>/dons" class="btn btn-secondary">✖ Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
const sel = document.getElementById('id_type');
const qty = document.getElementById('quantite');
function calc() {
    const opt = sel.options[sel.selectedIndex];
    const u = opt.getAttribute('data-unite');
    const p = parseFloat(opt.getAttribute('data-prix'));
    const q = parseFloat(qty.value) || 0;
    if(u && p) {
        document.getElementById('unite-display').textContent = 'Unité : ' + u;
        document.getElementById('valeur-display').textContent = (q * p).toLocaleString('fr-FR') + ' Ar';
        document.getElementById('valeur-group').style.display = 'block';
    } else {
        document.getElementById('valeur-group').style.display = 'none';
    }
}
sel.addEventListener('change', calc);
qty.addEventListener('input', calc);
</script>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
