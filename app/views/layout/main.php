<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - BNGRC</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bngrc.css">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="logo">
        <div class="logo-icon">B</div>
        <div class="logo-text">BNGRC</div>
    </div>
    <nav>
        <a href="<?= BASE_URL ?>/dashboard"     class="nav-item <?= ($active_nav??'') === 'dashboard'     ? 'active' : '' ?>"><span>📊</span> Dashboard</a>
        <a href="<?= BASE_URL ?>/villes"        class="nav-item <?= ($active_nav??'') === 'villes'        ? 'active' : '' ?>"><span>🏙️</span> Villes</a>
        <a href="<?= BASE_URL ?>/besoins"       class="nav-item <?= ($active_nav??'') === 'besoins'       ? 'active' : '' ?>"><span>📋</span> Besoins</a>
        <a href="<?= BASE_URL ?>/dons"          class="nav-item <?= ($active_nav??'') === 'dons'          ? 'active' : '' ?>"><span>🎁</span> Dons</a>
        <a href="<?= BASE_URL ?>/distributions" class="nav-item <?= ($active_nav??'') === 'distributions' ? 'active' : '' ?>"><span>📦</span> Distributions</a>
        <a href="<?= BASE_URL ?>/achats"        class="nav-item <?= ($active_nav??'') === 'achats'        ? 'active' : '' ?>"><span>💰</span> Achats <span class="nav-badge">NEW</span></a>
        <a href="<?= BASE_URL ?>/recap"         class="nav-item <?= ($active_nav??'') === 'recap'         ? 'active' : '' ?>"><span>📈</span> Récapitulation</a>
    </nav>
</div>

<!-- ===== MAIN ===== -->
<div class="main-content">
    <div class="top-bar">
        <div>
            <div class="page-title"><?= $title ?? 'Dashboard' ?></div>
            <div class="page-subtitle"><?= $subtitle ?? 'Bienvenue! Voici l\'aperçu de vos opérations' ?></div>
        </div>
        <div class="top-actions">
            <span class="icon-btn">🔄</span>
            <span class="icon-btn">⬇️</span>
            <span class="icon-btn">⚙️</span>
            <?php if(!empty($action_btn)): ?>
                <a href="<?= $action_btn['url'] ?>" class="btn btn-primary"><?= $action_btn['label'] ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?= $content ?>
</div>

</body>
</html>
