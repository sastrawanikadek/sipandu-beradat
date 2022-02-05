<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $id_masyarakat = $payload["id"];

    $mysqli = connect_db();
    $query = "SELECT pda.id FROM pecalang pda WHERE pda.status_aktif = 1 AND pda.id_masyarakat = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_masyarakat);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }

    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    if (!$id) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_POST["latitude"]) && $_POST["latitude"] &&
        isset($_POST["longitude"]) && $_POST["longitude"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    
    $query = "
        UPDATE pecalang pda
        SET pda.latitude = ?,
            pda.longitude = ?
        WHERE pda.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $latitude, $longitude, $id);
    
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
        "message" => "Lokasi pecalang desa adat berhasil diperbaharui"
    ];
    echo json_encode($response);