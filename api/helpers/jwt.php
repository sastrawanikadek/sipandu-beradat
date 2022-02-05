<?php
    use Firebase\JWT\JWT;

    function generate_jwt_token(int $id, string $role) {
        $access_token_payload = [
            "id" => $id,
            "role" => $role,
            "iat" => time(),
            "exp" => time() + (15 * 60)
        ];

        $refresh_token_payload = [
            "id" => $id,
            "role" => $role,
            "iat" => time()
        ];

        if (!in_array($role, ["masyarakat", "tamu", "petugas"], true)) {
            $refresh_token_payload["exp"] = time() + (3 * 60 * 60);
        }

        $access_token = JWT::encode($access_token_payload, $_ENV["ACCESS_TOKEN_SECRET"]);
        $refresh_token = JWT::encode($refresh_token_payload, $_ENV["REFRESH_TOKEN_SECRET"]);

        return [
            "access_token" => $access_token,
            "refresh_token" => $refresh_token
        ];
    }

    function decode_jwt_token(array $allowed_roles) {
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
                "message" => "Akses token tidak valid"
            ];
            echo json_encode($response);
            exit();
        }

        try {
            $token = $XAT[1];
            $payload = JWT::decode($token, $_ENV["ACCESS_TOKEN_SECRET"], ["HS256"]);
            $id = $payload->id;
            $role = $payload->role;
            
            if (!in_array($role, $allowed_roles, true)) {
                $response = [
                    "status_code" => 401,
                    "data" => null,
                    "message" => "Akses token tidak valid"
                ];
                echo json_encode($response);
                exit();
            }

            $now = date("Y-m-d H:i:s");
            $query = "SELECT COUNT(*) FROM access_token_$role t WHERE t.token = ? AND t.waktu_kadaluarsa >= ?";
            $mysqli = connect_db();
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $token, $now);

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
                    "message" => "Akses token tidak valid"
                ];
                echo json_encode($response);
                exit();
            }

            $stmt->close();

            return ["id" => $id, "role" => $role];
        } catch (Exception $e) {
            $response = [
                "status_code" => 401,
                "data" => null,
                "message" => "Akses token tidak valid"
            ];
            echo json_encode($response);
            exit();
        }
    }