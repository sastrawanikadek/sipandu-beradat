<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
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
        SELECT k.id_masyarakat, k.id_kerabat 
        FROM kerabat k 
        WHERE k.id = ?
        AND (k.id_masyarakat = ? OR k.id_kerabat = ?)
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
    
    $stmt->bind_result($id_masyarakat, $id_kerabat);
    $stmt->fetch();
    $stmt->close();
    
    if (!$id_masyarakat) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data kerabat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "DELETE FROM kerabat WHERE id = ?";
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
    
    $stmt->close();
    
    $query = "
        DELETE FROM notifikasi_masyarakat
        WHERE id_masyarakat = ?
        AND jenis = '0'
        AND data = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_kerabat, $id_masyarakat);
    
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
        "message" => "Permintaan kerabat telah ditolak"
    ];
    echo json_encode($response);