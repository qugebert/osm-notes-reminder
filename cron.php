<?php

include "includes/functions_notes.php";
include "includes/functions_db.php";
include "includes/functions_messages.php";
include "includes/functions_nominatim.php";
include "includes/request_header.php";
include "includes/lang.php";

$keepClosed = ["softremindme"];
$reminderText = "Here is your reminder as requested";

$oauth2 = json_decode(file_get_contents("/run/secrets/oauth2_notes_reminder"), true);

$config = json_decode(rtrim(file_get_contents("/run/secrets/postgis_config_notes")), true);
$dsn = "pgsql:host={$config['host']};port=5432;dbname={$config['db']}";
$pdo = new PDO($dsn, $config['user'], $config['pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

fetch_latest_notes("remindme");
fetch_latest_notes("softremindme");

$rows_today = getTodaysReminder();
foreach ($rows_today as $row) {
    $noteStatus = checkNoteStatus($row['note']);
    $details = getNoteDetails($row['note']);
    $country_code = $details['nominatim']['address']['country_code'] ?? null;
    if ($country_code && isset($comment_text[$country_code]))
        $reminderText = $comment_text[$country_code];
    if ($noteStatus == "open")
        commentNote($row['note'], $reminderText);
    else if ($noteStatus == "closed" && !in_array($row['action'], $keepClosed))
        reopenNote($row['note'], $reminderText);
    checkReminder($row['id']);
}

process_inbox();