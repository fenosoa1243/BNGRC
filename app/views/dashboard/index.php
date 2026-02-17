<?php
// ===== INCLUSION INVERSÉE : le contenu est capturé, puis injecté dans le layout =====
$title      = 'Dashboard';
$subtitle   = 'Bienvenue! Voici l\'aperçu de vos opérations';
$active_nav = 'dashboard';
$action_btn = ['url' => BASE_URL . '/dons/nouveau', 'label' => '➕ Nouveau Don'];

ob_start(); // Début capture du contenu
?>

<!-- STATS CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Villes</div>
                <div class="stat-value"><?= $stats['nb_villes'] ?></div>
            </div>
            <div class="stat-icon blue">🏙️</div>
        </div>
        <div class="stat-footer">
            <span class="change-up">↗ +12.5%</span>
            <span class="change-label">vs mois dernier</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Valeur Besoins</div>
                <div class="stat-value"><?= number_format($stats['valeur_besoins']/1000, 0) ?>K</div>
            </div>
            <div class="stat-icon green">📋</div>
        </div>
        <div class="stat-footer">
            <span class="change-up">↗ +8.2%</span>
            <span class="change-label">vs mois dernier</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Valeur Dons</div>
                <div class="stat-value"><?= number_format($stats['valeur_dons']/1000, 0) ?>K</div>
            </div>
            <div class="stat-icon orange">🎁</div>
        </div>
        <div class="stat-footer">
            <span class="change-down">↘ -2.1%</span>
            <span class="change-label">vs mois dernier</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">Taux Couverture</div>
                <div class="stat-value"><?= number_format($stats['taux_couverture'], 1) ?>%</div>
            </div>
            <div class="stat-icon cyan">⏱️</div>
        </div>
        <div class="stat-footer">
            <span class="change-up">↗ +5.4%</span>
            <span class="change-label">vs mois dernier</span>
        </div>
    </div>
</div>

<!-- CONTENT GRID -->
<div class="content-grid">

    <!-- Aperçu des besoins -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Aperçu des Besoins</span>
            <div class="time-tabs">
                <button class="time-tab active">7J</button>
                <button class="time-tab">30J</button>
                <button class="time-tab">90J</button>
                <button class="time-tab">1A</button>
            </div>
        </div>

        <?php if(empty($villes_data)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p>Aucune donnée. Commencez par saisir des besoins.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ville</th><th>Type</th><th>Catégorie</th>
                        <th>Besoin</th><th>Reçu</th><th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 0; foreach($villes_data as $vd): foreach($vd['besoins'] as $b): if($n++ >= 8) break 2; ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($vd['nom_ville']) ?></strong></td>
                        <td><?= htmlspecialchars($b['nom_type']) ?></td>
                        <td><span class="badge badge-<?= $b['categorie'] ?>"><?= ucfirst($b['categorie']) ?></span></td>
                        <td><?= number_format($b['besoin_total'], 0) ?></td>
                        <td><?= number_format($b['don_recu'], 0) ?></td>
                        <td><strong><?= number_format($b['valeur_besoin']/1000, 1) ?>K</strong></td>
                    </tr>
                    <?php endforeach; endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Activité récente -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Activité Récente</span>
        </div>
        <div class="activity-item">
            <div class="activity-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe)">👤</div>
            <div><div class="activity-title">Nouveau besoin enregistré</div><div class="activity-time">Il y a 2 minutes</div></div>
        </div>
        <div class="activity-item">
            <div class="activity-icon" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0)">✅</div>
            <div><div class="activity-title">Distribution #1234 effectuée</div><div class="activity-time">Il y a 5 minutes</div></div>
        </div>
        <div class="activity-item">
            <div class="activity-icon" style="background:linear-gradient(135deg,#fef3c7,#fde68a)">⚠️</div>
            <div><div class="activity-title">Besoin urgent signalé</div><div class="activity-time">Il y a 1 heure</div></div>
        </div>
        <div class="activity-item">
            <div class="activity-icon" style="background:linear-gradient(135deg,#e9d5ff,#d8b4fe)">📦</div>
            <div><div class="activity-title">Simulation terminée</div><div class="activity-time">Il y a 2 heures</div></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean(); // Fin capture
Flight::render('layout/main', compact('title','subtitle','active_nav','action_btn','content'));
