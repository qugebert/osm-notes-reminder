<?php

function get_inbox()
{
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "user/messages/inbox.json");
    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer']
    ];

    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
    $result = json_decode(curl_exec($cl), true);
    return $result['messages'];
    curl_close($cl);

}


function get_message($message_id)
{
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "user/messages/" . intval($message_id) . ".json");

    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer'],
        'Content-Type: application/x-www-form-urlencoded'
    ];
    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);

    $response = json_decode(curl_exec($cl), true);
    $http_code = curl_getinfo($cl, CURLINFO_HTTP_CODE);
    curl_close($cl);

    return $response;
}


function set_message_read($message_id, $read = true)
{
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "user/messages/" . intval($message_id));

    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer'],
        'Content-Type: application/x-www-form-urlencoded'
    ];
    $read_status = $read ? 'true' : 'false';
    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cl, CURLOPT_POSTFIELDS, http_build_query([
        "read_status" => $read_status
    ]));

    $response = curl_exec($cl);
    $http_code = curl_getinfo($cl, CURLINFO_HTTP_CODE);
    curl_close($cl);

    return $http_code === 200;
}

function delete_message($message_id)
{
    global $oauth2;

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $oauth2['api_base_url'] . "user/messages/" . intval($message_id));

    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer'],
        'Content-Type: application/x-www-form-urlencoded'
    ];

    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cl, CURLOPT_CUSTOMREQUEST, "DELETE");

    $response = curl_exec($cl);
    $http_code = curl_getinfo($cl, CURLINFO_HTTP_CODE);
    curl_close($cl);
    return $http_code === 200 || $http_code === 204;
}


function send_message($recipient, $title, $body)
{
    global $oauth2;

    $url = $oauth2['api_base_url'] . "user/messages";
    $post_fields = http_build_query([
        'recipient' => $recipient,
        'title' => $title,
        'body' => $body
    ]);

    $headers = [
        'Authorization: Bearer ' . $oauth2['bearer'],
        'Content-Type: application/x-www-form-urlencoded'
    ];

    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $url);
    curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cl, CURLOPT_POST, true);
    curl_setopt($cl, CURLOPT_POSTFIELDS, $post_fields);

    $response = curl_exec($cl);
    $http_code = curl_getinfo($cl, CURLINFO_HTTP_CODE);
    curl_close($cl);

    return $http_code === 200 || $http_code === 201;
}

function process_inbox()
{
    global $mysqli;
    foreach (get_inbox() as $msg) {
        if ($msg['deleted'] == 0 && $msg['message_read'] == 0) {
            switch ($msg['title']) {
                case "LIST":
                    send_list($msg['from_user_id'], $msg);
                    break;
            }
        }
    }

}

function send_list($user, $msg)
{

    $res = getOpenNotesByUser($user);
    if ($res->num_rows > 0) {

        $str = "| Datum | Hinweis | Ort |
|:--------|:-------:|-------:|\n";
        while ($row = $res->fetch_assoc()) {
            $detail = get_note_for_list($row['note']);
            $str .= "|  " . $row['date'] . "   | " . $detail['url'] . " | " . $detail['nearby'] . "   |\n";
        }

        send_message($msg['from_display_name'], "Re: LIST", $str);

    }
    else
        send_message($msg['from_display_name'],"Re: LIST","Für diesen Nutzer sind keine Erinnerungen vorgemerkt");

    set_message_read($msg['id']);
    delete_message($msg['id']);
}


?>