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
    
    if (!(isset($_POST["id_banjar"]) && $_POST["id_banjar"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["username"]) && $_POST["username"] &&
        isset($_POST["password"]) && $_POST["password"] &&
        isset($_POST["phone"]) && $_POST["phone"] &&
        isset($_POST["date_of_birth"]) && $_POST["date_of_birth"] &&
        isset($_POST["nik"]) && $_POST["nik"] &&
        isset($_POST["gender"]) && $_POST["gender"] &&
        isset($_POST["category"]) && ($_POST["category"] || $_POST["category"] == 0) &&
        isset($_POST["home_latitude"]) && $_POST["home_latitude"] &&
        isset($_POST["home_longitude"]) && $_POST["home_longitude"])) {
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
    
    $id_banjar = $_POST["id_banjar"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $username = $_POST["username"];
    $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $nik = $_POST["nik"];
    $gender = $_POST["gender"];
    $category = $_POST["category"];
    $home_latitude = $_POST["home_latitude"];
    $home_longitude = $_POST["home_longitude"];
    
    $mysqli = connect_db();
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
    
    $query = "SELECT COUNT(*) FROM masyarakat m WHERE m.username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    
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
            "message" => "Nama pengguna sudah terdaftar"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (isset($_FILES["avatar"])) {
        $avatar = upload_image("avatar");
    } else {
        $avatar = $gender == "l" ? "https://sipanduberadat.com/api/res/images/male_taetna.png" : "https://sipanduberadat.com/api/res/images/female_uz2cfi.png";
    }
    
    $query = "INSERT INTO masyarakat VALUES (NULL, ?, (SELECT sam.id FROM status_aktif_masyarakat sam WHERE sam.nama = 'Aktif' LIMIT 1), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssssssssss", $id_banjar, $name, $email, $username, $hash, $avatar, 
        $phone, $date_of_birth, $nik, $gender, $category, $home_latitude, $home_longitude);
    
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
    
    $tokens = generate_jwt_token($user_id, "masyarakat");
    $access_token = $tokens["access_token"];
    $refresh_token = $tokens["refresh_token"];
    
    $expired_date_access_token = date_create();
    date_add($expired_date_access_token, date_interval_create_from_date_string("15 minutes"));
    $expired_date_access_token = date_format($expired_date_access_token, "Y-m-d H:i:s");
    
    $query = "INSERT INTO access_token_masyarakat VALUES (NULL, ?, ?, ?)";
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
    
    $query = "INSERT INTO refresh_token_masyarakat VALUES (NULL, ?, ?)";
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
        "message" => "Proses registrasi berhasil dilakukan"
    ];
    
    echo json_encode($response);