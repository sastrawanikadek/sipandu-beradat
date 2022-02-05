<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $user_id = $payload["id"];
    
    if (!((isset($_POST["username"]) && $_POST["username"]) || (isset($_POST["id"]) && $_POST["id"]))) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
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
        SELECT m.id, b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, 
            m.nama, m.email, m.username, m.avatar, m.no_telp, m.tanggal_lahir, 
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
        AND m.id <> ?
    ";
    
    if (isset($_POST["username"])) {
        $query .= " AND m.username = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $user_id, $_POST["username"]);
    } else {
        $query .= " AND m.id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $user_id, $_POST["id"]);
    }
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($kerabat_id, $banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, 
        $active_status_active_status, $name, $email, $username, $avatar, $phone, $date_of_birth, 
        $nik, $gender, $category, $valid_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data masyarakat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $category_text = intval($category) == 0 ? "Krama Wid" : "Krama Tamiu";
    
    if (intval($category) == 2) {
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
    $stmt->bind_param("ss", $kerabat_id, $kerabat_id);
    
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
    
    $query = "
        SELECT k.id, k.status, IF(k.id_masyarakat=?, 1, 0)
        FROM kerabat k
        WHERE (k.id_masyarakat = ? AND k.id_kerabat = ?)
        OR (k.id_masyarakat = ? AND k.id_kerabat = ?)
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $user_id, $user_id, $kerabat_id, $kerabat_id, $user_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($id, $family_status, $initiator_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$id) {
        $id = -1;
        $family_status = -1;
    }
    
    $pelaporans = [];
    
    if (intval($family_status) == 1) {
        $query = "
            SELECT plr.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, 
                n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif,
                kec.nama, kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif,
                jp.id, jp.nama, jp.icon, jp.status_darurat, jp.status_aktif, plr.judul, plr.foto, 
                plr.keterangan, plr.waktu, plr.latitude, plr.longitude
            FROM pelaporan plr, desa_adat da, kecamatan kec, kabupaten kab, 
                provinsi p, negara n, jenis_pelaporan jp
            WHERE plr.id_jenis_pelaporan = jp.id
            AND plr.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND plr.id_masyarakat = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $kerabat_id);
        
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
        $stmt->bind_result($report_id, $report_desa_adat_id, $report_kecamatan_id, $report_kabupaten_id, 
            $report_provinsi_id, $report_negara_id, $report_negara_name, $report_negara_flag, $report_negara_active_status, 
            $report_provinsi_name, $report_provinsi_active_status, $report_kabupaten_name, 
            $report_kabupaten_active_status, $report_kecamatan_name, $report_kecamatan_active_status, 
            $report_desa_adat_name, $report_desa_adat_latitude, $report_desa_adat_longitude, 
            $report_desa_adat_active_status, $report_jenis_pelaporan_id, $report_jenis_pelaporan_name, 
            $report_jenis_pelaporan_icon, $report_jenis_pelaporan_emergency_status, 
            $report_jenis_pelaporan_active_status, $report_title, $report_photo, $report_description, $report_time, 
            $report_latitude, $report_longitude);
        
        while ($stmt->fetch()) {
            $status = null;
            
            $query = "
                SELECT pdapl.status, COUNT(pdapl.status) AS total 
                FROM pecalang_pelaporan pdapl 
                WHERE pdapl.id_pelaporan = ? 
                GROUP BY pdapl.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();
            
            if (!$status) {
                $report_status = 0;
            } else {
                $report_status = intval($status) == -1 ? -1 : 0;
                
                if (intval($status) == 1) {
                    $status = null;
                    
                    $query = "
                        SELECT ptpl.status, COUNT(ptpl.status) AS total 
                        FROM petugas_pelaporan ptpl 
                        WHERE ptpl.id_pelaporan = ? 
                        GROUP BY ptpl.status 
                        ORDER BY total DESC
                        LIMIT 1
                    ";
                    $stmt2 = $mysqli->prepare($query);
                    $stmt2->bind_param("s", $report_id);
                    
                    if (!$stmt2->execute()) {
                        $response = [
                            "status_code" => 500,
                            "data" => null,
                            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                        ];
                        echo json_encode($response);
                        exit();
                    }
                    
                    $stmt2->bind_result($status, $total);
                    $stmt2->fetch();
                    $stmt2->close();
                    
                    if (!$status) {
                        $report_status = 1;
                    } else {
                        $report_status = intval($status) == 1 ? 2 : 1;
                    }
                }
            }
            
            array_push($pelaporans, [
                "id" => $report_id,
                "masyarakat" => [
                    "id" => $kerabat_id,
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
                    "id" => $report_jenis_pelaporan_id,
                    "name" => $report_jenis_pelaporan_name,
                    "icon" => $report_jenis_pelaporan_icon,
                    "emergency_status" => boolval($report_jenis_pelaporan_emergency_status),
                    "active_status" => boolval($report_jenis_pelaporan_active_status)
                ],
                "title" => $report_title,
                "photo" => $report_photo,
                "description" => $report_description,
                "time" => $report_time,
                "latitude" => $report_latitude,
                "longitude" => $report_longitude,
                "status" => $report_status
            ]);
        }
        
        $stmt->close();
        
        $query = "
            SELECT plrd.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, 
                n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif,
                kec.nama, kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif,
                jp.id, jp.nama, jp.icon, jp.status_darurat, jp.status_aktif, plrd.waktu, 
                plrd.latitude, plrd.longitude
            FROM pelaporan_darurat plrd, desa_adat da, kecamatan kec, kabupaten kab, 
                provinsi p, negara n, jenis_pelaporan jp
            WHERE plrd.id_jenis_pelaporan = jp.id
            AND plrd.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND plrd.id_masyarakat = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $kerabat_id);
        
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
        $stmt->bind_result($emergency_report_id, $emergency_report_desa_adat_id, 
            $emergency_report_kecamatan_id, $emergency_report_kabupaten_id, $emergency_report_provinsi_id, 
            $emergency_report_negara_id, $emergency_report_negara_name, $emergency_report_negara_flag, $emergency_report_negara_active_status, 
            $emergency_report_provinsi_name, $emergency_report_provinsi_active_status, 
            $emergency_report_kabupaten_name, $emergency_report_kabupaten_active_status, 
            $emergency_report_kecamatan_name, $emergency_report_kecamatan_active_status, 
            $emergency_report_desa_adat_name, $emergency_report_desa_adat_latitude, 
            $emergency_report_desa_adat_longitude, $emergency_report_desa_adat_active_status, 
            $emergency_report_jenis_pelaporan_id, $emergency_report_jenis_pelaporan_name, 
            $emergency_report_jenis_pelaporan_icon, $emergency_report_jenis_pelaporan_emergency_status, 
            $emergency_report_jenis_pelaporan_active_status, $emergency_report_time, $emergency_report_latitude, 
            $emergency_report_longitude);
            
        while ($stmt->fetch()) {
            $emergency_status = null;
            
            $query = "
                SELECT pdapld.status, COUNT(pdapld.status) AS total 
                FROM pecalang_pelaporan_darurat pdapld 
                WHERE pdapld.id_pelaporan_darurat = ? 
                GROUP BY pdapld.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $emergency_report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($emergency_status, $total);
            $stmt2->fetch();
            $stmt2->close();
            
            if (!$emergency_status) {
                $emergency_report_status = 0;
            } else {
                $emergency_report_status = intval($emergency_status) == -1 ? -1 : 0;
                
                if (intval($emergency_status) == 1) {
                    $emergency_status = null;
                    
                    $query = "
                        SELECT ptpld.status, COUNT(ptpld.status) AS total 
                        FROM petugas_pelaporan_darurat ptpld 
                        WHERE ptpld.id_pelaporan_darurat = ? 
                        GROUP BY ptpld.status 
                        ORDER BY total DESC
                        LIMIT 1
                    ";
                    $stmt2 = $mysqli->prepare($query);
                    $stmt2->bind_param("s", $emergency_report_id);
                    
                    if (!$stmt2->execute()) {
                        $response = [
                            "status_code" => 500,
                            "data" => null,
                            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                        ];
                        echo json_encode($response);
                        exit();
                    }
                    
                    $stmt2->bind_result($emergency_status, $total);
                    $stmt2->fetch();
                    $stmt2->close();
                    
                    if (!$emergency_status) {
                        $emergency_report_status = 1;
                    } else {
                        $emergency_report_status = intval($emergency_status) == 1 ? 2 : 1;
                    }
                }
            }
            
            array_push($pelaporans, [
                "id" => $emergency_report_id,
                "masyarakat" => [
                    "id" => $kerabat_id,
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
                    "id" => $emergency_report_desa_adat_id,
                    "kecamatan" => [
                        "id" => $emergency_report_kecamatan_id,
                        "kabupaten" => [
                            "id" => $emergency_report_kabupaten_id,
                            "provinsi" => [
                                "id" => $emergency_report_provinsi_id,
                                "negara" => [
                                    "id" => $emergency_report_negara_id,
                                    "name" => $emergency_report_negara_name,
                                    "flag" => $emergency_report_negara_flag,
                                    "active_status" => boolval($emergency_report_negara_active_status)
                                ],
                                "name" => $emergency_report_provinsi_name,
                                "active_status" => boolval($emergency_report_provinsi_active_status)
                            ],
                            "name" => $emergency_report_kabupaten_name,
                            "active_status" => boolval($emergency_report_kabupaten_active_status)
                        ],
                        "name" => $emergency_report_kecamatan_name,
                        "active_status" => boolval($emergency_report_kecamatan_active_status)
                    ],
                    "name" => $emergency_report_desa_adat_name,
                    "latitude" => $emergency_report_desa_adat_latitude,
                    "longitude" => $emergency_report_desa_adat_longitude,
                    "active_status" => boolval($emergency_report_desa_adat_active_status)
                ],
                "jenis_pelaporan" => [
                    "id" => $emergency_report_jenis_pelaporan_id,
                    "name" => $emergency_report_jenis_pelaporan_name,
                    "icon" => $emergency_report_jenis_pelaporan_icon,
                    "emergency_status" => boolval($emergency_report_jenis_pelaporan_emergency_status),
                    "active_status" => boolval($emergency_report_jenis_pelaporan_active_status)
                ],
                "time" => $emergency_report_time,
                "latitude" => $emergency_report_latitude,
                "longitude" => $emergency_report_longitude,
                "status" => $emergency_report_status
            ]);
        }
        
        $stmt->close();
    }
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "masyarakat" => [
                "id" => $kerabat_id,
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
                "username" => $username,
                "avatar" => $avatar,
                "phone" => $phone,
                "date_of_birth" => $date_of_birth,
                "nik" => $nik,
                "gender" => $gender,
                "category" => $category_text,
                "block_status" => $block_counter < $max_invalid_report ? false : true,
                "valid_status" => boolval($valid_status)
            ],
            "pelaporan" => $pelaporans,
            "status" => intval($family_status),
            "initiator_status" => boolval($initiator_status)
        ],
        "message" => "Data kerabat berhasil diperoleh"
    ];
    
    echo json_encode($response);