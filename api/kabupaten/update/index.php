<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_provinsi"]) && $_POST["id_provinsi"] &&
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
    $id_provinsi = $_POST["id_provinsi"];
    $name = $_POST["name"];
    $active_status = intval(json_decode($_POST["active_status"]));

    $mysqli = connect_db();
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif 
        FROM negara n, provinsi p 
        WHERE p.id_negara = n.id
        AND p.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_provinsi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_name, $provinsi_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$provinsi_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data provinsi tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM kabupaten kab WHERE kab.nama = ? AND kab.id_provinsi = ? AND kab.id <> ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $name, $id_provinsi, $id);
    
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
        UPDATE kabupaten kab 
        SET
            kab.id_provinsi = ?,
            kab.nama = ?,
            kab.status_aktif = ? 
        WHERE kab.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $id_provinsi, $name, $active_status, $id);
    
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
            "provinsi" => [
                "id" => $id_provinsi,
                "negara" => [
                    "id" => $negara_id,
                    "name" => $negara_name,
                    "flag" => $negara_flag,
                    "active_status" => boolval($negara_active_status)
                ],
                "name" => $provinsi_name,
                "active_status" => boolval($provinsi_active_status)
            ],
            "name" => $name, 
            "active_status" => boolval($active_status)
        ],
        "message" => "Data kabupaten berhasil diubah"
    ];

    echo json_encode($response);