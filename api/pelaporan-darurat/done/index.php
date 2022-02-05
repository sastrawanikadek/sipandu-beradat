<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
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
    
    $query = "
        SELECT jp.nama 
        FROM pelaporan_darurat pldr, jenis_pelaporan jp 
        WHERE pldr.id_jenis_pelaporan = jp.id
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
    
    $stmt->bind_result($jenis_pelaporan_name);
    $stmt->fetch();
    $stmt->close();
    
    if (!$jenis_pelaporan_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data pelaporan tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $photo = upload_image("photo");
    
    $query = "
        UPDATE petugas_pelaporan_darurat ppdr
        SET ppdr.foto = ?, 
            ppdr.status = '1'
        WHERE ppdr.id_petugas = ?
        AND ppdr.id_pelaporan_darurat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $photo, $id, $id_pelaporan_darurat);
    
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
        "notification_title" => "Status Laporan $jenis_pelaporan_name",
        "notification_message" => "Petugas $petugas_name melaporkan bahwa laporan Anda telah selesai",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah menangani laporan ini"
    ];
    echo json_encode($response);
    exit();