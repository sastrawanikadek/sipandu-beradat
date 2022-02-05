<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id_negara"]) && $_POST["id_negara"] &&
        isset($_POST["name"]) && $_POST["name"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $id_negara = $_POST["id_negara"];
    $name = $_POST["name"];
    
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
    
    $query = "SELECT COUNT(*) FROM provinsi p WHERE p.nama = ? AND p.id_negara = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $name, $id_negara);
    
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
    
    $query = "INSERT INTO provinsi VALUES (NULL, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_negara, $name);

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
            "negara" => [
                "id" => $id_negara,
                "name" => $negara_name,
                "flag" => $negara_flag,
                "active_status" => boolval($negara_active_status)
            ],
            "name" => $name, 
            "active_status" => true
        ],
        "message" => "Data provinsi berhasil ditambahkan"
    ];

    echo json_encode($response);