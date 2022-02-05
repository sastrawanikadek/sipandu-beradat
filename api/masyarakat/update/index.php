<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    decode_jwt_token(["admin_desa"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_banjar"]) && $_POST["id_banjar"] &&
        isset($_POST["active_status"]) && $_POST["active_status"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["phone"]) && $_POST["phone"] &&
        isset($_POST["date_of_birth"]) && $_POST["date_of_birth"] &&
        isset($_POST["nik"]) && $_POST["nik"] &&
        isset($_POST["gender"]) && $_POST["gender"] &&
        isset($_POST["category"]) && ($_POST["category"] || $_POST["category"] == 0) &&
        isset($_POST["valid_status"]))) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!filter_var(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL)) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Email tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (strlen($_POST["phone"]) < 10 || strlen($_POST["phone"]) > 13) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "No. telepon harus terdiri dari 10-13 angka"
        ];
        echo json_encode($response);
        exit();
    }
    
    if ($_POST["date_of_birth"] > date("Y-m-d")) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Tanggal lahir tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (strlen($_POST["nik"]) != 16) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "NIK harus terdiri dari 16 angka"
        ];
        echo json_encode($response);
        exit();
    }
    
    if ($_POST["gender"] != "l" && $_POST["gender"] != "p") {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Jenis kelamin harus salah satu dari l atau p"
        ];
        echo json_encode($response);
        exit();
    }
    
    if ($_POST["category"] < 0 || $_POST["category"] > 2) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Kategori krama tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    $id_banjar = $_POST["id_banjar"];
    $active_status_id = $_POST["active_status"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $nik = $_POST["nik"];
    $gender = $_POST["gender"];
    $category = $_POST["category"];
    $valid_status = intval(json_decode($_POST["valid_status"]));
    $category_text = intval($category) == 0 ? "Krama Wid" : "Krama Tamiu";
    
    if (intval($category) == 2) {
        $category_text = "Tamiu";
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
        SELECT da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif
        FROM banjar b, desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n
        WHERE b.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND b.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_banjar);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $banjar_name, $banjar_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$banjar_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data banjar tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (isset($_FILES["avatar"])) {
        $avatar = upload_image("avatar");
    } else {
        $query = "SELECT m.avatar FROM masyarakat m WHERE m.id = ?";
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
        
        $stmt->bind_result($avatar);
        $stmt->fetch();
        $stmt->close();
    }
    
    $query = "
        UPDATE masyarakat m
        SET m.id_banjar = ?,
            m.id_status_aktif = ?,
            m.nama = ?,
            m.email = ?,
            m.avatar = ?,
            m.no_telp = ?,
            m.tanggal_lahir = ?,
            m.nik = ?,
            m.jenis_kelamin = ?,
            m.kategori = ?,
            m.status_valid = ?
        WHERE m.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssssssss", $id_banjar, $active_status_id, $name, $email, $avatar, $phone, 
        $date_of_birth, $nik, $gender, $category, $valid_status, $id);
    
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
    
    $query = "
        SELECT sam.nama, sam.status, sam.status_aktif 
        FROM status_aktif_masyarakat sam 
        WHERE sam.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $active_status_id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($active_status_name, $active_status_status, $active_status_active_status);
    $stmt->fetch();
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "banjar" => [
                "id" => $id_banjar,
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
        "message" => "Data masyarakat berhasil diubah"
    ];
    
    echo json_encode($response);