<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["petugas"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_pelaporan_darurat"]) && $_POST["id_pelaporan_darurat"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();    
    }
    
    $id_pelaporan_darurat = $_POST["id_pelaporan_darurat"];
    
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
    
    $query = "SELECT COUNT(*) FROM pelaporan_darurat pldr WHERE pldr.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_darurat);
    
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
            "message" => "Data pelaporan darurat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM petugas_pelaporan_darurat ppdr 
        WHERE ppdr.id_petugas = ?
        AND ppdr.id_pelaporan_darurat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $id_pelaporan_darurat);
    
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
    
    $query = "INSERT INTO petugas_pelaporan_darurat VALUES (NULL, ?, ?, NULL, '0')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pelaporan_darurat, $id);
    
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
        SELECT tnm.token 
        FROM token_notifikasi_masyarakat tnm, pelaporan_darurat pldr 
        WHERE pldr.id_masyarakat = tnm.id_masyarakat
        AND pldr.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_darurat);
    
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
        "id" => $id_pelaporan_darurat,
        "type" => 2
    ];
    
    send_notifications($factory, [$fcm_token], [
        "notification_type" => "emergency-report",
        "notification_title" => "Petugas Menuju Lokasi",
        "notification_message" => "Petugas $petugas_name sedang memproses pelaporan Anda",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah membantu menangani pelaporan ini"
    ];
    echo json_encode($response);
    exit();