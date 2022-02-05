<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["tamu"]);
    $user_id  = $payload["id"];
    
    if (!(isset($_POST["id"]) && $_POST["id"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    
    $mysqli = connect_db();
    $query = "
        SELECT COUNT(*) 
        FROM kerabat_tamu kt 
        WHERE kt.id = ?
        AND (kt.id_tamu = ? OR kt.id_kerabat = ?)
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id, $user_id, $user_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    
    if ($total == 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data kerabat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "DELETE FROM kerabat_tamu WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id);
    
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
        "data" => null,
        "message" => "Family request has been declined"
    ];
    echo json_encode($response);