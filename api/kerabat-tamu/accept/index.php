<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["tamu"]);
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
        SELECT kt.id_tamu, kt.id_kerabat, t.nama, t.avatar
        FROM kerabat_tamu kt, tamu t 
        WHERE kt.id_kerabat = t.id
        AND kt.id = ?
        AND (kt.id_tamu = ? OR kt.id_kerabat = ?)
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
    
    $stmt->bind_result($id_tamu, $id_kerabat, $name, $avatar);
    $stmt->fetch();
    $stmt->close();
    
    if (!$id_tamu) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data kerabat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT tnt.token
        FROM token_notifikasi_tamu tnt
        WHERE tnt.id_tamu = ?
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
    
    $query = "UPDATE kerabat_tamu kt SET kt.status = '1' WHERE kt.id = ?";
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
    
    $stmt->close();
    
    $query = "
        DELETE FROM notifikasi_tamu
        WHERE id_tamu = ?
        AND jenis = '0'
        AND data = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_kerabat, $id_tamu);
    
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
            "notification_title" => "New Family",
            "notification_message" => "$name accept your family request",
            "notification_photo" => $avatar,
            "notification_data" => json_encode($notification_data)
        ]);
    }
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Family request has been successfully accepted"
    ];
    echo json_encode($response);