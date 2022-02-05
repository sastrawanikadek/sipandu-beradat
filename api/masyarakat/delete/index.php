<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["admin_desa"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] && 
        isset($_POST["active_status_id"]) && $_POST["active_status_id"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    $active_status_id = $_POST["active_status_id"];
    
    $mysqli = connect_db();
    $query = "
        UPDATE masyarakat m 
        SET m.id_status_aktif = ?
        WHERE m.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $active_status_id, $id);
    
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
        "message" => "Data masyarakat berhasil dinonaktifkan"
    ];

    echo json_encode($response);