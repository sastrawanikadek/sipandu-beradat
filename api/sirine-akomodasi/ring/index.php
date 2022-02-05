<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["admin_akomodasi"]);
    $id_admin = $payload["id"];
    
    if (!(isset($_POST["code"]) && $_POST["code"] &&
        isset($_POST["id_jenis_pelaporan"]) && $_POST["id_jenis_pelaporan"] &&
        isset($_POST["duration"]) && $_POST["duration"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $code = $_POST["code"];
    $id_jenis_pelaporan = $_POST["id_jenis_pelaporan"];
    $duration = intval($_POST["duration"]) * 1000;
    
    $mysqli = connect_db();
    $query = "
        SELECT pa.id_akomodasi 
        FROM admin_akomodasi aa, pegawai_akomodasi pa 
        WHERE aa.id_pegawai = pa.id 
        AND aa.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_admin);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($id_akomodasi);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM sirine_akomodasi sa 
        WHERE sa.status_aktif = 1 
        AND sa.id_akomodasi = ?
        AND sa.kode = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_akomodasi, $code);
    
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
            "message" => "Data sirine tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT jp.nama, jp.status_darurat 
        FROM jenis_pelaporan jp 
        WHERE jp.status_aktif = 1 
        AND jp.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_jenis_pelaporan);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($jenis_pelaporan_name, $jenis_pelaporan_emergency_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$jenis_pelaporan_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data jenis pelaporan tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $database = $factory->createDatabase();
    $reference = $database->getReference("kulkul-akomodasi/$id_akomodasi/$code");
    $reference->set([
        "ring" => 1,
        "duration" => $duration,
        "jenis_pelaporan_emergency_status" => $jenis_pelaporan_emergency_status
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Sirine akomodasi berhasil dihidupkan"
    ];
    echo json_encode($response);