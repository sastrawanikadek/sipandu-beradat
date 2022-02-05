<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id_provinsi"]) && $_POST["id_provinsi"] &&
        isset($_POST["name"]) && $_POST["name"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $id_provinsi = $_POST["id_provinsi"];
    $name = $_POST["name"];
    
    $mysqli = connect_db();
    
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif 
        FROM negara n, provinsi p 
        WHERE p.id_negara = n.id
        AND p.id = ?
    ";
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
    
    $query = "SELECT COUNT(*) FROM kabupaten kab WHERE kab.nama = ? AND kab.id_provinsi = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $name, $id_provinsi);
    
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
    
    $query = "INSERT INTO kabupaten VALUES (NULL, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_provinsi, $name);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
            
    $id = $mysqli->insert_id;
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
            "active_status" => true
        ],
        "message" => "Data kabupaten berhasil ditambahkan"
    ];

    echo json_encode($response);