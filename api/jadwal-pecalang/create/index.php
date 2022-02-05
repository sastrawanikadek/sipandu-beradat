<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["admin_desa"]);
    $admin_id = $payload["id"];
    
    if (!(((isset($_POST["id_pecalang"]) && $_POST["id_pecalang"]) || (isset($_POST["all_pecalang_status"]) && json_decode($_POST["all_pecalang_status"]))) && isset($_POST["holiday_status"]) &&
        isset($_POST["days"]) && gettype(json_decode($_POST["days"])) == "array")) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $mysqli = connect_db();
    $pecalang_ids = [];
    
    if (isset($_POST["id_pecalang"]) && $_POST["id_pecalang"]) {
        array_push($pecalang_ids, $_POST["id_pecalang"]);
    } else {
        $query = "
            SELECT pda.id
            FROM pecalang pda, masyarakat m, banjar b
            WHERE pda.status_aktif = 1
            AND pda.id_masyarakat = m.id
            AND m.id_banjar = b.id
            AND b.id_desa = (
                SELECT b.id_desa
                FROM masyarakat m, banjar b
                WHERE m.id_banjar = b.id
                AND m.id = ?
            )
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $admin_id);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($pecalang_id);
        
        while ($stmt->fetch()) {
            array_push($pecalang_ids, $pecalang_id);
        }
        
        $stmt->close();
    }
    
    foreach ($pecalang_ids as $pecalang_id) {
        $query = "DELETE FROM jadwal_pecalang WHERE id_pecalang = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $pecalang_id);
        
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
    }
    
    $holiday_status = json_decode($_POST["holiday_status"]);
    $days = json_decode($_POST["days"]);
    sort($days);
    
    if ($holiday_status) {
        $last_day_of_month = date("t");
        $start_date = "";
        $end_date = "";
        
        for ($i = 1; $i <= $last_day_of_month; $i++) {
            $today = date("Y-m-").sprintf("%02d", $i);
            
            if (in_array($today, $days)) {
                if (!$start_date) {
                    continue;
                }
                
                foreach ($pecalang_ids as $pecalang_id) {
                    $query = "INSERT INTO jadwal_pecalang VALUES (NULL, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("sss", $pecalang_id, $start_date, $end_date);
                    
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
                }
                
                $start_date = "";
                $end_date = "";
                continue;
            }
            
            if (!$start_date) {
                $start_date = $today;
                $end_date = $today;
                continue;
            }
            
            $yesterday = new DateTime($end_date);
            $interval = $yesterday->diff(new DateTime($today), true);
            
            if (intval($interval->format("%a")) <= 1) {
                $end_date = $today;
            }
        }
        
        if ($start_date) {
            foreach ($pecalang_ids as $pecalang_id) {
                $query = "INSERT INTO jadwal_pecalang VALUES (NULL, ?, ?, ?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("sss", $pecalang_id, $start_date, $end_date);
                
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
            }
        }
    } else {
        $start_date = "";
        $end_date = "";
    
        foreach ($days as $day) {
            if (!$start_date) {
                $start_date = $day;
                $end_date = $day;
                continue;
            }
            
            $yesterday = new DateTime($end_date);
            $today = new DateTime($day);
            $interval = $yesterday->diff($today, true);
            
            if (intval($interval->format("%a")) > 1) {
                foreach ($pecalang_ids as $pecalang_id) {
                    $query = "INSERT INTO jadwal_pecalang VALUES (NULL, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("sss", $pecalang_id, $start_date, $end_date);
                    
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
                }
                
                $start_date = $day;
                $end_date = $day;
            } else {
                $end_date = $day;
            }
        }
        
        foreach ($pecalang_ids as $pecalang_id) {
            $query = "INSERT INTO jadwal_pecalang VALUES (NULL, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $pecalang_id, $start_date, $end_date);
            
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
        }
    }
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Data jadwal pecalang desa adat berhasil ditambahkan"
    ];
    
    echo json_encode($response);