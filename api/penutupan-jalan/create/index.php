<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $masyarakat_id = $payload["id"];
    
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
        SELECT pda.id, b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, 
            m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, 
            m.nik, m.jenis_kelamin, m.kategori, m.status_valid, 
            pda.otoritas_sirine, pda.status_prajuru, pda.status_aktif
        FROM pecalang pda, masyarakat m, banjar b, desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n, status_aktif_masyarakat sam
        WHERE pda.id_masyarakat = m.id
        AND m.id_status_aktif = sam.id
        AND m.id_banjar = b.id
        AND b.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND pda.id_masyarakat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $masyarakat_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($pecalang_id, $banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, $active_status_active_status, $masyarakat_name, $masyarakat_email, $masyarakat_avatar, $masyarakat_phone, $masyarakat_date_of_birth, $masyarakat_nik, $masyarakat_gender, $masyarakat_category, $masyarakat_valid_status, $pecalang_sirine_authority, $pecalang_prajuru_status, $pecalang_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$pecalang_id) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_POST["title"]) && $_POST["title"] &&
        isset($_POST["start_time"]) && $_POST["start_time"] &&
        isset($_POST["end_time"]) && $_POST["end_time"] &&
        isset($_POST["blocked_roads"]) && $_POST["blocked_roads"] &&
        isset($_POST["allowed_roads"]) && $_POST["allowed_roads"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon isi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $title = $_POST["title"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $blocked_roads = json_decode($_POST["blocked_roads"]);
    $allowed_roads = json_decode($_POST["allowed_roads"]);
    
    if (new DateTime($start_time) > new DateTime($end_time)) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Waktu mulai harus lebih kecil dari waktu selesai"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $category_text = $masyarakat_category == 0 ? "Krama Wid" : "Krama Tamiu";
    
    if ($masyarakat_category == 2) {
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
    $stmt->bind_param("ss", $masyarakat_id, $masyarakat_id);
    
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
    
    $cur_date = date("Y-m-d");
    $query = "
        SELECT COUNT(*) 
        FROM jadwal_pecalang jpcl 
        WHERE jpcl.id_pecalang = ? 
        AND jpcl.tanggal_mulai <= ?
        AND jpcl.tanggal_selesai >= ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $pecalang_id, $cur_date, $cur_date);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($schedule);
    $stmt->fetch();
    $stmt->close();
    
    $cover = upload_image("cover");
    
    $query = "INSERT INTO penutupan_jalan VALUES (NULL, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $pecalang_id, $title, $cover, $start_time, $end_time);
    
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
    
    $points = [];
    for ($i = 0; $i < count($blocked_roads); $i++) {
        $coordinates = [];
        $type = 0;
        $query = "INSERT INTO titik_penutupan_jalan VALUES (NULL, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $id, $type);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $point_id = $mysqli->insert_id;
        $stmt->close();
        
        for ($j = 0; $j < count($blocked_roads[$i]); $j++) {
            $latitude = $blocked_roads[$i][$j][0];
            $longitude = $blocked_roads[$i][$j][1];
            
            $query = "INSERT INTO detail_titik_penutupan_jalan VALUES (NULL, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $point_id, $latitude, $longitude);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $coordinate_id = $mysqli->insert_id;
            $stmt->close();
            
            array_push($coordinates, [
                "id" => $coordinate_id,
                "latitude" => $latitude,
                "longitude" => $longitude
            ]);
        }
        
        array_push($points, [
            "id" => $point_id,
            "coordinates" => $coordinates,
            "type" => $type
        ]);
    }
    
    for ($i = 0; $i < count($allowed_roads); $i++) {
        $coordinates = [];
        $type = 1;
        $query = "INSERT INTO titik_penutupan_jalan VALUES (NULL, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $id, $type);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $point_id = $mysqli->insert_id;
        $stmt->close();
        
        for ($j = 0; $j < count($allowed_roads[$i]); $j++) {
            $latitude = $allowed_roads[$i][$j][0];
            $longitude = $allowed_roads[$i][$j][1];
            
            $query = "INSERT INTO detail_titik_penutupan_jalan VALUES (NULL, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $point_id, $latitude, $longitude);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $coordinate_id = $mysqli->insert_id;
            $stmt->close();
            
            array_push($coordinates, [
                "id" => $coordinate_id,
                "latitude" => $latitude,
                "longitude" => $longitude
            ]);
        }
        
        array_push($points, [
            "id" => $point_id,
            "coordinates" => $coordinates,
            "type" => $type
        ]);
    }
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "pecalang" => [
               "id" => $pecalang_id,
                "masyarakat" => [
                    "id" => $masyarakat_id,
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
                    "name" => $masyarakat_name,
                    "email" => $masyarakat_email,
                    "avatar" => $masyarakat_avatar,
                    "phone" => $masyarakat_phone,
                    "date_of_birth" => $masyarakat_date_of_birth,
                    "nik" => $masyarakat_nik,
                    "gender" => $masyarakat_gender,
                    "category" => $category_text,
                    "block_status" => $block_counter < $max_invalid_report ? false : true,
                    "valid_status" => boolval($masyarakat_valid_status)
                ],
                "sirine_authority" => boolval($pecalang_sirine_authority),
                "prajuru_status" => boolval($pecalang_prajuru_status),
                "working_status" => $schedule > 0 ? true : false,
                "active_status" => boolval($pecalang_active_status)
            ],
            "points" => $points,
            "title" => $title,
            "cover" => $cover,
            "start_time" => $start_time,
            "end_time" => $end_time
        ],
        "message" => "Informasi penutupan jalan berhasil ditambahkan"
    ];
    echo json_encode($response);