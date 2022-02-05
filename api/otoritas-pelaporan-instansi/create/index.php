<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id_instansi"]) && $_POST["id_instansi"] &&
        isset($_POST["id_jenis_pelaporan"]) && $_POST["id_jenis_pelaporan"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_instansi = $_POST["id_instansi"];
    $id_jenis_pelaporan = $_POST["id_jenis_pelaporan"];
    
    $mysqli = connect_db();
    $query = "
        SELECT kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, jip.id, jip.nama, jip.status_aktif, ip.nama, 
            ip.status_pelaporan, ip.status_aktif
        FROM instansi_petugas ip, kecamatan kec, kabupaten kab, provinsi p, 
            negara n, jenis_instansi_petugas jip
        WHERE ip.id_kecamatan = kec.id
        AND ip.id_jenis_instansi = jip.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND ip.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_instansi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($kecamatan_id, $kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag,
        $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
        $kecamatan_active_status, $jenis_instansi_id, $jenis_instansi_name, 
        $jenis_instansi_active_status, $instansi_name, 
        $instansi_report_status, $instansi_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$instansi_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data instansi petugas tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT jp.nama, jp.icon, jp.status_darurat, jp.status_aktif 
        FROM jenis_pelaporan jp 
        WHERE jp.id = ?
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
    
    $stmt->bind_result($jenis_pelaporan_name, $jenis_pelaporan_icon, 
        $jenis_pelaporan_emergency_status, $jenis_pelaporan_active_status);
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
    
    $query = "
        SELECT COUNT(*) 
        FROM otoritas_pelaporan_instansi opi 
        WHERE opi.id_instansi = ? 
        AND opi.id_jenis_pelaporan = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_instansi, $id_jenis_pelaporan);
    
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
    
    $query = "INSERT INTO otoritas_pelaporan_instansi VALUES (NULL, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_instansi, $id_jenis_pelaporan);
    
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
            "instansi_petugas" => [
                "id" => $id_instansi,
                "kecamatan" => [
                    "id" => $kecamatan_id,
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
                    "id" => $jenis_instansi_id,
                    "name" => $jenis_instansi_name,
                    "active_status" => boolval($jenis_instansi_active_status)
                ],
                "name" => $instansi_name,
                "report_status" => $instansi_report_status,
                "active_status" => boolval($instansi_active_status)
            ],
            "jenis_pelaporan" => [
                "id" => $id_jenis_pelaporan,
                "name" => $jenis_pelaporan_name,
                "icon" => $jenis_pelaporan_icon,
                "emergency_status" => boolval($jenis_pelaporan_emergency_status),
                "active_status" => boolval($jenis_pelaporan_active_status)
            ]
        ],
        "message" => "Data otoritas pelaporan instansi berhasil ditambahkan"
    ];
    echo json_encode($response);