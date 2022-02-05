<?php
    require_once("../config.php");
    
    $now = date("Y-m-d H:i:s");
    $mysqli = connect_db();
    $query = "SELECT c.id FROM captcha c WHERE c.waktu_kadaluarsa < ?";
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
    
    $stmt->store_result();
    $stmt->bind_result($id);
    
    while($stmt->fetch()) {
        $query = "DELETE FROM captcha WHERE captcha.id = ?";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("s", $id);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->close();
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => null,
        "message" => "Captcha kadaluarsa berhasil dihapus"
    ];
    echo json_encode($response);
    exit();