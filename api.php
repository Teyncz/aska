<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'domains_CRUD.php';
include 'certficats_CRUD.php';
include 'rgpd_CRUD.php';


$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

$domainValue = $data['domainRaw'];
$rawDomain = $data['domain'];

$cleanDomain = preg_replace('/[^a-zA-Z0-9.-]/', '', $rawDomain);
$filenameDomain = str_replace('.', '-', $cleanDomain);
switch ($action) {
    case 'get_cert':
        if (!isset($data['domain']) || empty($data['domain'])) {
            echo json_encode(['success' => false, 'message' => 'domaine manquant.']);
            exit;
        }
        $output = [];
        $return_var = 0;
        exec('sudo /usr/local/bin/get-cert.sh', $output, $return_var);

        if ($return_var === 0) {

            $certificates = [];
            foreach ($output as $line) {
                if (strpos($line, 'Domaine') === 0) continue;

                $parts = explode("\t", $line);
                if (count($parts) >= 6 && $parts[0] === $data['domain']) {
                    $issuer =  $parts[4];
                    if (preg_match('/O=([^,]+)/', $issuer, $matches)) {
                        $organization = trim($matches[1]);
                    } else {
                        echo "Organisation non trouvée";
                    }
                    echo json_encode(['success' => false, 'message' => 'certificat généré trouvé.', 'not_before' => $parts[1], 'not_after' => $parts[2], 'issuer' => $organization, 'valid' => $parts[5]]);
                }
            }
        } else {
            echo "Erreur lors de l'exécution du script.";
        }
        break;
    case 'edit_rgpd':
        if (!isset($data['content']) || empty($data['content'])) {
            echo json_encode(['success' => false, 'message' => 'contenu manquant.']);
            exit;
        }

        $rgpd = new RGPD();
        $rgpd->type = $data['type'];
        $rgpd->content = $data['content'];
        $response = $rgpd->update();
        echo json_encode($response);
        break;
    case 'generate_cert':
        if (!isset($data['domain']) || empty($data['domain'])) {
            echo json_encode(['success' => false, 'message' => 'domaine manquant.']);
            exit;
        }

        $domain = $data['domain'];
        $email = $data['email'];

        exec("sudo /usr/local/bin/generate-cert.sh " . escapeshellarg($domain) . " " . escapeshellarg($email), $output, $return_var);

        if ($return_var === 0 || $return_var === 1) {
            $certificat = new Certficat();
            $certificat->domain = $domain;
            $certificat->create();
            echo json_encode(['success' => true, 'status' => '1', 'output' => $output, 'message' => 'Certificat généré avec succès !']);
        } else {
            echo json_encode(['success' => false, 'status' => '2', 'output' => $output, 'message' => 'Erreur lors de la génération du certificat.']);
        }
        break;
    case 'delete-cert':
        if (!isset($data['domain']) || empty($data['domain'])) {
            echo json_encode(['success' => false, 'message' => 'domaine manquant.']);
            exit;
        }

        $domain = $data['domain'];

        $cmd = "sudo /usr/local/bin/delete-cert.sh " . escapeshellarg($domain) . ' 2>&1';
        exec($cmd, $output, $return_var);


        if ($return_var === 0) {
            Certficat::delete($domain);
            echo json_encode(['success' => false, 'status' => '1', 'message' => 'Certificat généré avec succès !']);
        } else {
            echo json_encode(['success' => false, 'return_var' => $return_var, 'output' => $output, 'status' => '2', 'message' => 'Erreur lors de la génération du certificat.']);
        }
        break;

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

            $cmd = "sudo /usr/local/bin/create-apache-site.sh " . escapeshellarg($domainValue);
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
                        'output' => $output,
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
