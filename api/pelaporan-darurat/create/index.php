<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/location.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_jenis_pelaporan"]) && $_POST["id_jenis_pelaporan"] &&
        isset($_POST["latitude"]) && $_POST["latitude"] &&
        isset($_POST["longitude"]) && $_POST["longitude"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_jenis_pelaporan = $_POST["id_jenis_pelaporan"];
    $latitude = floatval($_POST["latitude"]);
    $longitude = floatval($_POST["longitude"]);
    
    $mysqli = connect_db();
    $query = "SELECT p.maks_laporan_tidak_valid FROM pengaturan p";
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

    $stmt->bind_result($max_invalid_report);
    $stmt->fetch();
    $stmt->close();

    $query = "
        SELECT b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif,
            m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, 
            m.nik, m.jenis_kelamin, m.kategori, m.status_valid
        FROM masyarakat m, banjar b, desa_adat da, kecamatan kec, kabupaten kab, 
            provinsi p, negara n, status_aktif_masyarakat sam
        WHERE m.id_status_aktif = sam.id
        AND m.id_banjar = b.id
        AND b.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND m.id = ?
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
    
    $stmt->bind_result($banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, 
        $active_status_active_status, $name, $email, $avatar, $phone, $date_of_birth, 
        $nik, $gender, $category, $valid_status);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT jp.nama, jp.icon, jp.status_darurat, jp.status_aktif
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
    
    $radius_result_pecalang = find_in_radius("pecalang", "id", "latitude", 
        "longitude", 1, $latitude, $longitude);
    
    if (count($radius_result_pecalang) > 0) {
        $total_pecalang_desa = [];
        $max_desa_adat_id = -1;
        $max_pecalang_count = 0;
        
        foreach ($radius_result_pecalang as $result) {
            $pecalang_id = $result["id"];
            
            $query = "
                SELECT b.id_desa
                FROM pecalang pda, masyarakat m, banjar b
                WHERE pda.id_masyarakat = m.id
                AND m.id_banjar = b.id
                AND pda.id = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $pecalang_id);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->bind_result($pecalang_desa_adat_id);
            $stmt->fetch();
            $stmt->close();
            
            if (!isset($total_pecalang_desa[$pecalang_desa_adat_id])) {
                $total_pecalang_desa[$pecalang_desa_adat_id] = 0;
            }
            
            $total_pecalang_desa[$pecalang_desa_adat_id] += 1;
        }
        
        if (isset($total_pecalang_desa[$desa_adat_id])) {
            $report_desa_adat_id = $desa_adat_id;
        } else {
            foreach ($total_pecalang_desa as $pecalang_desa_adat_id => $total) {
                if ($total > $max_pecalang_count) {
                    $max_desa_adat_id = $pecalang_desa_adat_id;
                    $max_pecalang_count = $total;
                }
            }
            
            $report_desa_adat_id = $max_desa_adat_id;
        }
    } else {
        $radius_result_desa_adat = find_in_radius("desa_adat", "id", "latitude",
            "longitude", 5, $latitude, $longitude);
        
        if (count($radius_result_desa_adat) == 0) {
            $response = [
                "status_code" => 400,
                "data" => null,
                "message" => "Desa terdekat tidak ditemukan"
            ];
            echo json_encode($response);
            exit();
        }
        
        $report_desa_adat_id = $radius_result_desa_adat[0]["id"];
    }
    
    $query = "
        SELECT kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif
        FROM desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n
        WHERE da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND da.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $report_desa_adat_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($report_kecamatan_id, $report_kabupaten_id, $report_provinsi_id, 
        $report_negara_id, $report_negara_name, $report_negara_flag, $report_negara_active_status, $report_provinsi_name, 
        $report_provinsi_active_status, $report_kabupaten_name, $report_kabupaten_active_status, 
        $report_kecamatan_name, $report_kecamatan_active_status, $report_desa_adat_name, 
        $report_desa_adat_latitude, $report_desa_adat_longitude, $report_desa_adat_active_status);
    $stmt->fetch();
    $stmt->close();
    
    $category_text = $category == 0 ? "Krama Wid" : "Krama Tamiu";
    
    if ($category == 2) {
        $category_text = "Tamiu";
    }
    
    $query = "
        SELECT
            (SELECT COUNT(*) 
            FROM laporan_darurat_tidak_valid ldtv, pelaporan_darurat pd
            WHERE ldtv.id_pelaporan_darurat = pd.id
            AND pd.id_masyarakat = ?) + 
            (SELECT COUNT(*)
            FROM laporan_tidak_valid ltv, pelaporan plr
            WHERE ltv.id_pelaporan = plr.id
            AND plr.id_masyarakat = ?)
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($block_counter);
    $stmt->fetch();
    $stmt->close();
    
    $now = date("Y-m-d H:i:s");
    $query = "INSERT INTO pelaporan_darurat VALUES (NULL, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id, $report_desa_adat_id, $id_jenis_pelaporan, 
        $now, $latitude, $longitude);
        
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $report_id = $mysqli->insert_id;
    $stmt->close();
    
    $data = [
        "id" => $report_id,
        "masyarakat" => [
            "id" => $id,
            "banjar" => [
                "id" => $banjar_id,
                "desa_adat" => [
                    "id" => $desa_adat_id,
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
                    "name" => $desa_adat_name,
                    "latitude" => $desa_adat_latitude,
                    "longitude" => $desa_adat_longitude,
                    "active_status" => boolval($desa_adat_active_status)
                ],
                "name" => $banjar_name,
                "active_status" => boolval($banjar_active_status)
            ],
            "active_status" => [
                "id" => $active_status_id,
                "name" => $active_status_name,
                "status" => boolval($active_status_status),
                "active_status" => boolval($active_status_active_status)
            ],
            "name" => $name,
            "email" => $email,
            "avatar" => $avatar,
            "phone" => $phone,
            "date_of_birth" => $date_of_birth,
            "nik" => $nik,
            "gender" => $gender,
            "category" => $category_text,
            "block_status" => $block_counter < $max_invalid_report ? false : true,
            "valid_status" => boolval($valid_status)
        ],
        "desa_adat" => [
            "id" => $report_desa_adat_id,
            "kecamatan" => [
                "id" => $report_kecamatan_id,
                "kabupaten" => [
                    "id" => $report_kabupaten_id,
                    "provinsi" => [
                        "id" => $report_provinsi_id,
                        "negara" => [
                            "id" => $report_negara_id,
                            "name" => $report_negara_name,
                            "flag" => $report_negara_flag,
                            "active_status" => boolval($report_negara_active_status)
                        ],
                        "name" => $report_provinsi_name,
                        "active_status" => boolval($report_provinsi_active_status)
                    ],
                    "name" => $report_kabupaten_name,
                    "active_status" => boolval($report_kabupaten_active_status)
                ],
                "name" => $report_kecamatan_name,
                "active_status" => boolval($report_kecamatan_active_status)
            ],
            "name" => $report_desa_adat_name,
            "latitude" => $report_desa_adat_latitude,
            "longitude" => $report_desa_adat_longitude,
            "active_status" => boolval($report_desa_adat_active_status)
        ],
        "jenis_pelaporan" => [
            "id" => $id_jenis_pelaporan,
            "name" => $jenis_pelaporan_name,
            "icon" => $jenis_pelaporan_icon,
            "emergency_status" => boolval($jenis_pelaporan_emergency_status),
            "active_status" => boolval($jenis_pelaporan_active_status)
        ],
        "pecalang_reports" => [],
        "petugas_reports" => [],
        "time" => $now,
        "latitude" => $latitude,
        "longitude" => $longitude,
        "status" => 0
    ];
    
    $pecalang_fcm_tokens = [];
    
    $query = "
        SELECT pda.id, tnm.token 
        FROM pecalang pda, token_notifikasi_masyarakat tnm, masyarakat m, banjar b
        WHERE m.id_banjar = b.id
        AND pda.id_masyarakat = m.id
        AND tnm.id_masyarakat = m.id
        AND pda.status_aktif = 1
        AND b.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $report_desa_adat_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->store_result();
    $stmt->bind_result($pecalang_id, $pecalang_fcm_token);
    
    while ($stmt->fetch()) {
        $cur_date = date("Y-m-d");
        $query = "
            SELECT COUNT(*) 
            FROM jadwal_pecalang jpcl 
            WHERE jpcl.id_pecalang = ? 
            AND jpcl.tanggal_mulai <= ?
            AND jpcl.tanggal_selesai >= ?
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("sss", $pecalang_id, $cur_date, $cur_date);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->bind_result($schedule);
        $stmt2->fetch();
        $stmt2->close();
        
        if ($schedule > 0) {
            array_push($pecalang_fcm_tokens, $pecalang_fcm_token);
        }
    }
    
    $stmt->close();
    
    send_notifications($factory, $pecalang_fcm_tokens, [
        "notification_type" => "emergency-report",
        "notification_title" => $jenis_pelaporan_name,
        "notification_message" => "Telah terjadi $jenis_pelaporan_name di dekatmu yang harus segera dibantu",
        "notification_photo" => $jenis_pelaporan_icon,
        "notification_data" => json_encode(["id" => $report_id])
    ]);
    
    $kerabat_fcm_tokens = [];
    $notification_title = $jenis_pelaporan_name;
    $notification_message = "$name baru saja membuat pelaporan $jenis_pelaporan_name";
    $notification_photo = $jenis_pelaporan_icon;
    $notification_data = [
        "id" => $report_id,
        "type" => 2
    ];
    
    $query = "
        SELECT m.id, tnm.token
        FROM kerabat k, masyarakat m, token_notifikasi_masyarakat tnm
        WHERE ((k.id_masyarakat = ? AND k.id_kerabat = m.id) OR (k.id_kerabat = ? AND k.id_masyarakat = m.id))
        AND tnm.id_masyarakat = m.id
        AND k.status = '1'
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->store_result();
    $stmt->bind_result($kerabat_id, $kerabat_fcm_token);
    
    while ($stmt->fetch()) {
        $query = "INSERT INTO notifikasi_masyarakat VALUES (NULL, ?, ?, ?, ?, '2', ?)";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("sssss", $kerabat_id, $notification_photo, $notification_title, $notification_message, $report_id);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->close();
        
        array_push($kerabat_fcm_tokens, $kerabat_fcm_token);
    }
    
    $stmt->close();
    
    send_notifications($factory, $kerabat_fcm_tokens, [
        "notification_type" => "emergency-report",
        "notification_title" => $notification_title,
        "notification_message" => $notification_message,
        "notification_photo" => $notification_photo,
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data pelaporan darurat berhasil dibuat"
    ];
    echo json_encode($response);