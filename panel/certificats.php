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


$req = $bdd->prepare("SELECT domain.* FROM domain LEFT JOIN certificats ON domain.name = certificats.domain WHERE certificats.domain IS NULL;");
$req->execute();
$domains = $req->fetchAll();
if (count($domains) > 0) {
} else {
}

foreach ($domains as &$domain) {
    $domain['ip'] = gethostbyname($domain['name']);
}

$output = [];
$return_var = 0;
exec('sudo /usr/local/bin/get-cert.sh', $output, $return_var);

if ($return_var === 0) {

    $certificates = [];
    foreach ($output as $line) {
        if (strpos($line, 'Domaine') === 0) continue;

        $parts = explode("\t", $line);
        if (count($parts) >= 6) {
            $issuer =  $parts[4];

            if (preg_match('/O=([^,]+)/', $issuer, $matches)) {
                $organization = trim($matches[1]);
            } else {
                echo "Organisation non trouvée";
            }

            $certificates[] = [
                'domain' => $parts[0],
                'not_before' => $parts[1],
                'not_after' => $parts[2],
                'serial' => $parts[3],
                'issuer' => $organization,
                'valid' => $parts[5]
            ];
        }
    }
} else {
    echo "Erreur lors de l'exécution du script.";
}


echo $twig->render('certificats.html.twig', [
    'user' => $user,
    'domains' => $domains,
    'page' => 'certificats',
    'certificates' => $certificates
]);
