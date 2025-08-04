<?php
$config = json_decode(rtrim(file_get_contents("/run/secrets/mysqli_config_notes")), true);
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
$file="/var/stats/".date(format: "Y-m-d").".md";
$md="";

function kramdown_table($result, $headers) {
    $output = "| " . implode(" | ", $headers) . " |\n";    
    $output .= "| " . str_repeat("--- | ", count($headers)) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        $output .= "| " . implode(" | ", $row) . " |\n";
    }
    
    return $output;
}


// 1. Nutzer gesamt
$total_users_query = "SELECT COUNT(DISTINCT user) AS total_users FROM reminder_bot";
$total_users_result = $mysqli->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'];

// 2. Verarbeitete Notes (done != 0)
$processed_notes_query = "SELECT COUNT(*) AS processed_notes FROM reminder_bot WHERE done != 0";
$processed_notes_result = $mysqli->query($processed_notes_query);
$processed_notes = $processed_notes_result->fetch_assoc()['processed_notes'];

// 3. Offene Notes (done = 0)
$open_notes_query = "SELECT COUNT(*) AS open_notes FROM reminder_bot WHERE done = 0";
$open_notes_result = $mysqli->query($open_notes_query);
$open_notes = $open_notes_result->fetch_assoc()['open_notes'];

// 4. Räumliche Verteilung - Länder
$country_query = "SELECT country, COUNT(*) AS country_count FROM note_location GROUP BY country ORDER BY COUNT(*) DESC";
$country_result = $mysqli->query($country_query);

// 5. Räumliche Verteilung - Bundesländer in Deutschland
$state_query = "SELECT state, COUNT(*) AS state_count FROM note_location WHERE country = 'Deutschland' GROUP BY state ORDER BY COUNT(*) DESC";
$state_result = $mysqli->query($state_query);



$md.= "## Statistiken (Stand ".date("d.m.Y").")\n\n";
$md .= "Nutzer: $total_users\n\n";
$md .= "Erledigte Erinnerungen: $processed_notes\n\n";
$md .= "Offene Erinnerungen: $open_notes\n\n";

$md .= "### Räumliche Verteilung der Hinweise\n\n";

$md .= "#### Länder\n\n";
$md .= kramdown_table($country_result, ['Land', 'Anzahl']) . "\n";

$md .= "#### Deutschland\n\n";
$md .= kramdown_table($state_result, ['Bundesland', 'Anzahl']) . "\n";

file_put_contents($file,$md);
?>