<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
    'debug' => true,
    'cache' => false
]);
$twig->addExtension(new DebugExtension());

$key = '373114775';

if (isset($_COOKIE['token'])) {
    $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));

    $expiration = $decoded->exp;
    $userId = $decoded->id;
    $user = [
        'username'  => $decoded->username,
        'firstname' => $decoded->firstname,
        'lastname'  => $decoded->lastname
    ];



    if ($decoded->exp < time()) {
        setcookie('token', '', time() - 3600, '/');
        header('Location: ../login.php');
        exit;
    }
} else {
    header('Location: ../login.php');
}

$servername = "localhost";
$username = "claude_admin";
$password = "jhqKUt6rNYBc964tSVXQYcsmN";

try {
    $bdd = new PDO("mysql:host=$servername;dbname=aska", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

$req = $bdd->prepare("SELECT * FROM domain");
$req->execute();
$domains = $req->fetchAll();
if (count($domains) > 0) {
} else {
}

$req = $bdd->prepare("SELECT * FROM certificats");
$req->execute();
$certificats = $req->fetchAll();
if (count($certificats) > 0) {
} else {
}


echo $twig->render('panel.html.twig', [
    'user' => $user,
    'domains' => $domains,
    'certificats' => $certificats,
    'page' => 'overview'
]);
