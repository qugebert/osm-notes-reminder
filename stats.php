<?php
$config = json_decode(rtrim(file_get_contents("/run/secrets/postgis_config_notes")), true);
$dsn = "pgsql:host={$config['host']};port=5432;dbname={$config['db']}";
$pdo = new PDO($dsn, $config['user'], $config['pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$file = "/var/stats/" . date("Y-m-d") . ".md";
$md = "";

function kramdown_table($rows, $headers) {
    $output = "| " . implode(" | ", $headers) . " |\n";
    $output .= "| " . str_repeat("--- | ", count($headers)) . "\n";
    foreach ($rows as $row) {
        $output .= "| " . implode(" | ", $row) . " |\n";
    }
    return $output;
}

// 1. Nutzer gesamt
$total_users = $pdo->query('SELECT COUNT(DISTINCT user_id) AS total_users FROM bot.reminder_bot')
    ->fetch()['total_users'];

// 2. Erledigte Erinnerungen (done IS NOT NULL)
$processed_notes = $pdo->query('SELECT COUNT(*) AS processed_notes FROM bot.reminder_bot WHERE done IS NOT NULL')
    ->fetch()['processed_notes'];

// 3. Offene Erinnerungen (done IS NULL)
$open_notes = $pdo->query('SELECT COUNT(*) AS open_notes FROM bot.reminder_bot WHERE done IS NULL')
    ->fetch()['open_notes'];

    // 4. Räumliche Verteilung - Länder
$country_rows = $pdo->query("
    SELECT nominatim->'address'->>'country' AS country, COUNT(*) AS country_count
    FROM bot.note_details
    GROUP BY nominatim->'address'->>'country'
    ORDER BY COUNT(*) DESC
")->fetchAll();

// 5. Räumliche Verteilung - Bundesländer in Deutschland
$state_rows = $pdo->query("
    SELECT nominatim->'address'->>'state' AS state, COUNT(*) AS state_count
    FROM bot.note_details
    WHERE nominatim->'address'->>'country_code' = 'de'
    GROUP BY nominatim->'address'->>'state'
    ORDER BY COUNT(*) DESC
")->fetchAll();

$md .= "## Statistiken (Stand " . date("d.m.Y") . ")\n\n";
$md .= "Nutzer: $total_users\n\n";
$md .= "Erledigte Erinnerungen: $processed_notes\n\n";
$md .= "Offene Erinnerungen: $open_notes\n\n";
$md .= "### Räumliche Verteilung der Hinweise\n\n";
$md .= "#### Länder\n\n";
$md .= kramdown_table($country_rows, ['Land', 'Anzahl']) . "\n";
$md .= "#### Deutschland\n\n";
$md .= kramdown_table($state_rows, ['Bundesland', 'Anzahl']) . "\n";

file_put_contents($file, $md);