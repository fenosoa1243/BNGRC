<?php

namespace App\Models;

use PDO;

class Don extends Model {
    
    protected $table = 'don';
    protected $primaryKey = 'id_don';
    
    /**
     * Récupérer tous les dons avec détails
     */
    public function getAllWithDetails() {
        $sql = "SELECT d.*, t.nom_type, t.categorie, t.unite, t.prix_unitaire,
                (d.quantite * t.prix_unitaire) as valeur_totale,
                (d.quantite_restante * t.prix_unitaire) as valeur_restante
                FROM {$this->table} d
                JOIN type_besoin t ON d.id_type = t.id_type
                ORDER BY d.date_saisie DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
