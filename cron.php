<?php

include "includes/functions_notes.php";
include "includes/functions_db.php";
include "includes/functions_messages.php";
include "includes/functions_nominatim.php";
include "includes/request_header.php";

$keepClosed = ["softremindme"];
$reminderText = "Here is your reminder as requested";

$config = json_decode(rtrim(file_get_contents("/run/secrets/mysqli_config_notes")), true);
$oauth2 = json_decode(file_get_contents("/run/secrets/oauth2_notes_reminder"), true);

$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

fetch_latest_notes("remindme");
fetch_latest_notes("softremindme");

$res_today = getTodaysReminder();
if ($res_today->num_rows > 0) {
    while ($row = $res_today->fetch_assoc()) {
        $noteStatus = checkNoteStatus($row['note']);
        if ($noteStatus == "open")
            commentNote($row['note'], $reminderText);
        else if ($noteStatus == "closed" && !in_array($row['action'], $keepClosed))
            reopenNote($row['note'], $reminderText);
        checkReminder($row['id']);
    }
}

process_inbox();

