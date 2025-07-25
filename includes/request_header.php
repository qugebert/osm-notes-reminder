
<?php

    $options = [
        "http" => [
            "header" => "User-Agent: OSMNotesReminder/1.0 (osm-bot@qugeb.de;https://osm.org/user/NotesReminderBot;https://github.com/qugebert/osm-notes-reminder/)\r\n"
        ]
    ];
    $context = stream_context_create($options);