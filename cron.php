<?php

include "includes/functions_notes.php";
include "includes/functions_db.php";
include "includes/functions_messages.php";
include "includes/functions_nominatim.php";
include "includes/request_header.php";

$config = json_decode(rtrim(file_get_contents("/run/secrets/mysqli_config_notes")), true);
$oauth2=json_decode(file_get_contents("/run/secrets/oauth2_notes_reminder"),true);

$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

if (!file_exists("/usr/src/app/last_query"))
    file_put_contents("/usr/src/app/last_query", "0");

$last = date("c", file_get_contents("/usr/src/app/last_query"));
$api_url="https://api.openstreetmap.org/api/0.6/notes/search.json?q=remindme"; //."&from=".$last; //Geht irgendwie nicht mit from 

if ($api = json_decode(file_get_contents($api_url, false, $context), true)) {

    foreach ($api['features'] as $note) {
        foreach ($note['properties']['comments'] as $comment_id => $comment) {
            preg_match("/\#remindme[: ]([0-9\-]{10})/", $comment['text'], $matches);
            if (isset($matches[1]) && strtotime($matches[1]) && strtotime($matches[1]) > time() ) {
                if (!getNoteLocation($note['properties']['id'])) { //Kein Eintrag in der Datenbank
                    insertNoteLocation($note['properties']['id'],$note['geometry']['coordinates'][1],$note['geometry']['coordinates'][0]); //Von Nominatim holen und cachen
                }
                $comment['uid'] = $comment['uid'] ?? 0;
                insertReminder($note['properties']['id'],$comment_id,$matches[1],$comment['uid']);
            }
        }
    }

    file_put_contents("/usr/src/app/last_query",(time()-3600*24));    
}

$res_today=getTodaysReminder();
if ($res_today->num_rows > 0) {
 while ($row=$res_today->fetch_assoc()) {
    $noteStatus=checkNoteStatus($row['note']);
    if ($noteStatus == "open") 
        commentNote($row['note'],"Here is your reminder as requested");
    else if ($noteStatus == "closed")
        reopenNote($row['note'],"Here is your reminder as requested");
    checkReminder($row['id']);
 }    
}

process_inbox();

