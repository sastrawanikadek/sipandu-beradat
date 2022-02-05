<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["tamu"]);
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
    $identity_types = ["Identity Card", "Driving License", "Passport"];
    
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
        SELECT a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif,
            nt.id, nt.nama, nt.status_aktif, t.nama, t.email, t.avatar, t.no_telp, t.tanggal_lahir, 
            t.jenis_identitas, t.no_identitas, t.jenis_kelamin, t.status_aktif
        FROM tamu t, akomodasi a, desa_adat da, kecamatan kec, kabupaten kab, 
            provinsi p, negara n, negara nt
        WHERE t.id_negara = nt.id
        AND t.id_akomodasi = a.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND t.id = ?
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
    
    $stmt->bind_result($akomodasi_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $akomodasi_cover, $akomodasi_description, $akomodasi_logo, $akomodasi_name, 
        $akomodasi_location, $akomodasi_active_status, $negara_tamu_id, $negara_tamu_name, 
        $negara_tamu_active_status, $name, $email, $avatar, $phone, $date_of_birth, $identity_type, 
        $identity_number, $gender, $active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Guest data was not found"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT
            (SELECT COUNT(*) 
            FROM laporan_darurat_tidak_valid_tamu ldtvt, pelaporan_darurat_tamu pdt
            WHERE ldtvt.id_pelaporan_darurat_tamu = pdt.id
            AND pdt.id_tamu = ?) + 
            (SELECT COUNT(*)
            FROM laporan_tidak_valid_tamu ltvt, pelaporan_tamu plrt
            WHERE ltvt.id_pelaporan_tamu = plrt.id
            AND plrt.id_tamu = ?)
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
        SELECT a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif,
            nt.id, nt.nama, nt.status_aktif, t.nama, t.email, t.avatar, t.no_telp, t.tanggal_lahir, 
            t.jenis_identitas, t.no_identitas, t.jenis_kelamin, t.status_aktif
        FROM tamu t, akomodasi a, desa_adat da, kecamatan kec, 
            kabupaten kab, provinsi p, negara n, negara nt
        WHERE t.id_negara = nt.id
        AND t.id_akomodasi = a.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND t.id = ?
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
    
    $stmt->bind_result($kerabat_akomodasi_id, $kerabat_desa_adat_id,
        $kerabat_kecamatan_id, $kerabat_kabupaten_id, $kerabat_provinsi_id, 
        $kerabat_negara_id, $kerabat_negara_name, $kerabat_negara_flag, $kerabat_negara_active_status, 
        $kerabat_provinsi_name, $kerabat_provinsi_active_status, $kerabat_kabupaten_name, 
        $kerabat_kabupaten_active_status, $kerabat_kecamatan_name, $kerabat_kecamatan_active_status, 
        $kerabat_desa_adat_name, $kerabat_desa_adat_latitude, $kerabat_desa_adat_longitude, 
        $kerabat_desa_adat_active_status, $kerabat_akomodasi_cover, $kerabat_akomodasi_description, 
        $kerabat_akomodasi_logo, $kerabat_akomodasi_name, $kerabat_akomodasi_location, 
        $kerabat_akomodasi_active_status, $kerabat_negara_tamu_id, $kerabat_negara_tamu_name, 
        $kerabat_negara_tamu_active_status, $kerabat_name, $kerabat_email, $kerabat_avatar, $kerabat_phone, $kerabat_date_of_birth, 
        $kerabat_identity_type, $kerabat_identity_number, $kerabat_gender, $kerabat_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$kerabat_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Family data was not found"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT tnt.token
        FROM token_notifikasi_tamu tnt
        WHERE tnt.id_tamu = ?
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
            FROM laporan_darurat_tidak_valid_tamu ldtvt, pelaporan_darurat_tamu pdt
            WHERE ldtvt.id_pelaporan_darurat_tamu = pdt.id
            AND pdt.id_tamu = ?) + 
            (SELECT COUNT(*)
            FROM laporan_tidak_valid_tamu ltvt, pelaporan_tamu plrt
            WHERE ltvt.id_pelaporan_tamu = plrt.id
            AND plrt.id_tamu = ?)
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
    
    $query = "INSERT INTO kerabat_tamu VALUES (NULL, ?, ?, '0')";
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
    
    $notification_title = "New Family Request";
    $notification_message = "$name added you as a family";
    $notification_photo = $avatar;
    
    $query = "INSERT INTO notifikasi_tamu VALUES (NULL, ?, ?, ?, ?, '0', ?)";
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
            "tamu" => [
                "id" => $user_id,
                "akomodasi" => [
                    "id" => $akomodasi_id,
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
                    "cover" => $akomodasi_cover,
                    "description" => $akomodasi_description,
                    "logo" => $akomodasi_logo,
                    "name" => $akomodasi_name,
                    "location" => $akomodasi_location,
                    "active_status" => boolval($akomodasi_active_status)
                ],
                "name" => $name,
                "email" => $email,
                "avatar" => $avatar,
                "phone" => $phone,
                "date_of_birth" => $date_of_birth,
                "identity_type" => $identity_types[intval($identity_type)],
                "identity_number" => $identity_number,
                "gender" => $gender,
                "block_status" => $block_counter < $max_invalid_report ? false : true,
                "active_status" => boolval($active_status)
            ],
            "family" => [
                "id" => $id_kerabat,
                "akomodasi" => [
                    "id" => $kerabat_akomodasi_id,
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
                    "cover" => $kerabat_akomodasi_cover,
                    "description" => $kerabat_akomodasi_description,
                    "logo" => $kerabat_akomodasi_logo,
                    "name" => $kerabat_akomodasi_name,
                    "location" => $kerabat_akomodasi_location,
                    "active_status" => boolval($kerabat_akomodasi_active_status)
                ],
                "name" => $kerabat_name,
                "email" => $kerabat_email,
                "avatar" => $kerabat_avatar,
                "phone" => $kerabat_phone,
                "date_of_birth" => $kerabat_date_of_birth,
                "identity_type" => $identity_types[intval($kerabat_identity_type)],
                "identity_number" => $kerabat_identity_number,
                "gender" => $kerabat_gender,
                "block_status" => $kerabat_block_counter < $max_invalid_report ? false : true,
                "active_status" => boolval($kerabat_active_status)
            ],
            "status" => 0
        ],
        "message" => "Family data has been successfully added"
    ];
    
    echo json_encode($response);