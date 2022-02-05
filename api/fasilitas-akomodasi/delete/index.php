<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["admin_akomodasi"]);
    
    if (!(isset($_POST["id_akomodasi"]) && $_POST["id_akomodasi"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id_akomodasi = $_POST["id_akomodasi"];
    
    $mysqli = connect_db();
    $query = "DELETE FROM detail_fasilitas WHERE id_akomodasi = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_akomodasi);
    
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
        "message" => "Data fasilitas akomodasi berhasil dihapus"
    ];

    echo json_encode($response);