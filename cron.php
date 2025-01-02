<?php

function send_tg_msg($txt)
{  
global $tg_config;
$params=[
'chat_id'=>$tg_config['tgr_user'],
'text'=> $txt
];
$req_uri="https://api.telegram.org/bot".$tg_config['tgr_key']."/sendMessage?".http_build_query($params);
file_get_contents($req_uri);
}

function checkNoteStatus($id) {
    global $oauth2;
    if (!$file=@file_get_contents($oauth2['api_base_url']."notes/".$id.".json"))
        return "404";
    $note=json_decode($file,true);
    return $note['properties']['status'];

}

function commentNote($id,$text) {
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "notes/".$id."/comment");
    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer']
    ];

    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($cl, CURLOPT_POST, true);
    curl_setopt($cl, CURLOPT_POSTFIELDS, http_build_query([
        "text" => $text, 
    ]));
    $result=curl_exec($cl);
    curl_close($cl);
    //return is_numeric($changeset_id) ? $changeset_id : false;

}

function reopenNote($id,$text) {
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "notes/".$id."/reopen");
    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer']
    ];

    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($cl, CURLOPT_POST, true);
    curl_setopt($cl, CURLOPT_POSTFIELDS, http_build_query([
        "text" => $text, 
    ]));
    $result=curl_exec($cl);
    curl_close($cl);
    //return is_numeric($changeset_id) ? $changeset_id : false;

}


$config = json_decode(rtrim(file_get_contents("/run/secrets/mysqli_config_notes")), true);
$oauth2=json_decode(file_get_contents("/run/secrets/oauth2_notes_reminder"),true);

$tg_config=['tgr_user'=>rtrim(file_get_contents("/run/secrets/tgr_user")),'tgr_key'=>rtrim(file_get_contents("/run/secrets/tgr_api_token"))];
$now=time();

$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

if (!file_exists("/usr/src/app/last_query"))
    file_put_contents("/usr/src/app/last_query", "0");

$last = date("c", file_get_contents("/usr/src/app/last_query"));

if ($api = json_decode(file_get_contents("https://api.openstreetmap.org/api/0.6/notes/search.json?q=remindme&user=339078&from=" . $last), true)) {

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

    file_put_contents("/usr/src/app/last_query",time());    
}


$today_query=$mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `date` = CURDATE() AND `done`=0; ");
$today_query->execute();
$res_today=$today_query->get_result();
if ($res_today->num_rows > 0) {
 while ($row=$res_today->fetch_assoc()) {

    if ($row['user']=="339078")
    send_tg_msg("https://osm.org/note/".$row['note']);

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