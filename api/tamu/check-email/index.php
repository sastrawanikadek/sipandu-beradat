<?php
    require_once("../../config.php");
    
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_GET["email"]) && $_GET["email"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!filter_var(filter_var($_GET["email"], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL)) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Invalid email"
        ];
        echo json_encode($response);
        exit();
    }
    
    $email = filter_var($_GET["email"], FILTER_SANITIZE_EMAIL);
    $mysqli = connect_db();
    $query = "
        SELECT t.id
        FROM tamu t
        WHERE t.email = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();
    
    if (!$id) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Account has not been registered yet"
        ];
        echo json_encode($response);
        exit();
    }
    
    $response = [
        "status_code" => 200,
        "data" => $id,
        "message" => "Account has been successfully obtained"
    ];
    echo json_encode($response);
    exit();