<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'domains_CRUD.php';

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

$domainValue = $data['domainRaw'];
$rawDomain = $data['domain'];

$cleanDomain = preg_replace('/[^a-zA-Z0-9.-]/', '', $rawDomain);
$filenameDomain = str_replace('.', '-', $cleanDomain);
switch ($action) {
    case 'check_domain':

    case 'delete_domain':
        if (!isset($data['domainId']) || empty($data['domainId'])) {
            echo json_encode(['success' => false, 'message' => 'Id de domaine manquant.']);
            exit;
        }
        $domainId = $data['domainId'];
        try {

            $cmd = "sudo /usr/local/bin/delete-apache-site.sh " . escapeshellarg($filenameDomain) . " 2>&1";
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0) {
                Domain::delete($domainId);

                echo json_encode([
                    'success' => true,
                    'message' => "Domaine supprimer avec succès"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'status' => 500,
                    'message' => "Erreur lors de l'exécution du script shell.",
                    'output' => $output
                ]);
                error_log("Shell output : " . implode("\n", $output));
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression du domaine.',
                'error' => $e->getMessage()
            ]);
        }
        break;

    case 'add_domain':
        if (!isset($data['domain']) || empty($data['domain'])) {
            echo json_encode(['success' => false, 'message' => 'Domaine manquant.']);
            exit;
        }
        $redirection = $data['redirection'];

        $domain = new Domain();
        $domain->name = $domainValue;
        $existingDomain = $domain->check_existing();

        if ($existingDomain) {
            echo json_encode([
                'success' => false,
                'status' => 3,
                'message' => "Domaine $domainValue déjà existant.",
            ]);
        } else {

            $filenameDomain = str_replace('.', '-', $cleanDomain);

            $cmd = "sudo /usr/local/bin/create-apache-site.sh " . escapeshellarg($cleanDomain);
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0) {

                try {
                    $domain = new Domain();
                    $domain->name = $domainValue;
                    $domain->redirection = $redirection;
                    $domain->create();

                    echo json_encode([
                        'success' => true,
                        'status' => 1,
                        'message' => "Fichier Apache créé et activé pour $domainValue.",
                        'id' => $domain->id
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'status' => 2,
                        'message' => 'Erreur lors de la création en base de données.',
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'status' => 500,
                    'message' => "Erreur lors de l'exécution du script shell.",
                    'output' => $output
                ]);
            }
        }
        break;
}
