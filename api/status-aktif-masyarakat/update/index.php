<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["status"]) && isset($_POST["active_status"]))) {
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
    $status = intval(json_decode($_POST["status"]));
    $active_status = intval(json_decode($_POST["active_status"]));
    
    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM status_aktif_masyarakat sam WHERE sam.nama = ? AND sam.id <> ?";
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
        UPDATE status_aktif_masyarakat sam
        SET sam.nama = ?,
            sam.status = ?,
            sam.status_aktif = ?
        WHERE sam.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $name, $status, $active_status, $id);

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
        "data" => ["id" => $id, "name" => $name, "status" => boolval($status), "active_status" => boolval($active_status)],
        "message" => "Data status aktif masyarakat berhasil diubah"
    ];

    echo json_encode($response);