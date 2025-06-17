<?php
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = '373114775';

if (isset($_COOKIE['token'])) {
    $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));

    $expiration = $decoded->exp;
    $userId = $decoded->id;
    $username = $decoded->username;

    if ($decoded->exp < time()) {
        setcookie('token', '', time() - 3600, '/');
        exit;
    } else {
        header('Location: panel.php');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="public/styles/panel.css" rel="stylesheet">
    </link>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>


    <title>Connexion</title>
</head>

<body class="SF h-[100vh] w-[100vw] flex justify-center items-center px-[20px]">

    <?php

    $servername = "localhost";
    $username = "claude_admin";
    $password = "jhqKUt6rNYBc964tSVXQYcsmN";

    try {
        $bdd = new PDO("mysql:host=$servername;dbname=aska", $username, $password);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        if ($email != "" && $password != "") {
            $req = $bdd->prepare("SELECT * FROM user WHERE email = :email ");
            $req->bindValue(':email', $email);
            $req->execute();
            $rep = $req->fetch(PDO::FETCH_ASSOC);
            if ($rep && isset($rep['id'])) {
                if (password_verify($password, $rep['password'])) {
                    $payload = [
                        'id' => $rep['id'],
                        'username' => $rep['email'],
                        'firstname' => $rep['firstname'],
                        'lastname' => $rep['lastname'],
                        'exp' => time() + 36000,
                    ];
                    $encode = JWT::encode($payload, $key, 'HS256');
                    setcookie("token", $encode, time() + 36000);
                    header('Location: panel.php');
                    exit();
                }
            } else {
                $error_msg = "Email ou mdp incorrect !";
            }
        }
    }
    ?>
    <section class="flex flex-col max-w-[400px] w-full items-center">
        <img class="w-[100px] rounded-[5px] mb-[40px]" src="public/img/Logo.jpg">

        <h1 class="w-full font-[700] text-[28px] pb-[10px]">Se connecter</h1>
        <form class="w-full mx-auto flex flex-col gap-[30px]" method="POST" action="">
            <div class="flex flex-col gap-[20px]">
                <div class="flex flex-col gap-[10px]">
                    <label class="text-[15px]">Utilisateur</label>
                    <input class="h-[46px] rounded-[4px] border border-[#454545] px-[30px]" type="text" placeholder="Utilisateur" id="email" name="email" require />
                </div>
                <div class="flex flex-col gap-[10px]">
                    <label>Mot de passe</label>
                    <div class="h-[46px] rounded-[4px] border border-[#454545] px-[30px] flex items-center">
                        <input class="h-full w-full" type="password" placeholder="Mot de passe" id="password" name="password" require />
                        <button type="button" id="showPasswordBtn" class="w-fit cursor-pointer hover:scale-95 transition duration-300"><i style="display: block;" id="showIcon" stroke-width="1.5" data-lucide="eye"></i><i id="hideIcon" style="display: none;" stroke-width="1.5" data-lucide="eye-off"></i></button>
                    </div>
                </div>
                <button class="bg-[#C03C3A] rounded-[4px] h-[46px] w-full text-white hover:scale-97 transition duration-300 cursor-pointer" type="submit" value="Se connecter" name="login">Se connecter</button>
        </form>


    </section>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script>
        const showPasswordButton = document.getElementById('showPasswordBtn');
        const passwordInput = document.getElementById('password');

        const showIcon = document.getElementById('showIcon')
        const hideIcon = document.getElementById('hideIcon')

        showPasswordButton.addEventListener('click', (e) => {
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            showIcon.style.display = showIcon.style.display === 'block' ? 'none' : 'block';
            hideIcon.style.display = hideIcon.style.display === 'block' ? 'none' : 'block';
        });
    </script>
</body>

</html>