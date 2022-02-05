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
        SELECT jip.id, jip.nama, jip.status_aktif
        FROM jenis_instansi_petugas jip 
    ";

    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= "WHERE jip.status_aktif = ?";

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

    $stmt->bind_result($id, $name, $active_status);
    $data = [];

    while($stmt->fetch()) {
        array_push($data, [
            "id" => $id, 
            "name" => $name, 
            "active_status" => boolval($active_status)
        ]);
    }

    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data jenis instansi petugas berhasil diperoleh"
    ];

    echo json_encode($response);