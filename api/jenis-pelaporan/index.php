<?php
    require_once("../config.php");

    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $params = [];
    $mysqli = connect_db();
    $query = "
        SELECT jp.id, jp.nama, jp.icon, jp.status_darurat, jp.status_aktif
        FROM jenis_pelaporan jp
        WHERE TRUE
    ";

    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= " AND jp.status_aktif = ?";
        array_push($params, $active_status_param);
    }
    
    if (isset($_GET["emergency_status"])) {
        $emergency_status_param = json_decode($_GET["emergency_status"]);
        $query .= " AND jp.status_darurat = ?";
        array_push($params, $emergency_status_param);
    }
    
    if (count($params) > 0) {
        $types = str_repeat('s', count($params));
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $mysqli->prepare($query);
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

    $stmt->bind_result($id, $name, $icon, $emergency_status, $active_status);
    $data = [];

    while($stmt->fetch()) {
        array_push($data, [
            "id" => $id, 
            "name" => $name, 
            "icon" => $icon,
            "emergency_status" => boolval($emergency_status),
            "active_status" => boolval($active_status)
        ]);
    }

    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data jenis pelaporan berhasil diperoleh"
    ];

    echo json_encode($response);