<?php
// Client per effettuare chiamate HTTP alle API REST anziché interrogare direttamente il DB nelle pagine web
function callAPI($method, $url, $data = false) {
    $curl = curl_init();
    switch (strtoupper($method)) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, true);
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Accept: application/json'
    ]);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}
