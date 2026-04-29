<?php

function getOpenNotesByUser($user)
{
    global $pdo;
    $query = $pdo->prepare('
        SELECT * FROM bot.reminder_bot 
        WHERE user_id = :user AND done IS NULL 
        ORDER BY reminder_date ASC
    ');
    $query->execute([':user' => $user]);
    return $query->fetchAll();
}

function getTodaysReminder()
{
    global $pdo;
    $query = $pdo->prepare('
        SELECT * FROM bot.reminder_bot 
        WHERE reminder_date = CURRENT_DATE AND done IS NULL
    ');
    $query->execute();
    return $query->fetchAll();
}

function checkReminder($id)
{
    global $pdo;
    $update = $pdo->prepare('
        UPDATE bot.reminder_bot 
        SET done = NOW() 
        WHERE id = :id
    ');
    $update->execute([':id' => $id]);
}

function insertReminder($note_id, $comment_id, $date, $user_id, $action = 'remindme')
{
    global $pdo;
    $queryExists = $pdo->prepare('
        SELECT * FROM bot.reminder_bot 
        WHERE note = :note AND reminder_date = :date AND action = :action
    ');
    $queryExists->execute([
        ':note'   => $note_id,
        ':date'   => $date,
        ':action' => $action,
    ]);
    $row = $queryExists->fetch();

    if (!$row) {
        $insert = $pdo->prepare('
            INSERT INTO bot.reminder_bot (note, comment_id, reminder_date, user_id, action)
            VALUES (:note, :comment, :date, :user, :action)
        ');
        $insert->execute([
            ':note'    => $note_id,
            ':comment' => $comment_id,
            ':date'    => $date,
            ':user'    => $user_id,
            ':action'  => $action,
        ]);
    } else {
        if ($action === 'remindme' && $row['action'] === 'softremindme') {
            checkReminder($row['id']);
            $insert = $pdo->prepare('
                INSERT INTO bot.reminder_bot (note, comment_id, reminder_date, user_id, action)
                VALUES (:note, :comment, :date, :user, :action)
            ');
            $insert->execute([
                ':note'    => $note_id,
                ':comment' => $comment_id,
                ':date'    => $date,
                ':user'    => $user_id,
                ':action'  => $action,
            ]);
        }
    }
}

function getNoteDetails($note_id)
{
    global $pdo;
    $query = $pdo->prepare('
        SELECT 
            note,
            ST_Y(geom::geometry) AS lat,
            ST_X(geom::geometry) AS lon,
            nominatim,
            data
        FROM bot.note_details 
        WHERE note = :note
    ');
    $query->execute([':note' => $note_id]);
    $row = $query->fetch();
    if (!$row) return false;

    $row['nominatim'] = $row['nominatim'] ? json_decode($row['nominatim'], true) : null;
    $row['data']      = $row['data']      ? json_decode($row['data'], true)      : null;
    return $row;
}

function insertOrUpdateNoteDetails($note_id, $lat, $lon, $noteApiData)
{
    global $pdo;

    $existing = getNoteDetails($note_id);
    if ($existing) {
        $nominatimJson = json_encode($existing['nominatim']);
    } else {
        $nominatim = callNominatim($lat, $lon);
        if (!$nominatim) return false;
        $nominatimJson = json_encode($nominatim);
    }

    $dataJson = json_encode($noteApiData);
    $wkt      = "POINT($lon $lat)";

    $query = $pdo->prepare('
        INSERT INTO bot.note_details (note, geom, nominatim, data)
        VALUES (:note, ST_GeogFromText(:wkt), :nominatim::jsonb, :data::jsonb)
        ON CONFLICT (note) DO UPDATE 
            SET data = EXCLUDED.data
    ');
    $query->execute([
        ':note'      => $note_id,
        ':wkt'       => $wkt,
        ':nominatim' => $nominatimJson,
        ':data'      => $dataJson,
    ]);

    return getNoteDetails($note_id);
}