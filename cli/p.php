<?php

define('CLI_SCRIPT', true);

require_once __DIR__ . '/../../../config.php';
require_once($CFG->dirroot . '/mod/turnitintooltwo/sdk/api.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/lib.php');

$learners = $DB->get_records('turnitintooltwo_users', array('turnitin_utp' => 2));
$config = turnitintooltwo_admin_config();
$tiiuserid = null;

$turnitincomms = new turnitintooltwo_comms();
$api = $turnitincomms->initialise_api();

$users = [];
foreach ($learners as $l) {
    $r = new \stdClass();
    $r->userid = $l->userid;
    $r->tiiuserid = $l->turnitin_uid;
    $r->role = 'Instructor';
    $DB->insert_record('local_plagiarism_turnitin', $r);

    if (count($users) == 100) {
        $user = new TiiUser();
        $user->setUserIds($users);
        $response = $api->readUsers($user);
        $users = $response->getUsers();
        foreach ($users as $u) {
            echo $u->getEmail() . "\n";
        }
        $users = [];
    }
    $users[] = $l->turnitin_uid;
}

if (count($users)) {

    $user = new TiiUser();
    $user->setUserIds($users);
    $response = $api->readUsers($user);
    $users = $response->getUsers();
    foreach ($users as $u) {
        echo $u->getEmail() . "\n";
    }
    $users = [];
}