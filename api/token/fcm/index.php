<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["masyarakat", "petugas", "tamu"]);
    $id = $payload["id"];
    $role = $payload["role"];
    
    if (!(isset($_POST["token"]) && $_POST["token"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $token = $_POST["token"];
    
    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM token_notifikasi_$role t WHERE t.token = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $token);
    
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
        $query = "
            UPDATE token_notifikasi_$role t
            SET t.id_$role = ?
            WHERE t.token = ?
        ";
    } else {
        $query = "INSERT INTO token_notifikasi_$role VALUES (NULL, ?, ?)";
    }

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $token);
    
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
        "data" => null,
        "message" => "Token FCM berhasil didaftarkan"
    ];
    echo json_encode($response);