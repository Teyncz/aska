<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, [
    'debug' => true,
    'cache' => false
]);
$twig->addExtension(new DebugExtension());


$servername = "localhost";
$username = "claude_admin";
$password = "jhqKUt6rNYBc964tSVXQYcsmN ";

try {
    $bdd = new PDO("mysql:host=$servername;dbname=aska", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

$host = $_SERVER['HTTP_HOST'];


$req = $bdd->prepare("SELECT * FROM domain");
$req->execute();
$domains = $req->fetchAll();
if (count($domains) > 0) {
} else {
}


echo $twig->render('page.html.twig', [
    'host' => $host
]);
