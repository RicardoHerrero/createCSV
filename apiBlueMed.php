<?php

include 'config.php';
$cardOrCpf = $_GET['cardOrCpf'];

if (isset($cardOrCpf) && !empty($cardOrCpf)) {

    $apiUrl = $configAPI['url'].'?cardOrCpf='.$cardOrCpf;
    $token = $configAPI['token'];

    $ch = curl_init($apiUrl);
    $headers = [
        'token: ' . $token,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro na solicitação cURL: ' . curl_error($ch);
    }

    curl_close($ch);
    echo $response;
    exit;
}

header("HTTP/1.1 500 Internal Server Error");
exit;