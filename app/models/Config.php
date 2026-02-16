<?php

namespace App\Models;

use PDO;

class Config extends Model {
    
    protected $table = 'config';
    protected $primaryKey = 'cle';
    
    /**
     * Récupérer une valeur de configuration
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
        $sql = "INSERT INTO {$this->table} (cle, valeur, description) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valeur = ?, description = COALESCE(?, description)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle, $valeur, $description, $valeur, $description]);
    }
    
    /**
     * Récupérer le pourcentage de frais d'achat
     */
    public function getFraisAchatPourcentage() {
        return (float) $this->getValeur('frais_achat_pourcentage', 10);
    }
}
