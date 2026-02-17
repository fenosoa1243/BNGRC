<?php

namespace App\Models;

use PDO;

class Distribution extends Model {
    
    protected $table = 'distribution';
    protected $primaryKey = 'id_distribution';
    
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
     * Simuler la distribution
     * @param bool $previewOnly Si true, ne sauvegarde pas en BDD (juste preview)
     */
    public function simulerDispatch($previewOnly = false) {
        if(!$previewOnly) {
            $this->db->beginTransaction();
        }
        
        try {
            // Récupérer tous les dons disponibles
            $query_dons = "SELECT d.*, t.id_type, t.nom_type, t.unite, t.prix_unitaire
                          FROM don d
                          JOIN type_besoin t ON d.id_type = t.id_type
                          WHERE d.quantite_restante > 0 
                          ORDER BY d.date_saisie ASC";
            $stmt_dons = $this->db->query($query_dons);
            $dons = $stmt_dons->fetchAll(PDO::FETCH_ASSOC);
            
            $distributions = [];
            $dons_traites = [];
            $nb_distributions = 0;
            
            foreach($dons as $don) {
                if($don['quantite_restante'] <= 0) continue;
                
                // Trouver les villes avec besoins
                $query_besoins = "SELECT 
                                    b.id_ville, 
                                    v.nom_ville,
                                    SUM(b.quantite) as besoin_total,
                                    COALESCE((SELECT SUM(dist.quantite_distribuee) 
                                              FROM distribution dist 
                                              JOIN don dd ON dist.id_don = dd.id_don 
                                              WHERE dist.id_ville = b.id_ville 
                                              AND dd.id_type = ?), 0) as deja_recu,
                                    MIN(b.date_saisie) as premiere_demande
                                 FROM besoin b
                                 JOIN ville v ON b.id_ville = v.id_ville
                                 WHERE b.id_type = ?
                                 GROUP BY b.id_ville, v.nom_ville
                                 HAVING (besoin_total - deja_recu) > 0
                                 ORDER BY premiere_demande ASC";
                
                $stmt_besoins = $this->db->prepare($query_besoins);
                $stmt_besoins->execute([$don['id_type'], $don['id_type']]);
                $besoins = $stmt_besoins->fetchAll(PDO::FETCH_ASSOC);
                
                $quantite_don_restante = $don['quantite_restante'];
                
                foreach($besoins as $besoin) {
                    if($quantite_don_restante <= 0) break;
                    
                    $besoin_restant = $besoin['besoin_total'] - $besoin['deja_recu'];
                    $quantite_a_distribuer = min($quantite_don_restante, $besoin_restant);
                    
                    $distribution_data = [
                        'id_don' => $don['id_don'],
                        'id_ville' => $besoin['id_ville'],
                        'nom_ville' => $besoin['nom_ville'],
                        'type_besoin' => $don['nom_type'],
                        'quantite_distribuee' => $quantite_a_distribuer,
                        'unite' => $don['unite'],
                        'valeur' => $quantite_a_distribuer * $don['prix_unitaire']
                    ];
                    
                    if(!$previewOnly) {
                        // Sauvegarder en BDD
                        $query_dist = "INSERT INTO distribution (id_don, id_ville, quantite_distribuee) 
                                      VALUES (?, ?, ?)";
                        $stmt_dist = $this->db->prepare($query_dist);
                        $stmt_dist->execute([
                            $don['id_don'], 
                            $besoin['id_ville'], 
                            $quantite_a_distribuer
                        ]);
                    }
                    
                    $distributions[] = $distribution_data;
                    $quantite_don_restante -= $quantite_a_distribuer;
                    $nb_distributions++;
                }
                
                if(!$previewOnly && $quantite_don_restante != $don['quantite_restante']) {
                    // Mettre à jour le don
                    $query_update_don = "UPDATE don 
                                        SET quantite_restante = ?,
                                            statut = CASE 
                                                WHEN ? = 0 THEN 'distribue'
                                                WHEN ? < quantite THEN 'partiel'
                                                ELSE 'disponible'
                                            END
                                        WHERE id_don = ?";
                    $stmt_update = $this->db->prepare($query_update_don);
                    $stmt_update->execute([
                        $quantite_don_restante,
                        $quantite_don_restante,
                        $quantite_don_restante,
                        $don['id_don']
                    ]);
                }
                
                $dons_traites[] = [
                    'id_don' => $don['id_don'],
                    'type' => $don['nom_type'],
                    'quantite_initiale' => $don['quantite_restante'],
                    'quantite_distribuee' => $don['quantite_restante'] - $quantite_don_restante,
                    'quantite_restante' => $quantite_don_restante
                ];
            }
            
            if(!$previewOnly) {
                $this->db->commit();
            }
            
            return [
                'distributions' => $distributions,
                'dons_traites' => $dons_traites,
                'nb_distributions' => $nb_distributions
            ];
            
        } catch(\Exception $e) {
            if(!$previewOnly) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
