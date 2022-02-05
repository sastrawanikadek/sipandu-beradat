<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["superadmin", "admin_akomodasi"]);
    
    if (!(isset($_POST["id_akomodasi"]) && $_POST["id_akomodasi"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["phone"]) && $_POST["phone"] &&
        isset($_POST["date_of_birth"]) && $_POST["date_of_birth"] &&
        isset($_POST["nik"]) && $_POST["nik"] &&
        isset($_POST["gender"]) && $_POST["gender"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
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

    $id_akomodasi = $_POST["id_akomodasi"];
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $nik = $_POST["nik"];
    $gender = $_POST["gender"];
    
    $mysqli = connect_db();
    
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.id, kec.nama, kec.status_aktif,
            da.id, da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, 
            a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec, desa_adat da, akomodasi a
        WHERE a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND a.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_akomodasi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_id, $provinsi_name, $provinsi_active_status, $kabupaten_id, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_id, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_id, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, 
        $akomodasi_description, $akomodasi_logo, $akomodasi_name, $akomodasi_location, 
        $akomodasi_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$akomodasi_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data akomodasi tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM pegawai_akomodasi pa WHERE pa.nik = ? AND pa.id_akomodasi = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $nik, $id_akomodasi);
    
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
    
    $avatar = upload_image("avatar");
    
    $query = "INSERT INTO pegawai_akomodasi VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssss", $id_akomodasi, $name, $avatar, $phone, $date_of_birth, $nik, $gender);

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
            "akomodasi" => [
                "id" => $id_akomodasi,
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
            "avatar" => $avatar,
            "phone" => $phone,
            "date_of_birth" => $date_of_birth,
            "nik" => $nik,
            "gender" => $gender,
            "active_status" => true
        ],
        "message" => "Data pegawai akomodasi berhasil ditambahkan"
    ];

    echo json_encode($response);