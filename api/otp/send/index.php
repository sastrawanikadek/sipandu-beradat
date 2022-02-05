<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/mail.php");
    
    if (!((isset($_POST["XAT"]) && $_POST["XAT"]) || 
        (isset($_POST["id"]) && $_POST["id"] && 
        isset($_POST["role"]) && $_POST["role"]))) {
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
        SELECT t.email
        FROM $role t
        WHERE t.id = ?
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
    
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
    
    do {
        $otp = mt_rand(1000, 9999);
        $query = "SELECT COUNT(*) FROM otp_$role t WHERE t.otp = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $otp);
        
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
    } while ($total > 0);
    
    $subject = "One Time Password (OTP) Sipandu Beradat";
    $message = "<h4 style='font-weight: 400;'>Hello <b>$email</b>!</h4>
    <p style='margin-bottom: 16px;'>To authenticate, please use the following One Time Password (OTP)</p>
    <h2 style='margin-bottom: 16px;'>$otp</h2/>
    <p style='margin-bottom: 32px;'>This OTP will be expire in 15 minutes, please do not share this OTP with anyone to secure your account.</p>
    <h4 style='font-weight: 400;'>Cheers, <b>Sipandu Beradat Team</b></h4>";
    
    $expired_date = date_create();
    date_add($expired_date, date_interval_create_from_date_string("15 minutes"));
    $expired_date = date_format($expired_date, "Y-m-d H:i:s");
    
    $query = "INSERT INTO otp_$role VALUES (NULL, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id, $otp, $expired_date);
    
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
    
    if (!send_mail($subject, $email, $message)) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => $role == "tamu" ? "Failed to send OTP, please try again later" : "Kode OTP gagal dikirimkan, silahkan coba lagi nanti"
        ];
        echo json_encode($response);
        exit();
    }
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => $role == "tamu" ? "Successfully send the OTP" : "Kode OTP berhasil dikirimkan"
    ];
    echo json_encode($response);