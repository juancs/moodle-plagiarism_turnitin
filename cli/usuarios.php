<?php

define('CLI_SCRIPT', true);

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ .'/../vendor/autoload.php';


$config = get_config('plagiarism');

$tiiapiurl = (substr($config->plagiarism_turnitin_apiurl, -1) == '/') ? substr($config->plagiarism_turnitin_apiurl, 0, -1) : $config->plagiarism_turnitin_apiurl;
$tiiintegrationid = 12;
$tiiaccountid = $config->plagiarism_turnitin_accountid;
$tiiapiurl = $tiiapiurl;
$tiisecretkey = $config->plagiarism_turnitin_secretkey;
$diagnostic = $config->plagiarism_turnitin_enablediagnostic;
$langcode = 'es';

$api = new \Integrations\PhpSdk\TurnitinAPI($tiiaccountid, $tiiapiurl, $tiisecretkey,$tiiintegrationid, $langcode);

$turnitinusers = [];
$idstoread = [];
$users = $DB->get_records('plagiarism_turnitin_users');
foreach ($users as $u) {

    $idstoread[] = $u->turnitin_uid;

    if (count($idstoread) == 100) {
        $users = new \Integrations\PhpSdk\TiiUser();
        $users->setUserIds($idstoread);
        try {
            $users = $api->readUsers($users);
            $tiiusers = $users->getUsers();
            foreach ($tiiusers as $tiiuser) {
                $turnitinusers[$tiiuser->getUserId()] = $tiiuser;
            }

            foreach ($idstoread as $id) {
                if (array_key_exists($id, $turnitinusers)) {
                    echo "OK," . $turnitinusers[$id]->getUserId() . "," . $turnitinusers[$id]->getDefaultRole() . "," . $turnitinusers[$id]->getEmail() . "," . $turnitinusers[$id]->getFirstName() . "," . $turnitinusers[$id]->getLastName() . "\n";
                } else {
                    echo "ERROR,". $id . ",No existe\n";
                }
            }

        } catch (\Exception $e) {
            print_r($e);
            //echo "ERROR," . $tiiuser->getUserId() . "," . $e->getMessage() . "\n";
        }

        $idstoread = [];
        $turnitinusers = [];
    }
}


