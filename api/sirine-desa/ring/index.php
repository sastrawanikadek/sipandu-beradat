<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $id_masyarakat = $payload["id"];

    $mysqli = connect_db();
    $query = "SELECT pda.id FROM pecalang pda WHERE pda.status_aktif = 1 AND pda.id_masyarakat = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_masyarakat);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }

    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    if (!$id) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT pda.otoritas_sirine, b.id_desa
        FROM pecalang pda, masyarakat m, banjar b
        WHERE pda.id_masyarakat = m.id
        AND m.id_banjar = b.id
        AND pda.id = ?
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
    
    $stmt->bind_result($sirine_authority, $desa_adat_id);
    $stmt->fetch();
    $stmt->close();
    
    if ($sirine_authority != 1) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
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
    
    $query = "
        SELECT COUNT(*) 
        FROM sirine_desa sd 
        WHERE sd.status_aktif = 1 
        AND sd.id_desa = ? 
        AND sd.kode = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $desa_adat_id, $code);
    
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
    $reference = $database->getReference("kulkul-desa/$desa_adat_id/$code");
    $reference->set([
        "ring" => 1,
        "duration" => $duration,
        "jenis_pelaporan_emergency_status" => $jenis_pelaporan_emergency_status
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Sirine desa adat berhasil dihidupkan"
    ];
    echo json_encode($response);