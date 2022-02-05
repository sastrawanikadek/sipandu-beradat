<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_negara"]) && $_POST["id_negara"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["active_status"]))) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    $id_negara = $_POST["id_negara"];
    $name = $_POST["name"];
    $active_status = intval(json_decode($_POST["active_status"]));

    $mysqli = connect_db();
    $query = "SELECT n.nama, n.bendera, n.status_aktif FROM negara n WHERE n.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_negara);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_name, $negara_flag, $negara_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$negara_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data negara tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM provinsi p WHERE p.nama = ? AND p.id_negara = ? AND p.id <> ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $name, $id_negara, $id);
    
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
    
    if ($total > 0) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data sudah ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "
        UPDATE provinsi p 
        SET
            p.id_negara = ?,
            p.nama = ?,
            p.status_aktif = ? 
        WHERE p.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $id_negara, $name, $active_status, $id);
    
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
        "data" => [
            "id" => $id,
            "negara" => [
                "id" => $id_negara,
                "name" => $negara_name,
                "flag" => $negara_flag,
                "active_status" => boolval($negara_active_status)
            ],
            "name" => $name, 
            "active_status" => boolval($active_status)
        ],
        "message" => "Data provinsi berhasil diubah"
    ];

    echo json_encode($response);