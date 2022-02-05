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
    
    $mysqli = connect_db();
    $query = "
        SELECT p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif
        FROM provinsi p, negara n
        WHERE p.id_negara = n.id
    ";

    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= "AND p.status_aktif = ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $active_status_param);
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

    $stmt->bind_result($id, $negara_id, $negara_name, $negara_flag, $negara_active_status, $name, $active_status);
    $data = [];

    while($stmt->fetch()) {
        array_push($data, [
            "id" => $id, 
            "negara" => [
                "id" => $negara_id,
                "name" => $negara_name,
                "flag" => $negara_flag,
                "active_status" => boolval($negara_active_status)
            ],
            "name" => $name, 
            "active_status" => boolval($active_status)
        ]);
    }

    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data provinsi berhasil diperoleh"
    ];

    echo json_encode($response);