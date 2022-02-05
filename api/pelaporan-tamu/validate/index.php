<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    require_once("../../helpers/notification.php");
    
    $payload = decode_jwt_token(["masyarakat"]);
    $id = $payload["id"];
    
    if (!(isset($_POST["id_pecalang"]) && $_POST["id_pecalang"] &&
        isset($_POST["id_pelaporan_tamu"]) && $_POST["id_pelaporan_tamu"] 
        && isset($_POST["valid_status"]))) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_pecalang = $_POST["id_pecalang"];
    $id_pelaporan_tamu = $_POST["id_pelaporan_tamu"];
    $valid_status = intval(json_decode($_POST["valid_status"]));
    $valid_status = $valid_status + ($valid_status - 1);
    
    $mysqli = connect_db();
    $query = "
        SELECT m.nama
        FROM pecalang pda, masyarakat m
        WHERE pda.status_aktif = 1 
        AND pda.id_masyarakat = m.id
        AND pda.id = ?
        AND pda.id_masyarakat = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_pecalang, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($pecalang_name);
    $stmt->fetch();
    $stmt->close();
    
    if (!$pecalang_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data pecalang tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT jp.id, jp.nama 
        FROM pelaporan_tamu plt, jenis_pelaporan jp 
        WHERE plt.id_jenis_pelaporan = jp.id
        AND plt.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_tamu);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($jenis_pelaporan_id, $jenis_pelaporan_name);
    $stmt->fetch();
    $stmt->close();
    
    if (!$jenis_pelaporan_name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data pelaporan tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $photo = upload_image("photo");
    
    $query = "
        UPDATE pecalang_pelaporan_tamu plt
        SET plt.foto = ?, 
            plt.status = ?
        WHERE plt.id_pecalang = ?
        AND plt.id_pelaporan_tamu = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss", $photo, $valid_status, $id_pecalang, $id_pelaporan_tamu);
    
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
    
    $query = "
        SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
        FROM pecalang_pelaporan_tamu pdaplt 
        WHERE pdaplt.id_pelaporan_tamu = ? 
        GROUP BY pdaplt.status 
        ORDER BY total DESC
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_tamu);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($status, $total);
    $stmt->fetch();
    $stmt->close();
    
    if ($status && intval($status) == -1) {
        $query = "
            SELECT COUNT(*) 
            FROM laporan_tidak_valid_tamu ltvt 
            WHERE ltvt.id_pelaporan_tamu = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $id_pelaporan_tamu);
        
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
        
        if ($total == 0) {
            $query = "INSERT INTO laporan_tidak_valid_tamu VALUES (NULL, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $id_pelaporan_tamu);
            
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
    } else {
        $query = "
            DELETE FROM laporan_tidak_valid_tamu 
            WHERE id_pelaporan_tamu = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $id_pelaporan_tamu);
        
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
        
        $petugas_fcm_tokens = [];
        $query = "
            SELECT ip.id, ip.status_pelaporan
            FROM instansi_petugas ip
            WHERE ip.status_aktif = '1'
        ";
        $stmt = $mysqli->prepare($query);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->store_result();
        $stmt->bind_result($instansi_id, $instansi_report_status);
        
        while($stmt->fetch()) {
            if (intval($instansi_report_status) == 0) {
                continue;
            } else if (intval($instansi_report_status) == 1) {
                $query = "
                    SELECT COUNT(*)
                    FROM otoritas_pelaporan_instansi opi
                    WHERE opi.id_instansi = ?
                    AND opi.id_jenis_pelaporan = ?
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("ss", $instansi_id, $jenis_pelaporan_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($total_authority);
                $stmt2->fetch();
                $stmt2->close();
                
                if ($total_authority == 0) {
                    continue;
                }
            }
            
            $query = "
                SELECT tnp.token
                FROM token_notifikasi_petugas tnp, petugas pt
                WHERE tnp.id_petugas = pt.id
                AND pt.id_instansi = ?
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $instansi_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($petugas_fcm_token);
            
            while ($stmt2->fetch()) {
                array_push($petugas_fcm_tokens, $petugas_fcm_token);
            }
            
            $stmt2->close();
        }
        
        $stmt->close();
        
        send_notifications($factory, $petugas_fcm_tokens, [
            "notification_type" => "guest-report",
            "notification_title" => $jenis_pelaporan_name,
            "notification_message" => "Telah terjadi $jenis_pelaporan_name di dekatmu yang harus segera dibantu",
            "notification_photo" => $photo,
            "notification_data" => json_encode(["id" => $id_pelaporan_tamu])
        ]);
    }
    
    $query = "
        SELECT tnt.token 
        FROM token_notifikasi_tamu tnt, pelaporan_tamu plt 
        WHERE plt.id_tamu = tnt.id_tamu
        AND plt.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pelaporan_tamu);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($fcm_token);
    $stmt->fetch();
    $stmt->close();
    
    $notification_data = [
        "id" => $id_pelaporan_tamu,
        "type" => 1
    ];
    
    send_notifications($factory, [$fcm_token], [
        "notification_type" => "report",
        "notification_title" => "$jenis_pelaporan_name Report Status",
        "notification_message" => $valid_status == 1 ? "Pecalang $pecalang_name has validate your report" : "Pecalang $pecalang_name determine that your report is invalid",
        "notification_data" => json_encode($notification_data)
    ]);
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Terima kasih telah memvalidasi laporan ini"
    ];
    echo json_encode($response);
    exit();