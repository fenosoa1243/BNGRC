<?php

namespace App\Models;

use PDO;

class Achat extends Model {
    
    protected $table = 'achat';
    protected $primaryKey = 'id_achat';
    
    /**
     * Récupérer tous les achats avec détails (ville, type)
     */
    public function getAllWithDetails() {
        $sql = "SELECT a.*, v.nom_ville, v.region, t.nom_type, t.categorie, t.unite,
                (a.quantite * a.prix_unitaire) as montant_base,
                a.montant_total
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
        $sql = "SELECT a.*, v.nom_ville, v.region, t.nom_type, t.categorie, t.unite,
                (a.quantite * a.prix_unitaire) as montant_base,
                a.montant_total
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
     * Calculer le montant total avec frais
     */
    public function calculerMontantTotal($montant_base, $frais_pourcentage) {
        return $montant_base * (1 + ($frais_pourcentage / 100));
    }
    
    /**
     * Vérifier si un achat existe dans les dons restants
     */
    public function verifierDisponibiliteDon($id_type, $quantite) {
        $sql = "SELECT SUM(quantite_restante) as total_disponible
                FROM don
                WHERE id_type = ? AND quantite_restante > 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['total_disponible'] ?? 0) >= $quantite;
    }
    
    /**
     * Valider les achats simulés
     */
    public function validerAchatsSimules() {
        $sql = "UPDATE {$this->table} SET statut = 'valide' WHERE statut = 'simule'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
    
    /**
     * Supprimer les achats simulés
     */
    public function supprimerAchatsSimules() {
        $sql = "DELETE FROM {$this->table} WHERE statut = 'simule'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
