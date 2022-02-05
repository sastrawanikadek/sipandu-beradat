<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["name"]) && $_POST["name"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $name = $_POST["name"];
    
    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM fasilitas f WHERE f.nama = ?";
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
    
    $query = "INSERT INTO fasilitas VALUES (NULL, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $icon, $name);

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
        "data" => ["id" => $id, "icon" => $icon, "name" => $name, "active_status" => true],
        "message" => "Data fasilitas berhasil ditambahkan"
    ];

    echo json_encode($response);