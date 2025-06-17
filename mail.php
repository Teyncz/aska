<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lastname = htmlspecialchars(trim($_POST["lastname"]));
    $firstname = htmlspecialchars(trim($_POST["firstname"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $phone = htmlspecialchars(trim($_POST["phone"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    if (empty($lastname) || empty($firstname) || empty($email) || empty($message) || empty($phone)) {
        echo json_encode(["status" => "error", "message" => "Tous les champs sont obligatoires"]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "L'adresse e-mail n'est pas valide"]);
        exit;
    }

    $to = "contact@mobilier-ecologique.fr"; 
    $subject = "[NOUVEAU MESSAGE] de la part de M.$lastname $firstname";
    $headers = "From: contact@mobilier-ecologique.fr\r\n";  
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $body = "Nom : $nom $prenom\n";
    $body .= "Téléphone : $phone\n";
    $body .= "E-mail : $email\n";
    $body .= "Message : \n$message";

    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(["status" => "success", "message" => "Votre message a bien été envoyé !"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Il y a eu une erreur"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée !"]);
}
