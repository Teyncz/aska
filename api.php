<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

switch ($action) {
    case 'check_domain':

    case 'add_domain':
        if (!isset($data['domain']) || empty($data['domain'])) {
            echo json_encode(['success' => false, 'message' => 'Domaine manquant.']);
            exit;
        }

        $rawDomain = $data['domain'];
        $cleanDomain = preg_replace('/[^a-zA-Z0-9.-]/', '', $rawDomain);
        $filenameDomain = str_replace('.', '-', $cleanDomain);

        $cmd = "sudo /usr/local/bin/create-apache-site.sh " . escapeshellarg($cleanDomain);
        exec($cmd, $output, $returnCode);

        if ($returnCode === 0) {
            echo json_encode([
                'success' => true,
                'message' => "Fichier Apache créé et activé pour $cleanDomain."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Erreur lors de l'exécution du script shell.",
                'output' => $output
            ]);
        }
}
