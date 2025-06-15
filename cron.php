<?php

include "includes/functions_notes.php";

$config = json_decode(rtrim(file_get_contents("/run/secrets/mysqli_config_notes")), true);
$oauth2=json_decode(file_get_contents("/run/secrets/oauth2_notes_reminder"),true);

$now=time();

$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

if (!file_exists("/usr/src/app/last_query"))
    file_put_contents("/usr/src/app/last_query", "0");

$last = date("c", file_get_contents("/usr/src/app/last_query"));
$api_url="https://api.openstreetmap.org/api/0.6/notes/search.json?q=remindme"; //."&from=".$last; //Geht irgendwie nicht mit from 

if ($api = json_decode(file_get_contents($api_url), true)) {

    foreach ($api['features'] as $note) {
        foreach ($note['properties']['comments'] as $comment_id => $comment) {
            preg_match("/\#remindme\ ([0-9\-]{10})/", $comment['text'], $matches);
            if (isset($matches[1]) && strtotime($matches[1]) && strtotime($matches[1]) > time() ) {
                $queryExists = $mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `note` = (?) AND `comment` = (?) ");
                $queryExists->bind_param("ii", $note['properties']['id'], $comment_id);
                $queryExists->execute();
                $res = $queryExists->get_result();
                if ($res->num_rows == 0) {
                    $insertquery = $mysqli->prepare("INSERT INTO `reminder_bot` (`id`, `note`, `comment`, `date`,`user`, `done`) VALUES (NULL, (?), (?), (?), (?), '0'); ");
                    $insertquery->bind_param("iisi", $note['properties']['id'], $comment_id, $matches[1],$comment['uid']);
                    $insertquery->execute();
                }

            }
        }
    }

    file_put_contents("/usr/src/app/last_query",(time()-3600*24));    
}


$today_query=$mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `date` = CURDATE() AND `done`=0; ");
$today_query->execute();
$res_today=$today_query->get_result();
if ($res_today->num_rows > 0) {
 while ($row=$res_today->fetch_assoc()) {

    $noteStatus=checkNoteStatus($row['note']);
    if ($noteStatus == "open") 
        commentNote($row['note'],"Here is your reminder as requested");
    else if ($noteStatus == "closed")
        reopenNote($row['note'],"Here is your reminder as requested");
    $update=$mysqli->prepare("UPDATE `reminder_bot` SET `done`= (?) WHERE `id`=(?)");
    $update->bind_param("ii",$now,$row['id']);
    $update->execute();
 }    
}
