<?php
$title      = 'Récapitulation';
$subtitle   = 'Vue globale des besoins et distributions';
$active_nav = 'recap';

ob_start();
?>

<!-- Auto-refresh -->
<div class="alert alert-info" style="display:flex;justify-content:space-between;align-items:center">
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()" style="width:16px;height:16px">
        Actualisation automatique toutes les 5 secondes
    </label>
    <span style="font-size:13px">
        Dernière mise à jour : <strong id="last-update">—</strong>
        <button class="btn btn-info btn-sm" onclick="loadStats()" style="margin-left:12px">🔄 Actualiser</button>
    </span>
</div>

<!-- Stats cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div><div class="stat-label">Besoins Totaux</div><div class="stat-value" id="s-besoins">—</div></div>
            <div class="stat-icon blue">📋</div>
        </div>
        <div class="stat-footer"><span id="s-besoins-qty" class="change-label">— unités</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div><div class="stat-label">Besoins Satisfaits</div><div class="stat-value" id="s-satisfaits">—</div></div>
            <div class="stat-icon green">✅</div>
        </div>
        <div class="stat-footer"><span id="s-satisfaits-qty" class="change-label">— unités</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div><div class="stat-label">Besoins Restants</div><div class="stat-value" id="s-restants">—</div></div>
            <div class="stat-icon orange">⏳</div>
        </div>
        <div class="stat-footer"><span id="s-restants-pct" class="change-down">—% non couvert</span></div>
    </div>
</div>

<!-- Barre de progression -->
<div class="card">
    <div class="card-header">
        <span class="card-title">📊 Taux de Couverture Global</span>
        <strong id="pct-label" style="font-size:20px;color:#667eea">—%</strong>
    </div>
    <div class="progress" style="height:16px">
        <div class="progress-bar" id="prog-bar" style="width:0%"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:13px;color:#94a3b8">
        <span>0%</span>
        <span>Satisfaits / Totaux</span>
        <span>100%</span>
    </div>
</div>

<script>
let autoInt = null;

function fmt(v) { return Math.round(v).toLocaleString('fr-FR') + ' Ar'; }
function fmtQty(v) { return Math.round(v).toLocaleString('fr-FR') + ' unités'; }

function loadStats() {
    fetch('<?= BASE_URL ?>/recap/stats')
    .then(r => r.json())
    .then(d => {
        document.getElementById('s-besoins').textContent      = fmt(d.besoins.montant_total);
        document.getElementById('s-besoins-qty').textContent  = fmtQty(d.besoins.quantite_totale);
        document.getElementById('s-satisfaits').textContent   = fmt(d.satisfaits.montant);
        document.getElementById('s-satisfaits-qty').textContent = fmtQty(d.satisfaits.quantite);
        document.getElementById('s-restants').textContent     = fmt(d.restants.montant);
        document.getElementById('s-restants-pct').textContent = d.restants.pourcentage.toFixed(1) + '% non couvert';

        const taux = d.besoins.montant_total > 0
            ? ((d.satisfaits.montant / d.besoins.montant_total) * 100).toFixed(1) : 0;
        document.getElementById('pct-label').textContent    = taux + '%';
        document.getElementById('prog-bar').style.width     = taux + '%';
        document.getElementById('last-update').textContent  = new Date().toLocaleTimeString('fr-FR');
    });
}

function toggleAutoRefresh() {
    if(document.getElementById('auto-refresh').checked) {
        loadStats();
        autoInt = setInterval(loadStats, 5000);
    } else {
        clearInterval(autoInt); autoInt = null;
    }
}

loadStats();
</script>

<?php
$content = ob_get_clean();
Flight::render('layout/main', compact('title','subtitle','active_nav','content'));
