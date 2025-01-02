<?php

if(!file_exists("/usr/src/app/last_query"))
    file_put_contents("/usr/src/app/last_query","0");

$last=date("c",file_get_contents("/usr/src/app/last_query"));

if ($api=json_decode(file_get_contents("https://api.openstreetmap.org/api/0.6/notes/search.json?q=remindme&user=339078&from=".$last),true)) {
    /*
    foreach ($api['features'] as $note) {
        $notes_id=$note['properties']['id'];
        $closed=isset($note['properties']['closed_at'])?substr($note['properties']['closed_at'],0,-4):NULL;
        
        foreach ($note['properties']['comments'] as $comment) {
            checkForImages($notes_id,$closed,$comment['text']);
        }
    }

    */
}

file_put_contents("/usr/src/app/test",$api);
?>