<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["admin_petugas"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_instansi"]) && $_POST["id_instansi"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["phone"]) && $_POST["phone"] &&
        isset($_POST["date_of_birth"]) && $_POST["date_of_birth"] &&
        isset($_POST["nik"]) && $_POST["nik"] &&
        isset($_POST["gender"]) && $_POST["gender"] &&
        isset($_POST["active_status"]))) {
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

    $id = $_POST["id"];
    $id_instansi = $_POST["id_instansi"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $nik = $_POST["nik"];
    $gender = $_POST["gender"];
    $active_status = intval(json_decode($_POST["active_status"]));
    
    $mysqli = connect_db();
    
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.id, kec.nama, kec.status_aktif,
            jip.id, jip.nama, jip.status_aktif, ip.nama, ip.status_pelaporan, 
            ip.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec, 
            jenis_instansi_petugas jip, instansi_petugas ip
        WHERE ip.id_jenis_instansi = jip.id
        AND ip.id_kecamatan = kec.id
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
    
    $stmt->bind_result($negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_id, $provinsi_name, $provinsi_active_status, $kabupaten_id, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_id, $kecamatan_name, 
        $kecamatan_active_status, $jenis_instansi_id, $jenis_instansi_name, 
        $jenis_instansi_active_status, $instansi_name, $instansi_report_status, 
        $instansi_active_status);
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
        SELECT COUNT(*) 
        FROM petugas pt 
        WHERE pt.nik = ? 
        AND pt.id_instansi = ?
        AND pt.id <> ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $nik, $id_instansi, $id);
    
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
    
    if (isset($_FILES["avatar"])) {
        $avatar = upload_image("avatar");
    } else {
        $query = "SELECT pt.avatar FROM petugas pt WHERE pt.id = ?";
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
        UPDATE petugas pt
        SET pt.id_instansi = ?,
            pt.nama = ?,
            pt.email = ?,
            pt.nik = ?,
            pt.avatar = ?,
            pt.no_telp = ?,
            pt.tanggal_lahir = ?,
            pt.jenis_kelamin = ?,
            pt.status_aktif = ?
        WHERE pt.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssssss", $id_instansi, $name, $email, $nik, $avatar, $phone, 
        $date_of_birth, $gender, $active_status, $id);

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
                    "active_status" => $jenis_instansi_active_status
                ],
                "name" => $instansi_name,
                "report_status" => $instansi_report_status,
                "active_status" => boolval($instansi_active_status)
            ],
            "name" => $name,
            "email" => $email,
            "avatar" => $avatar,
            "phone" => $phone,
            "date_of_birth" => $date_of_birth,
            "nik" => $nik,
            "gender" => $gender,
            "active_status" => boolval($active_status)
        ],
        "message" => "Data petugas instansi berhasil diubah"
    ];

    echo json_encode($response);