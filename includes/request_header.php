<?php
$user_agent=json_decode(file_get_contents("/run/secrets/oauth2_notes_reminder"),true)['User-Agent']??'NotesReminderBot/1.0';

    $options = [
        "http" => [
            "header" => "User-Agent: ".$user_agent."\r\n"
        ]
    ];
    $context = stream_context_create($options);