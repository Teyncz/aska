<?php

include 'include/conn.php';

class Domain
{
    public $id;
    public $name;
    public $redirection;

    function check_existing()
    {
        try {
            $sql = 'SELECT * FROM domain WHERE name = :name';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);
            $query->bindValue(':name', $this->name, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans Domain::create() : " . $e->getMessage());
            throw $e;
        }
    }

    function create()
    {
        try {
            $sql = 'INSERT INTO domain (name, redirection) VALUES (:name, :redirection);';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);
            $query->bindValue(':name', $this->name, PDO::PARAM_STR);
            $query->bindValue(':redirection', $this->redirection, PDO::PARAM_STR);
            $query->execute();
            $this->id = $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans Domain::create() : " . $e->getMessage());
            throw $e;
        }
    }

    static function delete($domain_id)
    {
        try {
            $sql = 'DELETE FROM domain WHERE id = :domain_id';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);
            $query->bindValue(':domain_id', $domain_id, PDO::PARAM_INT);
            $query->execute();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans Domain::delete() : " . $e->getMessage());
            throw $e;
        }
    }
}
