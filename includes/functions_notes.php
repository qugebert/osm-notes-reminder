<?php


function get_note_for_list($id) {
global $oauth2;

    $result=json_decode(file_get_contents($oauth2['api_base_url'] . "notes/".$id.".json"),true);
    $location=getNoteLocation($id);
    if (!is_array($location))
        $location['display_name']="Unable to geocode location";
    
    return ["url"=>"[Hinweis ".$id."](https://osm.org/note/".$id.")","nearby"=> $location['display_name']];
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
