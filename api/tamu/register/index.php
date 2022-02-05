<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_POST["id_akomodasi"]) && $_POST["id_akomodasi"] &&
        isset($_POST["id_negara"]) && $_POST["id_negara"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["username"]) && $_POST["username"] &&
        isset($_POST["password"]) && $_POST["password"] &&
        isset($_POST["phone"]) && $_POST["phone"] &&
        isset($_POST["date_of_birth"]) && $_POST["date_of_birth"] &&
        isset($_POST["identity_type"]) && ($_POST["identity_type"] || $_POST["identity_type"] == 0) &&
        isset($_POST["identity_number"]) && $_POST["identity_number"] &&
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
    
    if ($_POST["date_of_birth"] > date("Y-m-d")) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Tanggal lahir tidak valid"
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
    
    $identity_types = ["Identity Card", "Driving License", "Passport"];

    $id_akomodasi = $_POST["id_akomodasi"];
    $id_negara = $_POST["id_negara"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $username = $_POST["username"];
    $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $identity_type = $_POST["identity_type"];
    $identity_number = $_POST["identity_number"];
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
    
    $query = "SELECT nt.nama, nt.status_aktif FROM negara nt WHERE nt.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_negara);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_tamu_name, $negara_tamu_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$negara_tamu_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data negara tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM tamu t 
        WHERE t.id_akomodasi = ?
        AND ((t.username = ?) OR (t.jenis_identitas = ? AND t.no_identitas = ? AND t.id_negara = ?))
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $id_akomodasi, $username, $identity_type, $identity_number, $id_negara);
    
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
            "message" => "Account already exist"
        ];

        echo json_encode($response);
        exit();
    }
    
    if (isset($_FILES["avatar"])) {
        $avatar = upload_image("avatar");
    } else {
        $avatar = $gender == "l" ? "https://sipanduberadat.com/api/res/images/male_taetna.png" : "https://sipanduberadat.com/api/res/images/female_uz2cfi.png";
    }
    
    $query = "INSERT INTO tamu VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssssssss", $id_akomodasi, $id_negara, $name, $email, $username, 
        $hash, $avatar, $phone, $date_of_birth, $identity_type, $identity_number, $gender);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
            
    $user_id = $mysqli->insert_id;
    $stmt->close();
    
    $tokens = generate_jwt_token($user_id, "tamu");
    $access_token = $tokens["access_token"];
    $refresh_token = $tokens["refresh_token"];
    
    $expired_date_access_token = date_create();
    date_add($expired_date_access_token, date_interval_create_from_date_string("15 minutes"));
    $expired_date_access_token = date_format($expired_date_access_token, "Y-m-d H:i:s");
    
    $query = "INSERT INTO access_token_tamu VALUES (NULL, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $user_id, $access_token, $expired_date_access_token);
    
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
    
    $query = "INSERT INTO refresh_token_tamu VALUES (NULL, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $user_id, $refresh_token);
    
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
            "access_token" => $access_token,
            "refresh_token" => $refresh_token
        ],
        "message" => "Account has been successfully registered"
    ];

    echo json_encode($response);