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
        isset($_POST["password"]) && $_POST["password"])) {
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
    
    $mysqli = connect_db();
    $query = "SELECT t.id, t.password FROM tamu t WHERE t.status_aktif = 1 AND t.username = ?";
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
            "message" => "Invalid username or password"
        ];
        echo json_encode($response);
        exit();
    }
    
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
        "message" => "Successfully logged in to the apps"
    ];
    
    echo json_encode($response);