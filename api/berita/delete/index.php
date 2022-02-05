<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    $payload = decode_jwt_token(["admin_desa", "admin_akomodasi"]);
    $id = $payload["id"];
    $role = $payload["role"];
    
    if (!(isset($_POST["id"]) && $_POST["id"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $news_id = $_POST["id"];
    
    $mysqli = connect_db();
    $table_name = $role == "admin_desa" ? "berita_desa_adat" : "berita_akomodasi";
    
    $query = "
        UPDATE $table_name t
        SET t.status_aktif = 0
        WHERE t.id = ?
        AND t.id_$role = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $news_id, $id);
    
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
        "message" => "Data berita berhasil dihapus"
    ];

    echo json_encode($response);