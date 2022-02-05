<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
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
    $name = $_POST["name"];
    $active_status = intval(json_decode($_POST["active_status"]));

    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM jenis_instansi_petugas jip WHERE jip.nama = ? AND jip.id <> ?";
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
        UPDATE jenis_instansi_petugas jip 
        SET 
            jip.nama = ?,
            jip.status_aktif = ? 
        WHERE jip.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $name, $active_status, $id);
    
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
        "data" => ["id" => $id, "name" => $name, "active_status" => boolval($active_status)],
        "message" => "Data jenis instansi petugas berhasil diubah"
    ];

    echo json_encode($response);