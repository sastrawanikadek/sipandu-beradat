<?php
    require_once("../config.php");
    require_once("../helpers/notification.php");
    
    $fcm_tokens = [];
    $mysqli = connect_db();
    $query = "
        SELECT tnm.token
        FROM token_notifikasi_masyarakat tnm, masyarakat m
        WHERE tnm.id_masyarakat = m.id
        AND m.id_status_aktif = (SELECT id FROM status_aktif_masyarakat WHERE nama = 'Aktif')
    ";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($token);
    
    while ($stmt->fetch()) {
        array_push($fcm_tokens, $token);
    }
    
    $stmt->close();
    
    $query = "
        SELECT tnt.token
        FROM token_notifikasi_tamu tnt, tamu t
        WHERE tnt.id_tamu = t.id
        AND t.status_aktif = 1
    ";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($token);
    
    while ($stmt->fetch()) {
        array_push($fcm_tokens, $token);
    }
    
    $stmt->close();
    
    $data = ["notification_type" => "request-location"];
    
    send_notifications($factory, $fcm_tokens, $data);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Berhasil meminta lokasi pengguna"
    ];
    echo json_encode($response);
    exit();