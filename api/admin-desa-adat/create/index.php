<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["superadmin", "admin_desa"]);
    $id = $payload["id"];
    $role = $payload["role"];
    
    $mysqli = connect_db();
    
    if ($role == "admin_desa") {
        $query = "SELECT ad.status_super_admin FROM admin_desa ad WHERE ad.id = ?";
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
        
        $stmt->bind_result($super_admin_status);
        $stmt->fetch();
        $stmt->close();
        
        if ($super_admin_status == 0) {
            $response = [
                "status_code" => 400,
                "data" => null,
                "message" => "Halaman tidak ditemukan"
            ];
            echo json_encode($response);
            exit();
        }
    }
    
    if (!(isset($_POST["id_masyarakat"]) && $_POST["id_masyarakat"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_masyarakat = $_POST["id_masyarakat"];
    $super_admin_status = $role == "superadmin" ? 1 : 0;
    
    $query = "
        SELECT b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, m.nama, m.email, m.avatar, m.no_telp, 
            m.tanggal_lahir, m.nik, m.jenis_kelamin, m.kategori, m.status_valid
        FROM masyarakat m, banjar b, desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n, 
            status_aktif_masyarakat sam
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
    
    $stmt->bind_result($banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, $active_status_active_status, 
        $name, $email, $avatar, $phone, $date_of_birth, $nik, $gender, $category, $valid_status);
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
    
    $query = "SELECT COUNT(*) FROM admin_desa ad WHERE ad.id_masyarakat = ?";
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
    
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    
    if ($total > 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data admin desa sudah ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $category_text = $category == 0 ? "Krama Wid" : "Krama Tamiu";
    
    if ($category == 2) {
        $category_text = "Tamiu";
    }
    
    $query = "INSERT INTO admin_desa VALUES (NULL, ?, 1, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_masyarakat, $super_admin_status);
    
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
    $user_id = $mysqli->insert_id;
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $user_id,
            "masyarakat" => [
                "id" => $id_masyarakat,
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
                "valid_status" => boolval($valid_status)
            ],
            "active_status" => true,
            "super_admin_status" => boolval($super_admin_status)
        ],
        "message" => "Proses registrasi berhasil dilakukan"
    ];
    
    echo json_encode($response);