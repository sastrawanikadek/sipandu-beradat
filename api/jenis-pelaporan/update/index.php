<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["emergency_status"]) && isset($_POST["active_status"]))) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    $name = $_POST["name"];
    $emergency_status = intval(json_decode($_POST["emergency_status"]));
    $active_status = intval(json_decode($_POST["active_status"]));

    $mysqli = connect_db();
    
    if (isset($_FILES["icon"])) {
        $icon = upload_image("icon");
    } else {
        $query = "SELECT jp.icon FROM jenis_pelaporan jp WHERE jp.id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $id);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500, 
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
        
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($icon);
        $stmt->fetch();
        $stmt->close();
    }
    
    $query = "SELECT COUNT(*) FROM jenis_pelaporan jp WHERE jp.nama = ? AND jp.id <> ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $name, $id);
    
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
        UPDATE jenis_pelaporan jp 
        SET jp.nama = ?,
            jp.icon = ?,
            jp.status_darurat = ?,
            jp.status_aktif = ? 
        WHERE jp.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $name, $icon, $emergency_status, $active_status, $id);
    
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
            "name" => $name, 
            "icon" => $icon, 
            "emergency_status" => boolval($emergency_status),
            "active_status" => boolval($active_status)
        ],
        "message" => "Data jenis pelaporan berhasil diubah"
    ];

    echo json_encode($response);