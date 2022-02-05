<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["petugas"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_pelaporan_darurat_tamu"]) && $_POST["id_pelaporan_darurat_tamu"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();    
    }
    
    $id_pelaporan_darurat_tamu = $_POST["id_pelaporan_darurat_tamu"];
    
    $mysqli = connect_db();
    $query = "
        SELECT pt.nama
        FROM petugas pt
        WHERE pt.id = ?
    ";
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
    
    $stmt->bind_result($petugas_name);
    $stmt->fetch();
    $stmt->close();
    
    if (!$petugas_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data petugas tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM pelaporan_darurat_tamu pldrt WHERE pldrt.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_darurat_tamu);
    
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
    
    if ($total == 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data pelaporan darurat tamu tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM petugas_pelaporan_darurat_tamu ppdrt 
        WHERE ppdrt.id_petugas = ?
        AND ppdrt.id_pelaporan_darurat_tamu = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $id_pelaporan_darurat_tamu);
    
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
    
    $query = "INSERT INTO petugas_pelaporan_darurat_tamu VALUES (NULL, ?, ?, NULL, '0')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pelaporan_darurat_tamu, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->close();
    
    $query = "
        SELECT tnt.token 
        FROM token_notifikasi_tamu tnt, pelaporan_darurat_tamu pldrt 
        WHERE pldrt.id_tamu = tnt.id_tamu
        AND pldrt.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_darurat_tamu);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($fcm_token);
    $stmt->fetch();
    $stmt->close();
    
    $notification_data = [
        "id" => $id_pelaporan_darurat_tamu,
        "type" => 2
    ];
    
    send_notifications($factory, [$fcm_token], [
        "notification_type" => "emergency-report",
        "notification_title" => "The Officer is On The Way",
        "notification_message" => "Officer $petugas_name is processing your report",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah membantu menangani pelaporan ini"
    ];
    echo json_encode($response);
    exit();