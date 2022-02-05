<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    if (!((isset($_POST["XAT"]) && $_POST["XAT"]) || 
        (isset($_POST["id"]) && $_POST["id"] && 
        isset($_POST["role"]) && $_POST["role"]) && 
        isset($_POST["code"]) && $_POST["code"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (isset($_POST["XAT"])) {
        $payload = decode_jwt_token(["masyarakat", "petugas", "tamu", "admin_desa", "admin_akomodasi", "admin_petugas", "superadmin"]);
        $role = $payload["role"];
        $id = $payload["id"];
    }
    
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
    }
    
    if (isset($_POST["role"])) {
        $role = $_POST["role"];
    }
    
    if (strlen($_POST["code"]) != 4) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => $role == "tamu" ? "Invalid OTP" : "Kode OTP tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    $now = date("Y-m-d H:i:s");
    $code = $_POST["code"];
    $mysqli = connect_db();
    
    if ($role == "admin_desa") {
        $query = "
            SELECT ad.id_masyarakat
            FROM admin_desa ad
            WHERE ad.id = ?
        ";
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
        
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        
        $role = "masyarakat";
    } else if ($role == "admin_petugas") {
        $query = "
            SELECT ap.id_petugas
            FROM admin_petugas ap
            WHERE ap.id = ?
        ";
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
        
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        
        $role = "petugas";
    }
    
    $query = "
        SELECT t.id
        FROM otp_$role t
        WHERE t.id_$role = ?
        AND t.otp = ?
        AND t.waktu_kadaluarsa >= ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id, $code, $now);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($otp_id);
    $stmt->fetch();
    $stmt->close();
    
    if (!$otp_id) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => $role == "tamu" ? "Invalid or expired OTP" : "Kode OTP salah atau kadaluarsa"
        ];
        echo json_encode($response);
        exit();
    }
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => $role == "tamu" ? "The OTP is valid" : "Kode OTP sesuai"
    ];
    echo json_encode($response);
    