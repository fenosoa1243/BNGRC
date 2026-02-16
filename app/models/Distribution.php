<?php

namespace App\Models;

use PDO;

class Distribution extends Model {
    
    protected $table = 'distribution';
    protected $primaryKey = 'id_distribution';
    
    /**
     * Récupérer toutes les distributions avec détails
     */
    public function getAllWithDetails() {
        $sql = "SELECT dist.*, v.nom_ville, v.region, t.nom_type, t.categorie, t.unite,
                d.donateur, t.prix_unitaire,
                (dist.quantite_distribuee * t.prix_unitaire) as valeur_distribuee
                FROM {$this->table} dist
                JOIN ville v ON dist.id_ville = v.id_ville
                JOIN don d ON dist.id_don = d.id_don
                JOIN type_besoin t ON d.id_type = t.id_type
                ORDER BY dist.date_distribution DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Simuler la distribution automatique des dons
     */
    public function simulerDispatch() {
        try {
            $this->db->beginTransaction();
            
            // Récupérer tous les dons disponibles triés par date
            $query_dons = "SELECT d.*, t.id_type 
                          FROM don d
                          JOIN type_besoin t ON d.id_type = t.id_type
                          WHERE d.quantite_restante > 0 
                          ORDER BY d.date_saisie ASC";
            $stmt_dons = $this->db->query($query_dons);
            $dons = $stmt_dons->fetchAll(PDO::FETCH_ASSOC);
            
            $distributions_effectuees = 0;
            
            // Pour chaque don
            foreach($dons as $don) {
                if($don['quantite_restante'] <= 0) continue;
                
                // Trouver les villes ayant besoin de ce type
                $query_besoins = "SELECT b.id_ville, SUM(b.quantite) as besoin_total,
                                 COALESCE((SELECT SUM(dist.quantite_distribuee) 
                                          FROM distribution dist 
                                          JOIN don dd ON dist.id_don = dd.id_don 
                                          WHERE dist.id_ville = b.id_ville 
                                          AND dd.id_type = ?), 0) as deja_recu
                                 FROM besoin b
                                 WHERE b.id_type = ?
                                 GROUP BY b.id_ville
                                 HAVING (besoin_total - deja_recu) > 0
                                 ORDER BY MIN(b.date_saisie) ASC";
                
                $stmt_besoins = $this->db->prepare($query_besoins);
                $stmt_besoins->execute([$don['id_type'], $don['id_type']]);
                $besoins = $stmt_besoins->fetchAll(PDO::FETCH_ASSOC);
                
                $quantite_don_restante = $don['quantite_restante'];
                
                // Distribuer aux villes dans l'ordre
                foreach($besoins as $besoin) {
                    if($quantite_don_restante <= 0) break;
                    
                    $besoin_restant = $besoin['besoin_total'] - $besoin['deja_recu'];
                    $quantite_a_distribuer = min($quantite_don_restante, $besoin_restant);
                    
                    // Créer la distribution
                    $query_dist = "INSERT INTO distribution (id_don, id_ville, quantite_distribuee) 
                                  VALUES (?, ?, ?)";
                    $stmt_dist = $this->db->prepare($query_dist);
                    $stmt_dist->execute([$don['id_don'], $besoin['id_ville'], $quantite_a_distribuer]);
                    
                    // Mettre à jour le don
                    $query_update_don = "UPDATE don 
                                        SET quantite_restante = quantite_restante - ?,
                                            statut = CASE 
                                                WHEN (quantite_restante - ?) = 0 THEN 'distribue'
                                                WHEN (quantite_restante - ?) < quantite THEN 'partiel'
                                                ELSE 'disponible'
                                            END
                                        WHERE id_don = ?";
                    $stmt_update = $this->db->prepare($query_update_don);
                    $stmt_update->execute([
                        $quantite_a_distribuer, 
                        $quantite_a_distribuer, 
                        $quantite_a_distribuer, 
                        $don['id_don']
                    ]);
                    
                    $quantite_don_restante -= $quantite_a_distribuer;
                    $distributions_effectuees++;
                }
            }
            
            $this->db->commit();
            return $distributions_effectuees;
            
        } catch(\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
