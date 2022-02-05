<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id_kecamatan"]) && $_POST["id_kecamatan"] &&
        isset($_POST["id_jenis_instansi"]) && $_POST["id_jenis_instansi"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["report_status"]) && ($_POST["report_status"] || $_POST["report_status"] == 0))) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_kecamatan = $_POST["id_kecamatan"];
    $id_jenis_instansi = $_POST["id_jenis_instansi"];
    $name = $_POST["name"];
    $report_status = $_POST["report_status"];
    
    $mysqli = connect_db();
    $query = "
        SELECT kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif, 
            kab.nama, kab.status_aktif, kec.nama, kec.status_aktif
        FROM kecamatan kec, kabupaten kab, provinsi p, negara n
        WHERE kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND kec.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_kecamatan);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag,
        $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$kecamatan_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data kecamatan tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT jip.nama, jip.status_aktif FROM jenis_instansi_petugas jip WHERE jip.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_jenis_instansi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($jenis_instansi_name, $jenis_instansi_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$jenis_instansi_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data jenis instansi petugas tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM instansi_petugas ip 
        WHERE ip.id_kecamatan = ? 
        AND ip.id_jenis_instansi = ? 
        AND ip.nama = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id_kecamatan, $id_jenis_instansi, $name);
    
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
    
    $query = "INSERT INTO instansi_petugas VALUES (NULL, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $id_kecamatan, $id_jenis_instansi, $name, $report_status);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $id = $mysqli->insert_id;
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "kecamatan" => [
                "id" => $id_kecamatan,
                "kabupaten" => [
                    "id" => $kabupaten_id,
                    "provinsi" => [
                        "id" => $provinsi_id,
                        "negara" => [
                            "id" => $negara_id,
                            "name" => $negara_name,
                            "flag" => $negara_flag,
                            "active_status" => boolval($negara_active_status)
                        ],
                        "name" => $provinsi_name,
                        "active_status" => boolval($provinsi_active_status)
                    ],
                    "name" => $kabupaten_name,
                    "active_status" => boolval($kabupaten_active_status)
                ],
                "name" => $kecamatan_name,
                "active_status" => boolval($kecamatan_active_status)
            ],
            "jenis_instansi" => [
                "id" => $id_jenis_instansi,
                "name" => $jenis_instansi_name,
                "active_status" => boolval($jenis_instansi_active_status)
            ],
            "name" => $name,
            "report_status" => $report_status,
            "active_status" => true
        ],
        "message" => "Data instansi petugas berhasil ditambahkan"
    ];
    echo json_encode($response);