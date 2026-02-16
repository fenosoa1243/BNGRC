<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Villes - BNGRC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏛️ BNGRC - Gestion des Dons</h1>
            <p class="subtitle">Liste des Villes</p>
        </header>

        <nav class="main-nav">
            <a href="/dashboard">📊 Dashboard</a>
            <a href="/villes" class="active">🏙️ Villes</a>
            <a href="/besoins">📋 Besoins</a>
            <a href="/dons">🎁 Dons</a>
            <a href="/distributions">📦 Distributions</a>
        </nav>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                switch($_GET['success']) {
                    case 'create': echo '✓ Ville créée avec succès'; break;
                    case 'update': echo '✓ Ville modifiée avec succès'; break;
                    case 'delete': echo '✓ Ville supprimée avec succès'; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h2>🏙️ Villes Enregistrées</h2>
                <a href="/villes/nouveau" class="btn btn-primary">➕ Nouvelle Ville</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de la Ville</th>
                        <th>Région</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($villes)): ?>
                        <tr><td colspan="4" class="text-center">Aucune ville enregistrée</td></tr>
                    <?php else: ?>
                        <?php foreach($villes as $ville): ?>
                            <tr>
                                <td><?php echo $ville['id_ville']; ?></td>
                                <td><strong><?php echo htmlspecialchars($ville['nom_ville']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ville['region']); ?></td>
                                <td>
                                    <a href="/villes/modifier/<?php echo $ville['id_ville']; ?>" class="btn-small btn-info">✏️ Modifier</a>
                                    <a href="/villes/supprimer/<?php echo $ville['id_ville']; ?>" 
                                       class="btn-small btn-danger" 
                                       onclick="return confirm('Voulez-vous vraiment supprimer cette ville ?')">🗑️ Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer><p>© 2026 BNGRC - Projet Final S3</p></footer>
</body>
</html>