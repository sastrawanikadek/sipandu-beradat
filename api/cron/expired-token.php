<?php
    require_once("../config.php");
    
    $now = date("Y-m-d H:i:s");
    $mysqli = connect_db();
    $query = "DELETE FROM access_token_admin_akomodasi WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM access_token_admin_desa WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM access_token_admin_petugas WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM access_token_masyarakat WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM access_token_petugas WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM access_token_superadmin WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM access_token_tamu WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM refresh_token_admin_akomodasi WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM refresh_token_admin_desa WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM refresh_token_admin_petugas WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
    
    $query = "DELETE FROM refresh_token_superadmin WHERE waktu_kadaluarsa < ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $now);
    
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
        "message" => "Captcha kadaluarsa berhasil dihapus"
    ];
    echo json_encode($response);
    exit();