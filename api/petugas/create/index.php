<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["superadmin", "admin_petugas"]);
    
    if (!(isset($_POST["id_instansi"]) && $_POST["id_instansi"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["username"]) && $_POST["username"] &&
        isset($_POST["password"]) && $_POST["password"] &&
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

    $id_instansi = $_POST["id_instansi"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $username = $_POST["username"];
    $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $nik = $_POST["nik"];
    $gender = $_POST["gender"];
    
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
        WHERE (pt.nik = ? AND pt.id_instansi = ?)
        OR pt.username = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $nik, $id_instansi, $username);
    
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
    
    $query = "INSERT INTO petugas VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssssss", $id_instansi, $name, $email, $username, $hash, $nik, 
        $avatar, $phone, $date_of_birth, $gender);

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
            "active_status" => true
        ],
        "message" => "Data petugas instansi berhasil ditambahkan"
    ];

    echo json_encode($response);