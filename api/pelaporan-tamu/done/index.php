<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["petugas"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_pelaporan_tamu"]) && $_POST["id_pelaporan_tamu"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_pelaporan_tamu = $_POST["id_pelaporan_tamu"];
    
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
        FROM pelaporan_tamu plt, jenis_pelaporan jp 
        WHERE plt.id_jenis_pelaporan = jp.id
        AND plt.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_tamu);
    
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
        UPDATE petugas_pelaporan_tamu ppt
        SET ppt.foto = ?, 
            ppt.status = '1'
        WHERE ppt.id_petugas = ?
        AND ppt.id_pelaporan_tamu = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $photo, $id, $id_pelaporan_tamu);
    
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
        FROM token_notifikasi_tamu tnt, pelaporan_tamu plt 
        WHERE plt.id_tamu = tnt.id_tamu
        AND plt.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_tamu);
    
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
        "id" => $id_pelaporan_tamu,
        "type" => 1
    ];
    
    send_notifications($factory, [$fcm_token], [
        "notification_type" => "report",
        "notification_title" => "$jenis_pelaporan_name Report Status",
        "notification_message" => "Officer $petugas_name has reported that your report is done",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah menangani laporan ini"
    ];
    echo json_encode($response);
    exit();