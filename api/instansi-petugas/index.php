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
    
    $data = [];
    $params = [];
    $mysqli = connect_db();
    
    $query = "
        SELECT ip.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, jip.id, jip.nama, jip.status_aktif, ip.nama, 
            ip.status_pelaporan, ip.status_aktif
        FROM instansi_petugas ip, kecamatan kec, kabupaten kab, provinsi p, 
            negara n, jenis_instansi_petugas jip
        WHERE ip.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND ip.id_jenis_instansi = jip.id
    ";
    
    if (isset($_GET["active_status"])) {
        array_push($params, json_decode($_GET["active_status"]));
        $query .= " AND ip.status_aktif = ?";
    }
    
    if (isset($_GET["report_status"])) {
        array_push($params, $_GET["report_status"]);
        $query .= " AND ip.status_pelaporan = ?";
    }
    
    if (count($params) > 0) {
        $types = str_repeat("s", count($params));
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
    
    $stmt->bind_result($id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, 
        $provinsi_active_status, $kabupaten_name, $kabupaten_active_status, 
        $kecamatan_name, $kecamatan_active_status, $jenis_instansi_id, 
        $jenis_instansi_name, $jenis_instansi_active_status, $name, 
        $report_status, $active_status);
    
    while($stmt->fetch()) {
        array_push($data, [
            "id" => $id,
            "kecamatan" => [
                "id" => $kecamatan_id,
                "kabupaten" => [
                    "id" => $kabupaten_id,
                    "provinsi" => [
                        "id" => $provinsi_id,
                        "negara" => [
                            "id" => $negara_id,
                            "name" => $negara_name,
                            "flag" => $negara_flag,
                            "active_status" => boolval($negara_active_status)
                        ],
                        "name" => $provinsi_name,
                        "active_status" => boolval($provinsi_active_status)
                    ],
                    "name" => $kabupaten_name,
                    "active_status" => boolval($kabupaten_active_status)
                ],
                "name" => $kecamatan_name,
                "active_status" => boolval($kecamatan_active_status)
            ],
            "jenis_instansi" => [
                "id" => $jenis_instansi_id,
                "name" => $jenis_instansi_name,
                "active_status" => boolval($jenis_instansi_active_status)
            ],
            "name" => $name,
            "report_status" => $report_status,
            "active_status" => boolval($active_status)
        ]);
    }
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data instansi petugas berhasil diperoleh"
    ];
    echo json_encode($response);