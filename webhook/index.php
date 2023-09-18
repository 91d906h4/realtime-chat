<?php
    //Webhook
    ini_set("display_errors", 0);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Method: *");
    header("Access-Control-Allow-Headers: *");

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $json_data = json_decode(file_get_contents('php://input'), true);
        $jsonfile = "./data.json";
        $json = file_get_contents($jsonfile);
        $dataj = json_decode($json, true);
        foreach($dataj as $key => $value){
            $webhook_url = $value["IP_ADDRESS"];
            $ch = curl_init($webhook_url);
            $data = array(
                "room" => $json_data["room"],
                "user" => $json_data["user"],
                "message" => $json_data["message"],
                "time" => $json_data["time"]
            );
            $payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        }
    }
?>
<?php
    //Website interface
    $hidden = "hidden";
    $hidden1 = "";
    $jsonfile = "./data.json";
    $json = file_get_contents($jsonfile);
    $data = json_decode($json, true);
    foreach($data as $key => $value){
        if($_SERVER["REMOTE_ADDR"].":8080" == $value["IP_ADDRESS"]){
            $hidden = "";
            $hidden1 = "hidden";
            goto label1;
        }
    }
    if(isset($_GET["get_key"]) & $hidden == "hidden"){
        $n_data["IP_ADDRESS"] = $_SERVER["REMOTE_ADDR"].":8080";
        array_push($data, $n_data);
        $newJsonString = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($jsonfile, $newJsonString);
        header("location: 1");
    }
    label1:
?>
<html>
    <head>
        <title>Connect Webhook</title>
        <link rel="stylesheet" type="text/css" href="./style.css" />
    </head>
    <body>
        <h1 style="text-align: center;">Connect Webhook to RTC</h1>
        <div class="wrap">
            <h3 style="margin: 5px;">Register your Webhook host</h3>
            <form action="./" method="GET">
                <input type="submit" class="url_button" style="width: 150px;" name="get_key" value="Register your host" <?php echo $hidden1; ?>>
            </form>
            <input type="input" id="host" onclick="this.select();" value="<?php echo $_SERVER["REMOTE_ADDR"].":8080"; ?>" class="url_inputbox" style="width: 50%;" <?php echo $hidden; ?> readonly="readonly" />
        </div>
        <div class="wrap">
            <h3 style="margin: 5px;">Source</h3>
            <div style="text-align: left;">
                <h3>Download</h3>
                <p>Download <a href="./Webhook-client.zip" download>Webhook-client.zip</a>.</p>
                <p>(For more information pleace download and read the README.txt file in Webhook-client.zip.)</p>
            </div>
        </div>
        <div class="wrap">
            <h3 style="margin: 5px;">About</h3>
            <p>This is a client-hosting webhook.<br>Real Time Chat™ Webhook @ 2022<br>Version 1.0.0</p>
        </div>
    </body>
</html>