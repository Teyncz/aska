<?php

include 'include/conn.php';

class RGPD
{
    public $type;
    public $content;

    function update()
    {
        try {
            $sql = 'UPDATE `rgpd` SET `content` = :content WHERE type = :type';
            $pdo = __main_conn();
            $query = $pdo->prepare($sql);

            $query->bindValue(':content', $this->content, PDO::PARAM_STR);
            $query->bindValue(':type', $this->type, PDO::PARAM_STR);

            $query->execute();

            if ($query->rowCount() > 0) {
                return ['success' => true, 'message' => 'RGPD mis à jour avec succès !'];
            } else {
                return ['success' => false, 'message' => 'Aucune modification effectuée'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()];
        }
    }
}
