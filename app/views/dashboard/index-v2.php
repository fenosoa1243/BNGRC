<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BNGRC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8f9fa;
            color: #1e293b;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 20px 0;
            overflow-y: auto;
        }

        .logo {
            padding: 0 20px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #6366f1;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-title {
            padding: 0 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .nav-item.active {
            background: #eef2ff;
            color: #6366f1;
            border-right: 3px solid #6366f1;
        }

        .nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 24px;
        }

        /* Header */
        .header {
            background: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .header-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue { background: #dbeafe; color: #3b82f6; }
        .stat-icon.green { background: #d1fae5; color: #10b981; }
        .stat-icon.purple { background: #ede9fe; color: #8b5cf6; }
        .stat-icon.orange { background: #fed7aa; color: #f59e0b; }
        .stat-icon.cyan { background: #cffafe; color: #06b6d4; }

        .stat-title {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-change.positive { color: #10b981; }
        .stat-change.negative { color: #ef4444; }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .time-filter {
            display: flex;
            gap: 8px;
        }

        .time-btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .time-btn.active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        /* Table */
        .data-table {
            width: 100%;
            margin-top: 20px;
        }

        .data-table th {
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.nature { background: #d1fae5; color: #065f46; }
        .badge.materiau { background: #fee2e2; color: #991b1b; }
        .badge.argent { background: #fef3c7; color: #92400e; }

        /* Activity List */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: #94a3b8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
            .content-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">B</div>
            <span>BNGRC</span>
        </div>

        <div class="nav-section">
            <div class="nav-title">Menu Principal</div>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="nav-item active">
                <span>📊</span> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/villes" class="nav-item">
                <span>🏙️</span> Villes
            </a>
            <a href="<?php echo BASE_URL; ?>/besoins" class="nav-item">
                <span>📋</span> Besoins
            </a>
            <a href="<?php echo BASE_URL; ?>/dons" class="nav-item">
                <span>🎁</span> Dons
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-title">Opérations</div>
            <a href="<?php echo BASE_URL; ?>/achats" class="nav-item">
                <span>💰</span> Achats
                <span class="nav-badge">NEW</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/distributions/simuler" class="nav-item">
                <span>🚀</span> Distributions
            </a>
            <a href="<?php echo BASE_URL; ?>/recap" class="nav-item">
                <span>📈</span> Récapitulation
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Dashboard</h1>
                <div class="header-subtitle">Bienvenue! Voici l'aperçu de vos données</div>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/besoins/nouveau" class="btn btn-secondary">
                    ➕ Nouveau Besoin
                </a>
                <a href="<?php echo BASE_URL; ?>/dons/nouveau" class="btn btn-primary">
                    🎁 Enregistrer un Don
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Villes</div>
                        <div class="stat-value"><?php echo $stats['nb_villes']; ?></div>
                        <div class="stat-change positive">
                            ↗ +12.5%
                        </div>
                    </div>
                    <div class="stat-icon blue">🏙️</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Valeur Besoins</div>
                        <div class="stat-value"><?php echo number_format($stats['valeur_besoins']/1000000, 1); ?>M</div>
                        <div class="stat-change positive">
                            ↗ +8.2%
                        </div>
                    </div>
                    <div class="stat-icon green">📋</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Valeur Dons</div>
                        <div class="stat-value"><?php echo number_format($stats['valeur_dons']/1000000, 1); ?>M</div>
                        <div class="stat-change negative">
                            ↘ -2.1%
                        </div>
                    </div>
                    <div class="stat-icon purple">🎁</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Taux de Couverture</div>
                        <div class="stat-value"><?php echo number_format($stats['taux_couverture'], 1); ?>%</div>
                        <div class="stat-change positive">
                            ↗ +5.4%
                        </div>
                    </div>
                    <div class="stat-icon cyan">⏱️</div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Besoins par Ville -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Besoins par Ville</h2>
                    <div class="time-filter">
                        <button class="time-btn active">7J</button>
                        <button class="time-btn">30J</button>
                        <button class="time-btn">90J</button>
                        <button class="time-btn">1A</button>
                    </div>
                </div>

                <?php if(empty($villes_data)): ?>
                    <p style="color: #94a3b8; text-align: center; padding: 40px 0;">
                        Aucune donnée disponible
                    </p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Besoin</th>
                                <th>Reçu</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 0;
                            foreach($villes_data as $ville_data): 
                                foreach($ville_data['besoins'] as $besoin): 
                                    if($count++ >= 8) break 2;
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ville_data['nom_ville']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($besoin['nom_type']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $besoin['categorie']; ?>">
                                            <?php echo ucfirst($besoin['categorie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($besoin['besoin_total'], 0); ?></td>
                                    <td><?php echo number_format($besoin['don_recu'], 0); ?></td>
                                    <td><strong><?php echo number_format($besoin['valeur_besoin'], 0); ?> Ar</strong></td>
                                </tr>
                            <?php 
                                endforeach;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Activité Récente -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Activité Récente</h2>
                </div>

                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon" style="background: #dbeafe; color: #3b82f6;">
                            👤
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Nouveau besoin enregistré</div>
                            <div class="activity-time">Il y a 2 minutes</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon" style="background: #d1fae5; color: #10b981;">
                            ✅
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Don #1234 distribué</div>
                            <div class="activity-time">Il y a 5 minutes</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon" style="background: #fef3c7; color: #f59e0b;">
                            ⚠️
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Besoin urgent à Antananarivo</div>
                            <div class="activity-time">Il y a 1 heure</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon" style="background: #ede9fe; color: #8b5cf6;">
                            📦
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Nouvelle distribution simulée</div>
                            <div class="activity-time">Il y a 2 heures</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
