<?php
    require_once("../config.php");
    
    $now = date("Y-m-d H:i:s");
    $mysqli = connect_db();
    $query = "
        UPDATE check_in_tamu cit, tamu t
        SET t.status_aktif = 0
        WHERE cit.id_tamu = t.id
        AND cit.waktu_selesai <= ?
    ";
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
        "message" => "Tamu sudah checkout"
    ];
    echo json_encode($response);
    exit();