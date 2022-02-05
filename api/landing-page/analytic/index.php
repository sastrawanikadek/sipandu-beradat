<?php
    require_once("../../config.php");
    
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $mysqli = connect_db();
    $query = "
        SELECT COUNT(*) 
        FROM masyarakat m, status_aktif_masyarakat sam 
        WHERE m.id_status_aktif = sam.id
        AND m.id_status_aktif = 
            (SELECT id FROM status_aktif_masyarakat WHERE nama = 'Aktif')
        AND m.status_valid = 1
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
    
    $stmt->bind_result($total_masyarakat);
    $stmt->fetch();
    $stmt->close();

    $query = "
        SELECT COUNT(*)
        FROM pecalang pcl
        WHERE pcl.status_aktif = 1
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
    
    $stmt->bind_result($total_pecalang);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*)
        FROM tamu t
        WHERE t.status_aktif = 1
        AND t.status_valid = 1
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
    
    $stmt->bind_result($total_tamu);
    $stmt->fetch();
    $stmt->close();

    $query = "
        SELECT COUNT(*)
        FROM desa_adat da
        WHERE da.status_aktif = 1
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
    
    $stmt->bind_result($total_desa_adat);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*)
        FROM instansi_petugas ip
        WHERE ip.status_aktif = 1
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
    
    $stmt->bind_result($total_instansi);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*)
        FROM akomodasi a
        WHERE a.status_aktif = 1
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
    
    $stmt->bind_result($total_akomodasi);
    $stmt->fetch();
    $stmt->close();

    $response = [
        "status_code" => 200,
        "data" => [
            "total_masyarakat" => $total_masyarakat,
            "total_pecalang" => $total_pecalang,
            "total_tamu" => $total_tamu,
            "total_desa_adat" => $total_desa_adat,
            "total_instansi" => $total_instansi,
            "total_akomodasi" => $total_akomodasi
        ],
        "message" => "Data berhasil diperoleh"
    ];
    echo json_encode($response);