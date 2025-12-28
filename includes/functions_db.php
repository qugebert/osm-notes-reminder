<?php

function getOpenNotesByUser($user)
{
    global $mysqli;
    $query = $mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `user` = (?) AND `done` = 0 ORDER BY `reminder_bot`.`date` ASC ");
    $query->bind_param("i", $user);
    $query->execute();
    return $query->get_result();

}

function getTodaysReminder()
{
    global $mysqli;
    $today_query = $mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `date` = CURDATE() AND `done`=0; ");
    $today_query->execute();
    return $today_query->get_result();
}

function checkReminder($id)
{
    global $mysqli;
    $now = time();
    $update = $mysqli->prepare("UPDATE `reminder_bot` SET `done`= (?) WHERE `id`=(?)");
    $update->bind_param("ii", $now, $id);
    $update->execute();
}

function insertReminder($note_id, $comment_id, $date, $user_id, $action="remindme")
{
    global $mysqli;
    $queryExists = $mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `note` = (?) AND `date` = (?) AND `done` != 0 ");
    $queryExists->bind_param("is", $note_id, $date);
    $queryExists->execute();
    $res = $queryExists->get_result();
    if ($res->num_rows == 0) {
        $insertquery = $mysqli->prepare("INSERT INTO `reminder_bot` (`id`, `note`, `comment`, `date`,`user`, `done`, `action`) VALUES (NULL, (?), (?), (?), (?), '0', (?)); ");
        $insertquery->bind_param("iisis", $note_id, $comment_id, $date, $user_id, $action);
        $insertquery->execute();
    } else {
        $row=$res->fetch_assoc();
        if ($action == "remindme" && $row['action'] == "softremindme") {
            check_reminder($row['id']);
            $insertquery = $mysqli->prepare("INSERT INTO `reminder_bot` (`id`, `note`, `comment`, `date`,`user`, `done`, `action`) VALUES (NULL, (?), (?), (?), (?), '0', (?)); ");
            $insertquery->bind_param("iisis", $note_id, $comment_id, $date, $user_id, $action);
            $insertquery->execute();
        }
    }

}

function getNoteLocation($note_id) {
    global $mysqli;
    $queryLocation = $mysqli->prepare("SELECT * FROM `note_location` WHERE `note` = (?);");
    $queryLocation->bind_param("i", $note_id);
    $queryLocation->execute();
    $res = $queryLocation->get_result();
    if ($res -> num_rows == 0) {
        return false;
    }
    return $res->fetch_assoc();

}

function insertNoteLocation($note_id,$lat,$lon) {
    global $mysqli;
    
    $nominatim = callNominatim($lat,$lon);
    if (!$nominatim) 
        return false;
    $address=$nominatim['address'] ?? [];
    $insertParams = [
    $note_id,
    $lat,
    $lon,
    $nominatim['display_name'] ?? null,
    $address['country'] ?? null,
    $address['country_code'] ?? null,
    $address['state'] ?? null,
    $address['region'] ?? null,
    $address['county'] ?? null,
    $address['city'] ?? null,
    $address['town'] ?? null,
    $address['village'] ?? null,
    $address['suburb'] ?? null,
    $address['postcode'] ?? null,
    json_encode($nominatim)
];
    $insertQuery=$mysqli->prepare("INSERT INTO `note_location` (`note`, `lat`, `lon`, `display_name`, `country`, `country_code`, `state`, `region`, `county`,
     `city`, `town`, `village`, `suburb`, `postcode`, `address_json`) 
     VALUES 
     ((?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?));");
     $insertQuery->bind_param("iddssssssssssss",...$insertParams);
     $insertQuery->execute();

    $queryLocation = $mysqli->prepare("SELECT * FROM `note_location` WHERE `note` = (?);");
    $queryLocation->bind_param("i", $note_id);
    $queryLocation->execute();
    $res = $queryLocation->get_result();
    if ($res -> num_rows == 0) {
        return false;
    }
    return $res->fetch_assoc();

}
