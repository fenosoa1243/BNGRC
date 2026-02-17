<?php
$title      = 'Achats';
$subtitle   = 'Acheter des dons avec frais configurable';
$active_nav = 'achats';

ob_start();
?>

<!-- Config frais -->
<div class="alert alert-warning" style="display:flex;justify-content:space-between;align-items:center">
    <div>
        💳 <strong>Frais d'achat :</strong>
        <input type="number" id="frais_achat" value="<?= $frais_achat ?>" step="0.1" min="0" max="100"
               style="width:70px;padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;margin:0 8px">%
        <button onclick="updateFrais()" class="btn btn-warning btn-sm">💾 Mettre à jour</button>
    </div>
    <span style="font-size:13px;color:#92400e">
        Ex: 100 Ar avec <?= $frais_achat ?>% = <?= 100 * (1 + $frais_achat/100) ?> Ar
    </span>
</div>

<!-- Filtre ville -->
<div class="card" style="margin-bottom:20px">
    <div style="display:flex;align-items:center;gap:16px">
        <label style="font-weight:600;font-size:14px">🏙️ Filtrer par ville :</label>
        <select id="ville_filtre" onchange="filtrerVille()" class="form-control" style="max-width:300px">
            <option value="">Toutes les villes</option>
            <?php foreach($villes as $v): ?>
                <option value="<?= $v['id_ville'] ?>" <?= ($ville_selectionnee == $v['id_ville']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['nom_ville']) ?> (<?= htmlspecialchars($v['region']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start">

    <!-- Tableau besoins -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Besoins Restants</span>
        </div>

        <?php if(empty($besoins_restants)): ?>
            <div class="empty-state"><div class="empty-icon">✅</div><p>Tous les besoins sont couverts !</p></div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Ville</th><th>Type</th><th>Catégorie</th>
                        <th>Restant</th><th>Prix Unit.</th><th>Valeur</th><th>Qté à acheter</th></tr>
                </thead>
                <tbody>
                    <?php foreach($besoins_restants as $b):
                        $restant = $b['besoin_total'] - $b['deja_recu'];
                        $valeur  = $restant * $b['prix_unitaire'];
                    ?>
                    <tr data-ville="<?= $b['id_ville'] ?>" data-type="<?= $b['id_type'] ?>"
                        data-prix="<?= $b['prix_unitaire'] ?>" data-max="<?= $restant ?>">
                        <td><strong><?= htmlspecialchars($b['nom_ville']) ?></strong></td>
                        <td><?= htmlspecialchars($b['nom_type']) ?></td>
                        <td><span class="badge badge-<?= $b['categorie'] ?>"><?= ucfirst($b['categorie']) ?></span></td>
                        <td><?= number_format($restant, 2) ?> <?= $b['unite'] ?></td>
                        <td><?= number_format($b['prix_unitaire'], 0) ?> Ar</td>
                        <td><strong><?= number_format($valeur, 0) ?> Ar</strong></td>
                        <td>
                            <input type="number" class="form-control" style="width:100px;padding:6px 10px"
                                   min="0" max="<?= $restant ?>" step="0.01" value="0"
                                   onchange="updatePanier()">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Panier -->
    <div class="card" style="position:sticky;top:20px">
        <div class="card-header"><span class="card-title">🛒 Panier</span></div>
        <div id="panier-items" style="min-height:60px;margin-bottom:16px">
            <p style="color:#94a3b8;font-size:14px">Panier vide</p>
        </div>
        <hr style="border:none;border-top:1px solid #f1f5f9;margin-bottom:16px">
        <div style="font-size:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span>Montant de base :</span>
                <strong id="m-base">0 Ar</strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span>Frais (<?= $frais_achat ?>%) :</span>
                <strong id="m-frais" style="color:#f59e0b">0 Ar</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:2px solid #f1f5f9">
                <strong style="font-size:16px">Total :</strong>
                <strong id="m-total" style="font-size:18px;color:#667eea">0 Ar</strong>
            </div>
        </div>
        <button class="btn btn-success" style="width:100%;margin-top:16px;justify-content:center" onclick="validerAchat()">
            ✅ Valider l'Achat
        </button>
    </div>
</div>

<script>
function filtrerVille() {
    const v = document.getElementById('ville_filtre').value;
    window.location.href = v ? '<?= BASE_URL ?>/achats?ville=' + v : '<?= BASE_URL ?>/achats';
}

function updateFrais() {
    const f = document.getElementById('frais_achat').value;
    fetch('<?= BASE_URL ?>/achats/configurer-frais', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({frais: f})
    }).then(r => r.json()).then(d => {
        if(d.success) { alert('Frais mis à jour: ' + d.frais + '%'); location.reload(); }
    });
}

const FRAIS = <?= $frais_achat ?>;
window.currentAchats = [];

function updatePanier() {
    let base = 0;
    window.currentAchats = [];
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
        const inp = row.querySelector('input[type=number]');
        if(!inp) return;
        const qty = parseFloat(inp.value);
        if(!qty || qty <= 0) return;
        const max  = parseFloat(row.dataset.max);
        const prix = parseFloat(row.dataset.prix);
        if(qty > max) { inp.value = max; return; }
        const mt = qty * prix;
        base += mt;
        window.currentAchats.push({
            id_ville: row.dataset.ville, id_type: row.dataset.type,
            quantite: qty, montant: mt,
            ville: row.children[0].textContent.trim(),
            type: row.children[1].textContent.trim()
        });
    });

    const frais = base * (FRAIS / 100);
    document.getElementById('m-base').textContent  = base.toLocaleString('fr-FR') + ' Ar';
    document.getElementById('m-frais').textContent = frais.toLocaleString('fr-FR') + ' Ar';
    document.getElementById('m-total').textContent = (base + frais).toLocaleString('fr-FR') + ' Ar';

    document.getElementById('panier-items').innerHTML = window.currentAchats.length
        ? window.currentAchats.map(a =>
            `<div style="padding:8px;background:#f8f9fc;border-radius:8px;margin-bottom:8px;font-size:13px">
                <strong>${a.type}</strong><br>
                <span style="color:#64748b">${a.ville}</span><br>
                Qté: ${a.quantite} — <strong>${a.montant.toLocaleString('fr-FR')} Ar</strong>
             </div>`).join('')
        : '<p style="color:#94a3b8;font-size:14px">Panier vide</p>';
}

function validerAchat() {
    if(!window.currentAchats.length) { alert('Panier vide'); return; }
    if(!confirm('Confirmer l\'achat de ' + window.currentAchats.length + ' article(s) ?')) return;
    fetch('<?= BASE_URL ?>/achats/acheter', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({achats_json: JSON.stringify(window.currentAchats)})
    }).then(r => r.json()).then(d => {
        if(d.success) { alert('✅ ' + d.message + '\nTotal: ' + d.total.toLocaleString('fr-FR') + ' Ar'); location.reload(); }
        else alert('❌ ' + d.message);
    });
}
</script>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','content'));
