<?php

include 'include/conn.php';

class Certficat
{
    public $id;
    public $domain;

    function check_existing()
    {
        try {
            $sql = 'SELECT * FROM certificats WHERE domain = :domain';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);
            $query->bindValue(':domain', $this->domain, PDO::PARAM_STR);
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
            $sql = 'INSERT INTO certificats (domain) VALUES (:domain);';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);
            $query->bindValue(':domain', $this->domain, PDO::PARAM_STR);
            $query->execute();
            $this->id = $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans Domain::create() : " . $e->getMessage());
            throw $e;
        }
    }

    static function delete($domain)
    {
        try {
            $sql = 'DELETE FROM certificats WHERE domain = :domain';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);
            $query->bindValue(':domain', $domain, PDO::PARAM_STR);
            $query->execute();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans Domain::delete() : " . $e->getMessage());
            throw $e;
        }
    }
}
