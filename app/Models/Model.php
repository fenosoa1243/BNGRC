<?php

namespace App\Models;

use PDO;
use Flight;

/**
 * Base Model class
 * Tous les models héritent de cette classe
 */
abstract class Model {
    
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Flight::db();
    }
    
    /**
     * Récupérer tous les enregistrements
     */
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un enregistrement par ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un enregistrement par critère
     */
    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer plusieurs enregistrements par critère
     */
    public function where($column, $value, $orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Créer un nouvel enregistrement
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Mettre à jour un enregistrement
     */
    public function update($id, $data) {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        $setString = implode(', ', $sets);
        
        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = ?";
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Supprimer un enregistrement
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Compter les enregistrements
     */
    public function count($where = null, $value = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where && $value !== null) {
            $sql .= " WHERE {$where} = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$value]);
        } else {
            $stmt = $this->db->query($sql);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Exécuter une requête SQL personnalisée
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
