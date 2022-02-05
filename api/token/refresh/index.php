<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    use Firebase\JWT\JWT;
    
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $response = [
            "status_code" => 400, 
            "data" => null, 
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }

    if (!isset($_POST["XAT"])) {
        $response = [
            "status_code" => 401,
            "data" => null,
            "message" => "Mohon masuk terlebih dahulu"
        ];
        echo json_encode($response);
        exit();
    }

    $XAT = explode(" ", $_POST["XAT"]);
    if (count($XAT) != 2 || $XAT[0] != "Bearer") {
        $response = [
            "status_code" => 401,
            "data" => null,
            "message" => "Refresh token tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    try {
        $token = $XAT[1];
        $payload = JWT::decode($token, $_ENV["REFRESH_TOKEN_SECRET"], ["HS256"]);
        $id = $payload->id;
        $role = $payload->role;

        $mysqli = connect_db();
        $query = "SELECT COUNT(*) FROM refresh_token_$role WHERE token = ?";
        
        if (!in_array($role, ["masyarakat", "tamu", "pecalang", "petugas"], true)) {
            $now = date("Y-m-d H:i:s");
            $query .= " AND waktu_kadaluarsa >= ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $token, $now);
        } else {
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $token);
        }

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

        if ($total == 0) {
            $response = [
                "status_code" => 401,
                "data" => null,
                "message" => "Refresh token tidak valid"
            ];
            echo json_encode($response);
            exit();
        }

        $stmt->close();
        
        $query = "DELETE FROM access_token_$role WHERE id_$role = ?";
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
        
        $stmt->close();
        
        $query = "DELETE FROM refresh_token_$role WHERE id_$role = ?";
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
        
        $stmt->close();

        $tokens = generate_jwt_token($id, $role);
        $access_token = $tokens["access_token"];
        $refresh_token = $tokens["refresh_token"];
        
        $expired_date_access_token = date_create();
        date_add($expired_date_access_token, date_interval_create_from_date_string("15 minutes"));
        $expired_date_access_token = date_format($expired_date_access_token, "Y-m-d H:i:s");
        
        $expired_date_refresh_token = date_create();
        date_add($expired_date_refresh_token, date_interval_create_from_date_string("3 hours"));
        $expired_date_refresh_token = date_format($expired_date_refresh_token, "Y-m-d H:i:s");
        
        $query = "INSERT INTO access_token_$role VALUES (NULL, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $id, $access_token, $expired_date_access_token);
        
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
        
        if (!in_array($role, ["masyarakat", "tamu", "pecalang", "petugas"], true)) {
            $query = "INSERT INTO refresh_token_$role VALUES (NULL, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $id, $refresh_token, $expired_date_refresh_token);
        } else {
            $query = "INSERT INTO refresh_token_$role VALUES (NULL, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $id, $refresh_token);
        }
        
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
            "message" => "Token berhasil diperbaharui"
        ];
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        $response = [
            "status_code" => 401,
            "data" => null,
            "message" => "Refresh token tidak valid"
        ];
        echo json_encode($response);
        exit();
    }