<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id_kabupaten"]) && $_POST["id_kabupaten"] &&
        isset($_POST["name"]) && $_POST["name"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $id_kabupaten = $_POST["id_kabupaten"];
    $name = $_POST["name"];
    
    $mysqli = connect_db();
    
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.nama, kab.status_aktif
        FROM negara n, provinsi p, kabupaten kab
        WHERE kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND kab.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_kabupaten);
    
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
        $provinsi_id, $provinsi_name, $provinsi_active_status, $kabupaten_name, 
        $kabupaten_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$kabupaten_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data kabupaten tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM kecamatan kec WHERE kec.nama = ? AND kec.id_kabupaten = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $name, $id_kabupaten);
    
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
    
    $query = "INSERT INTO kecamatan VALUES (NULL, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_kabupaten, $name);

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
            "kabupaten" => [
                "id" => $id_kabupaten,
                "provinsi" => [
                    "id" => $provinsi_id,
                    "negara" => [
                        "id" => $negara_id,
                        "name" => $negara_name,
                        "flag" => $negara_flag,
                        "active_status" => boolval($negara_active_status)
                    ],
                    "name" => $provinsi_name,
                    "active_status" => boolval($provinsi_active_status)
                ],
                "name" => $kabupaten_name,
                "active_status" => boolval($kabupaten_active_status)
            ],
            "name" => $name, 
            "active_status" => true
        ],
        "message" => "Data kecamatan berhasil ditambahkan"
    ];

    echo json_encode($response);