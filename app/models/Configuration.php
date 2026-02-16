<?php

namespace App\Models;

use PDO;

class Configuration extends Model {
    
    protected $table = 'configuration';
    protected $primaryKey = 'id_config';
    
    /**
     * Récupérer une valeur de configuration par clé
     */
    public function getValeur($cle, $default = null) {
        $sql = "SELECT valeur FROM {$this->table} WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['valeur'] : $default;
    }
    
    /**
     * Définir une valeur de configuration
     */
    public function setValeur($cle, $valeur, $description = null) {
        // Vérifier si la configuration existe
        $existing = $this->findBy('cle', $cle);
        
        if ($existing) {
            // Mettre à jour
            $sql = "UPDATE {$this->table} SET valeur = ?";
            $params = [$valeur];
            
            if ($description !== null) {
                $sql .= ", description = ?";
                $params[] = $description;
            }
            
            $sql .= " WHERE cle = ?";
            $params[] = $cle;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Créer
            $data = [
                'cle' => $cle,
                'valeur' => $valeur
            ];
            
            if ($description !== null) {
                $data['description'] = $description;
            }
            
            return $this->create($data);
        }
    }
}
