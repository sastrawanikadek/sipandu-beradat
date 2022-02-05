<?php
    require_once("../config.php");
    require_once("../helpers/jwt.php");

    $payload = decode_jwt_token(["admin_akomodasi", "admin_desa", "admin_petugas", "masyarakat", "petugas", "superadmin", "tamu"]);
    $id = $payload["id"];
    $role = $payload["role"];
    
    $mysqli = connect_db();
    $query = "DELETE FROM access_token_$role WHERE id_$role = ?";
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
    
    $query = "DELETE FROM refresh_token_$role WHERE id_$role = ?";
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
    
    $response = [
        "status_code" => 200, 
        "data" => null,
        "message" => "Proses logout berhasil dilakukan"
    ];

    echo json_encode($response);