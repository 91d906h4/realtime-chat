<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Method: *");
    header("Access-Control-Allow-Headers: *");

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $DATA_FROM_SERVER = json_decode(file_get_contents('php://input'), true);
        $JSON_FILE = "./data.json";
        $JSON = file_get_contents($JSON_FILE);
        $DATA = json_decode($JSON, true);
        $NEW_DATA["room"] = $DATA_FROM_SERVER["room"];
        $NEW_DATA["user"] = $DATA_FROM_SERVER["user"];
        $NEW_DATA["message"] = $DATA_FROM_SERVER["message"];
        $NEW_DATA["time"] = $DATA_FROM_SERVER["time"];
        array_push($DATA, $NEW_DATA);
        $NEW_JSON = json_encode($DATA, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($JSON_FILE, $NEW_JSON);
    }
?>