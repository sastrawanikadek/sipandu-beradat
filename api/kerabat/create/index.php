<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $user_id = $payload["id"];
    
    if (!(isset($_POST["id_kerabat"]) && $_POST["id_kerabat"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_kerabat = $_POST["id_kerabat"];
    
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
            m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, m.nik, m.jenis_kelamin, m.kategori, m.status_valid
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
    $stmt->bind_param("s", $user_id);
    
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
    $stmt->bind_param("ss", $user_id, $user_id);
    
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
        SELECT b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, 
            m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, m.nik, m.jenis_kelamin, m.kategori, m.status_valid
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
    $stmt->bind_param("s", $id_kerabat);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    $stmt->bind_result($kerabat_banjar_id, $kerabat_desa_adat_id, 
        $kerabat_kecamatan_id, $kerabat_kabupaten_id, $kerabat_provinsi_id, 
        $kerabat_negara_id, $kerabat_negara_name, $kerabat_negara_flag, $kerabat_negara_active_status, 
        $kerabat_provinsi_name, $kerabat_provinsi_active_status, $kerabat_kabupaten_name, 
        $kerabat_kabupaten_active_status, $kerabat_kecamatan_name, $kerabat_kecamatan_active_status, 
        $kerabat_desa_adat_name, $kerabat_desa_adat_latitude, $kerabat_desa_adat_longitude, 
        $kerabat_desa_adat_active_status, $kerabat_banjar_name, $kerabat_banjar_active_status, 
        $kerabat_active_status_id, $kerabat_active_status_name, $kerabat_active_status_status, 
        $kerabat_active_status_active_status, $kerabat_name, $kerabat_email, $kerabat_avatar, $kerabat_phone, 
        $kerabat_date_of_birth, $kerabat_nik, $kerabat_gender, $kerabat_category, $kerabat_valid_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$kerabat_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data kerabat tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $kerabat_category_text = intval($kerabat_category) == 0 ? "Krama Wid" : "Krama Tamiu";
    
    if (intval($kerabat_category) == 2) {
        $kerabat_category_text = "Tamiu";
    }
    
    $query = "
        SELECT tnm.token 
        FROM token_notifikasi_masyarakat tnm 
        WHERE tnm.id_masyarakat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_kerabat);
    
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
    $stmt->bind_param("ss", $id_kerabat, $id_kerabat);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($kerabat_block_counter);
    $stmt->fetch();
    $stmt->close();
    
    $query = "INSERT INTO kerabat VALUES (NULL, ?, ?, '0')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $user_id, $id_kerabat);
    
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
    
    $notification_data = [
        "id" => $user_id,
        "type" => 0
    ];
    
    $notification_title = "Permintaan Kerabat Baru";
    $notification_message = "$name menambahkan Anda sebagai kerabat";
    $notification_photo = $avatar;
    
    $query = "INSERT INTO notifikasi_masyarakat VALUES (NULL, ?, ?, ?, ?, '0', ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $id_kerabat, $notification_photo, $notification_title, $notification_message, $user_id);
    
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
    
    if ($fcm_token) {
        send_notifications($factory, [$fcm_token], [
            "notification_type" => "family-request",
            "notification_title" => $notification_title,
            "notification_message" => $notification_message,
            "notification_photo" => $notification_photo,
            "notification_data" => json_encode($notification_data)
        ]);
    }
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "masyarakat" => [
                "id" => $user_id,
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
            "family" => [
                "id" => $id_kerabat,
                "banjar" => [
                    "id" => $kerabat_banjar_id,
                    "desa_adat" => [
                        "id" => $kerabat_desa_adat_id,
                        "kecamatan" => [
                            "id" => $kerabat_kecamatan_id,
                            "kabupaten" => [
                                "id" => $kerabat_kabupaten_id,
                                "provinsi" => [
                                    "id" => $kerabat_provinsi_id,
                                    "negara" => [
                                        "id" => $kerabat_negara_id,
                                        "name" => $kerabat_negara_name,
                                        "flag" => $kerabat_negara_flag,
                                        "active_status" => boolval($kerabat_negara_active_status)
                                    ],
                                    "name" => $kerabat_provinsi_name,
                                    "active_status" => boolval($kerabat_provinsi_active_status)
                                ],
                                "name" => $kerabat_kabupaten_name,
                                "active_status" => boolval($kerabat_kabupaten_active_status)
                            ],
                            "name" => $kerabat_kecamatan_name,
                            "active_status" => boolval($kerabat_kecamatan_active_status)
                        ],
                        "name" => $kerabat_desa_adat_name,
                        "latitude" => $kerabat_desa_adat_latitude,
                        "longitude" => $kerabat_desa_adat_longitude,
                        "active_status" => boolval($kerabat_desa_adat_active_status)
                    ],
                    "name" => $kerabat_banjar_name,
                    "active_status" => boolval($kerabat_banjar_active_status)
                ],
                "active_status" => [
                    "id" => $kerabat_active_status_id,
                    "name" => $kerabat_active_status_name,
                    "status" => boolval($kerabat_active_status_status),
                    "active_status" => boolval($kerabat_active_status_active_status)
                ],
                "name" => $kerabat_name,
                "email" => $kerabat_email,
                "avatar" => $kerabat_avatar,
                "phone" => $kerabat_phone,
                "date_of_birth" => $kerabat_date_of_birth,
                "nik" => $kerabat_nik,
                "gender" => $kerabat_gender,
                "category" => $kerabat_category_text,
                "block_status" => $kerabat_block_counter < $max_invalid_report ? false : true,
                "valid_status" => boolval($kerabat_valid_status)
            ],
            "status" => 0
        ],
        "message" => "Data kerabat berhasil ditambahkan"
    ];
    
    echo json_encode($response);