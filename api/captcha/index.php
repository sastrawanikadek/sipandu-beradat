<?php
    require_once("../config.php");

    $json_string = file_get_contents("https://python-captcha.herokuapp.com/");
    $json_data = json_decode($json_string);

    if ($json_data->status_code != 200) {
	$response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Gagal membuat captcha" 
        ];
        
        echo json_encode($response);
        exit();
    }
    
    $captcha = $json_data->data->captcha;
    $secure_url = $json_data->data->img;
    
    $date = date_create();
    date_add($date, date_interval_create_from_date_string("10 minutes"));
    $datetime_string = date_format($date, "Y-m-d H:i:s");
    
    $mysqli = connect_db();
    $query = "INSERT INTO captcha VALUES (NULL, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $captcha, $datetime_string);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        
        echo json_encode($response);
        exit();
    }
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $mysqli->insert_id,
            "url" => $secure_url
        ],
        "message" => "Captcha berhasil dibuat"
    ];
    
    echo json_encode($response);