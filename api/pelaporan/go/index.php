<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_pecalang"]) && $_POST["id_pecalang"] &&
        isset($_POST["id_pelaporan"]) && $_POST["id_pelaporan"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();    
    }
    
    $id_pecalang = $_POST["id_pecalang"];
    $id_pelaporan = $_POST["id_pelaporan"];
    
    $mysqli = connect_db();
    $query = "
        SELECT m.nama
        FROM pecalang pda, masyarakat m
        WHERE pda.status_aktif = 1 
        AND pda.id_masyarakat = m.id
        AND pda.id = ?
        AND pda.id_masyarakat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pecalang, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($pecalang_name);
    $stmt->fetch();
    $stmt->close();
    
    if (!$pecalang_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data pecalang tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM pelaporan pl WHERE pl.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan);
    
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
            "message" => "Data pelaporan tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM pecalang_pelaporan pp 
        WHERE pp.id_pecalang = ?
        AND pp.id_pelaporan = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pecalang, $id_pelaporan);
    
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
    
    $query = "INSERT INTO pecalang_pelaporan VALUES (NULL, ?, ?, NULL, '0')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pelaporan, $id_pecalang);
    
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
        FROM token_notifikasi_masyarakat tnm, pelaporan pl 
        WHERE pl.id_masyarakat = tnm.id_masyarakat
        AND pl.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan);
    
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
        "id" => $id_pelaporan,
        "type" => 1
    ];
    
    send_notifications($factory, [$fcm_token], [
        "notification_type" => "report",
        "notification_title" => "Pecalang Menuju Lokasi",
        "notification_message" => "Pecalang $pecalang_name sedang meninjau pelaporan Anda",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah membantu menangani pelaporan ini"
    ];
    echo json_encode($response);
    exit();