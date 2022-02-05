<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $user_id  = $payload["id"];
    
    if (!(isset($_POST["id"]) && $_POST["id"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    
    $mysqli = connect_db();
    $query = "
        SELECT k.id_masyarakat, k.id_kerabat, m.nama, m.avatar
        FROM kerabat k, masyarakat m
        WHERE k.id_kerabat = m.id
        AND k.id = ?
        AND (k.id_masyarakat = ? OR k.id_kerabat = ?)
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id, $user_id, $user_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($id_masyarakat, $id_kerabat, $name, $avatar);
    $stmt->fetch();
    $stmt->close();
    
    if (!$id_masyarakat) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data kerabat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT tnm.token
        FROM token_notifikasi_masyarakat tnm
        WHERE tnm.id_masyarakat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $user_id);
    
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
    
    $query = "UPDATE kerabat k SET k.status = '1' WHERE k.id = ?";
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
    
    $query = "
        DELETE FROM notifikasi_masyarakat
        WHERE id_masyarakat = ?
        AND jenis = '0'
        AND data = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_kerabat, $id_masyarakat);
    
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
    
    $notification_data = [
        "id" => $id_kerabat,
        "type" => 0
    ];
    
    if ($fcm_token) {
        send_notifications($factory, [$fcm_token], [
            "notification_type" => "family-request",
            "notification_title" => "Kerabat Baru",
            "notification_message" => "$name menyetujui permintaan kekerabatan Anda",
            "notification_photo" => $avatar,
            "notification_data" => json_encode($notification_data)
        ]);
    }
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Permintaan kerabat berhasil disetujui"
    ];
    echo json_encode($response);