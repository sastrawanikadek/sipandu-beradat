<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_pecalang"]) && $_POST["id_pecalang"] &&
        isset($_POST["id_pelaporan_darurat_tamu"]) && $_POST["id_pelaporan_darurat_tamu"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();    
    }
    
    $id_pecalang = $_POST["id_pecalang"];
    $id_pelaporan_darurat_tamu = $_POST["id_pelaporan_darurat_tamu"];
    
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
        FROM pecalang_pelaporan_darurat_tamu pdapldrt 
        WHERE pdapldrt.id_pecalang = ?
        AND pdapldrt.id_pelaporan_darurat_tamu = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pecalang, $id_pelaporan_darurat_tamu);
    
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
    
    $query = "INSERT INTO pecalang_pelaporan_darurat_tamu VALUES (NULL, ?, ?, NULL, '0')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pelaporan_darurat_tamu, $id_pecalang);
    
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
        "notification_title" => "Pecalang is On The Way",
        "notification_message" => "Pecalang $pecalang_name is reviewing your report",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah membantu menangani pelaporan ini"
    ];
    echo json_encode($response);
    exit();