<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["name"]) && $_POST["name"] && isset($_POST["emergency_status"]))) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $name = $_POST["name"];
    $emergency_status = intval(json_decode($_POST["emergency_status"]));
    
    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM jenis_pelaporan jp WHERE jp.nama = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $name);
    
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
    
    $icon = upload_image("icon");
    
    $query = "INSERT INTO jenis_pelaporan VALUES (NULL, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $name, $icon, $emergency_status);

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
            "name" => $name, 
            "icon" => $icon, 
            "emergency_status" => boolval($emergency_status), 
            "active_status" => true
        ],
        "message" => "Data jenis pelaporan berhasil ditambahkan"
    ];

    echo json_encode($response);