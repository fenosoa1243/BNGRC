<?php

namespace App\Models;

use PDO;

class Besoin extends Model {
    
    protected $table = 'besoin';
    protected $primaryKey = 'id_besoin';
    
    /**
     * Récupérer tous les besoins avec détails (ville, type)
     */
    public function getAllWithDetails() {
        $sql = "SELECT b.*, v.nom_ville, v.region, t.nom_type, t.categorie, t.unite, t.prix_unitaire,
                (b.quantite * t.prix_unitaire) as valeur_totale
                FROM {$this->table} b
                JOIN ville v ON b.id_ville = v.id_ville
                JOIN type_besoin t ON b.id_type = t.id_type
                ORDER BY b.date_saisie DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un besoin avec détails
     */
    public function getWithDetails($id) {
        $sql = "SELECT b.*, v.nom_ville, t.nom_type 
                FROM {$this->table} b
                JOIN ville v ON b.id_ville = v.id_ville
                JOIN type_besoin t ON b.id_type = t.id_type
                WHERE b.{$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
