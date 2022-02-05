<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["tamu"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["latitude"]) && $_POST["latitude"] &&
        isset($_POST["longitude"]) && $_POST["longitude"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    $now = date("Y-m-d H:i:s");
    
    $mysqli = connect_db();
    $query = "INSERT INTO riwayat_lokasi_tamu VALUES (NULL, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $id, $latitude, $longitude, $now);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Data lokasi telah berhasil ditambahkan"
    ];
    echo json_encode($response);