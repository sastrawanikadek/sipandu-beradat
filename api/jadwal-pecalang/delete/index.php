<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["admin_desa"]);
    
    if (!(isset($_POST["id_pecalang"]) && $_POST["id_pecalang"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id_pecalang = $_POST["id_pecalang"];
    
    $mysqli = connect_db();
    $query = "
        DELETE FROM jadwal_pecalang
        WHERE id_pecalang = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pecalang);
    
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
        "message" => "Data jadwal pecalang desa adat berhasil dihapus"
    ];

    echo json_encode($response);