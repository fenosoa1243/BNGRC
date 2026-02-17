<?php
$title      = 'Simulation Distribution';
$subtitle   = 'Simuler et valider le dispatch des dons';
$active_nav = 'distributions';
$action_btn = ['url' => BASE_URL . '/distributions', 'label' => '← Historique'];

ob_start();
?>

<div class="alert alert-info">
    ℹ️ Cliquez sur <strong>Simuler</strong> pour prévisualiser les distributions, puis sur <strong>Valider</strong> pour les enregistrer.
</div>

<div class="card" style="margin-bottom:24px">
    <div class="card-header">
        <span class="card-title">⚡ Dispatch des Dons</span>
        <div style="display:flex;gap:12px">
            <button class="btn btn-info" onclick="simuler()">🔍 Simuler</button>
            <button class="btn btn-success" id="btn-valider" onclick="valider()" disabled>✅ Valider et Enregistrer</button>
        </div>
    </div>
    <p style="color:#64748b;font-size:14px">L'algorithme distribue les dons disponibles aux villes selon leurs besoins restants, en respectant l'ordre chronologique.</p>
</div>

<div id="resultat" style="display:none">
    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stat-card">
            <div class="stat-header">
                <div><div class="stat-label">Distributions</div><div class="stat-value" id="nb-dist">0</div></div>
                <div class="stat-icon blue">📦</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div><div class="stat-label">Dons Traités</div><div class="stat-value" id="nb-dons">0</div></div>
                <div class="stat-icon green">🎁</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div><div class="stat-label">Valeur Totale</div><div class="stat-value" id="val-total">0 Ar</div></div>
                <div class="stat-icon orange">💰</div>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <!-- Distributions prévues -->
        <div class="card">
            <div class="card-header"><span class="card-title">📋 Distributions Prévues</span></div>
            <div id="liste-dist"></div>
        </div>
        <!-- Dons traités -->
        <div class="card">
            <div class="card-header"><span class="card-title">🎁 Dons Traités</span></div>
            <div id="liste-dons"></div>
        </div>
    </div>
</div>

<script>
let simData = null;

function simuler() {
    document.getElementById('btn-valider').disabled = true;
    document.getElementById('resultat').style.display = 'none';

    fetch('<?= BASE_URL ?>/distributions/preview', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if(!data.success) { alert('❌ ' + data.message); return; }
        simData = data;

        document.getElementById('nb-dist').textContent  = data.nb_distributions;
        document.getElementById('nb-dons').textContent  = data.dons_traites.length;
        const val = data.distributions.reduce((s,d) => s + (d.valeur||0), 0);
        document.getElementById('val-total').textContent = val.toLocaleString('fr-FR') + ' Ar';

        document.getElementById('liste-dist').innerHTML = data.distributions.map(d => `
            <div style="padding:12px 0;border-bottom:1px solid #f1f5f9">
                <div style="font-weight:700;color:#1e293b">🏙️ ${d.nom_ville}</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px">
                    ${d.type_besoin} — <strong>${d.quantite_distribuee} ${d.unite}</strong>
                    — <span style="color:#667eea;font-weight:600">${(d.valeur||0).toLocaleString('fr-FR')} Ar</span>
                </div>
            </div>`).join('') || '<p style="color:#94a3b8">Aucune distribution</p>';

        document.getElementById('liste-dons').innerHTML = data.dons_traites.map(d => `
            <div style="padding:12px 0;border-bottom:1px solid #f1f5f9">
                <div style="font-weight:700">🎁 Don #${d.id_don} — ${d.type}</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px">
                    ${d.quantite_initiale} → distribué: <strong>${d.quantite_distribuee}</strong> → restant: ${d.quantite_restante}
                </div>
                <div class="progress" style="margin-top:8px">
                    <div class="progress-bar" style="width:${d.quantite_initiale>0?(d.quantite_distribuee/d.quantite_initiale*100):0}%"></div>
                </div>
            </div>`).join('') || '<p style="color:#94a3b8">Aucun don traité</p>';

        document.getElementById('resultat').style.display = 'block';
        document.getElementById('btn-valider').disabled = false;
    });
}

function valider() {
    if(!simData || !confirm('Confirmer l\'enregistrement de ' + simData.nb_distributions + ' distribution(s) ?')) return;
    fetch('<?= BASE_URL ?>/distributions/executer', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if(data.success) { alert('✅ ' + data.message); window.location.href = '<?= BASE_URL ?>/distributions'; }
        else alert('❌ ' + data.message);
    });
}
</script>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
