<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $response = [
            "status_code" => 400, 
            "data" => null, 
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_POST["username"]) && $_POST["username"] && 
        isset($_POST["password"]) && $_POST["password"] &&
        isset($_POST["id_captcha"]) && $_POST["id_captcha"] &&
        isset($_POST["captcha"]) && $_POST["captcha"])) {
        $response = [
            "status_code" => 400, 
            "data" => null, 
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $username = $_POST["username"];
    $password = $_POST["password"];
    $id_captcha = $_POST["id_captcha"];
    $captcha = $_POST["captcha"];
    
    $mysqli = connect_db();
    $query = "
        SELECT ad.id, m.password 
        FROM admin_desa ad, masyarakat m
        WHERE ad.status_aktif = 1
        AND ad.id_masyarakat = m.id
        AND m.username = ?
    ";
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
    
    $stmt->bind_result($user_id, $hash);
    $stmt->fetch();
    $stmt->close();
    
    if (!password_verify($password, $hash)) {
        $response = [
            "status_code" => 400, 
            "data" => null, 
            "message" => "Nama pengguna, kata sandi, atau captcha salah"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT captcha.waktu_kadaluarsa
        FROM captcha 
        WHERE captcha.id = ? 
        AND captcha.captcha = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_captcha, $captcha);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($expired_date_captcha);
    $stmt->fetch();
    $stmt->close();
    
    if (!$expired_date_captcha) {
        $response = [
            "status_code" => 400, 
            "data" => null, 
            "message" => "Nama pengguna, kata sandi, atau captcha salah"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (new DateTime() > new DateTime($expired_date_captcha)) {
        $response = [
            "status_code" => 400, 
            "data" => null, 
            "message" => "Captcha telah kadaluarsa"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "DELETE FROM captcha WHERE captcha.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_captcha);
    
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
    
    $tokens = generate_jwt_token($user_id, "admin_desa");
    $access_token = $tokens["access_token"];
    $refresh_token = $tokens["refresh_token"];
    
    $expired_date_access_token = date_create();
    date_add($expired_date_access_token, date_interval_create_from_date_string("15 minutes"));
    $expired_date_access_token = date_format($expired_date_access_token, "Y-m-d H:i:s");
    
    $expired_date_refresh_token = date_create();
    date_add($expired_date_refresh_token, date_interval_create_from_date_string("3 hours"));
    $expired_date_refresh_token = date_format($expired_date_refresh_token, "Y-m-d H:i:s");
    
    $query = "INSERT INTO access_token_admin_desa VALUES (NULL, ?, ?, ?)";
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
    
    $query = "INSERT INTO refresh_token_admin_desa VALUES (NULL, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $user_id, $refresh_token, $expired_date_refresh_token);
    
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
        "message" => "Proses login berhasil dilakukan"
    ];
    
    echo json_encode($response);