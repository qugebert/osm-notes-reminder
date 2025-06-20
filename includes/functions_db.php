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

function insertReminder($note_id, $comment_id, $date, $user_id)
{
    global $mysqli;
    $queryExists = $mysqli->prepare("SELECT * FROM `reminder_bot` WHERE `note` = (?) AND `comment` = (?) ");
    $queryExists->bind_param("ii", $note_id, $comment_id);
    $queryExists->execute();
    $res = $queryExists->get_result();
    if ($res->num_rows == 0) {
        $insertquery = $mysqli->prepare("INSERT INTO `reminder_bot` (`id`, `note`, `comment`, `date`,`user`, `done`) VALUES (NULL, (?), (?), (?), (?), '0'); ");
        $insertquery->bind_param("iisi", $note_id, $comment_id, $date, $user_id);
        $insertquery->execute();
    }

}