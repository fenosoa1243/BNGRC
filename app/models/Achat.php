<?php

namespace App\Models;

use PDO;

class Achat extends Model {
    
    protected $table = 'achat';
    protected $primaryKey = 'id_achat';
    
    /**
     * Récupérer tous les achats avec détails
     */
    public function getAllWithDetails() {
        $sql = "SELECT a.*, v.nom_ville, v.region, t.nom_type, t.categorie, t.unite, t.prix_unitaire
                FROM {$this->table} a
                JOIN ville v ON a.id_ville = v.id_ville
                JOIN type_besoin t ON a.id_type = t.id_type
                ORDER BY a.date_achat DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les achats filtrés par ville
     */
    public function getByVille($id_ville) {
        $sql = "SELECT a.*, v.nom_ville, v.region, t.nom_type, t.categorie, t.unite, t.prix_unitaire
                FROM {$this->table} a
                JOIN ville v ON a.id_ville = v.id_ville
                JOIN type_besoin t ON a.id_type = t.id_type
                WHERE a.id_ville = ?
                ORDER BY a.date_achat DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_ville]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Valider des achats en simulation
     */
    public function validerSimulation($ids_achats) {
        if(empty($ids_achats)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids_achats), '?'));
        $sql = "UPDATE {$this->table} 
                SET statut = 'valide' 
                WHERE id_achat IN ($placeholders) AND statut = 'simulation'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids_achats);
        return $stmt->rowCount();
    }
    
    /**
     * Supprimer les achats en simulation
     */
    public function supprimerSimulations() {
        $sql = "DELETE FROM {$this->table} WHERE statut = 'simulation'";
        $stmt = $this->db->query($sql);
        return $stmt->rowCount();
    }
}
