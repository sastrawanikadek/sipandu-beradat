<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["admin_petugas"]);
    $user_id = $payload["id"];
    
    $mysqli = connect_db();
    $query = "SELECT ap.status_super_admin FROM admin_petugas ap WHERE ap.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $user_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($super_admin_status);
    $stmt->fetch();
    $stmt->close();
    
    if ($super_admin_status == 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
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
    
    $query = "
        UPDATE admin_petugas ap 
        SET ap.status_aktif = 0
        WHERE ap.id = ?
    ";
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
        "message" => "Data admin instansi berhasil dinonaktifkan"
    ];

    echo json_encode($response);