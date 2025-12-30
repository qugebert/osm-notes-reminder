<?php


function fetch_latest_notes($keyword = "remindme")
{
    global $context;

    $api_url = "https://api.openstreetmap.org/api/0.6/notes/search.json?q=" . $keyword;
    if ($api = json_decode(file_get_contents($api_url, false, $context), true)) {

        foreach ($api['features'] as $note) {
            foreach ($note['properties']['comments'] as $comment_id => $comment) {
                preg_match("/\#" . $keyword . "[: ]([0-9\-]{10})/", $comment['text'], $matches);
                if (isset($matches[1]) && strtotime($matches[1]) && strtotime($matches[1]) > time()) {
                    if (!getNoteLocation($note['properties']['id'])) { //Kein Eintrag in der Datenbank
                        insertNoteLocation($note['properties']['id'], $note['geometry']['coordinates'][1], $note['geometry']['coordinates'][0]); //Von Nominatim holen und cachen
                    }
                    $comment['uid'] = $comment['uid'] ?? 0;
                    insertReminder($note['properties']['id'], $comment_id, $matches[1], $comment['uid'], $keyword);
                }
            }
        }
    }

}

function get_note_for_list($id)
{
    global $oauth2;

    $result = json_decode(file_get_contents($oauth2['api_base_url'] . "notes/" . $id . ".json"), true);
    $location = getNoteLocation($id);
    if (!is_array($location))
        $location['display_name'] = "Unable to geocode location";

    return ["url" => "[Hinweis " . $id . "](https://osm.org/note/" . $id . ")", "nearby" => $location['display_name']];
}


function checkNoteStatus($id)
{
    global $oauth2;
    if (!$file = @file_get_contents($oauth2['api_base_url'] . "notes/" . $id . ".json"))
        return "404";
    $note = json_decode($file, true);

    //Zur Sicherheit prüfen, ob heute schon erinnert, damit es parallel laufen kann.
    foreach ($note['properties']['comments'] as $comment) {
        if ($comment['uid'] == $oauth2['osm-user-id']) {
            $isToday = (new DateTime($comment['date']))->format('Y-m-d') === (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
            if ($isToday && in_array($comment['action'] ?? '', ["commented", "reopened"]))
                return "reminded";
        }
    }
    return $note['properties']['status'];

}

function commentNote($id, $text)
{
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "notes/" . $id . "/comment");
    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer']
    ];

    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cl, CURLOPT_POST, true);
    curl_setopt($cl, CURLOPT_POSTFIELDS, http_build_query([
        "text" => $text,
    ]));
    $result = curl_exec($cl);
    curl_close($cl);
    //return is_numeric($changeset_id) ? $changeset_id : false;

}

function reopenNote($id, $text)
{
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "notes/" . $id . "/reopen");
    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer']
    ];

    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cl, CURLOPT_POST, true);
    curl_setopt($cl, CURLOPT_POSTFIELDS, http_build_query([
        "text" => $text,
    ]));
    $result = curl_exec($cl);
    curl_close($cl);
    //return is_numeric($changeset_id) ? $changeset_id : false;

}
