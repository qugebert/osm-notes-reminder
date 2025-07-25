<?php


function callNominatim($lat,$lon) {
    global $context;
    $url = "https://nominatim.osm.org/reverse?lat=$lat&lon=$lon&format=json";

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return false;
    }

    return json_decode($response, true);

}