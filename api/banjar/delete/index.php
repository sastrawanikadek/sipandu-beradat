<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin", "admin_desa"]);
    
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
        UPDATE banjar b 
        SET b.status_aktif = 0
        WHERE b.id = ?
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
        "message" => "Data banjar berhasil dinonaktifkan"
    ];

    echo json_encode($response);