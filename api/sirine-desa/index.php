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
        SELECT sd.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, 
            p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, 
            da.nama, da.latitude, da.longitude, da.status_aktif, sd.kode, sd.foto,
            sd.alamat, sd.status_aktif
        FROM sirine_desa sd, desa_adat da, kecamatan kec, kabupaten kab, 
            provinsi p, negara n
        WHERE sd.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
    ";

    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= " AND sd.status_aktif = ?";
        array_push($params, $active_status_param);
    }
    
    if (isset($_GET["id_desa"])) {
        $query .= " AND sd.id_desa = ?";
        array_push($params, $_GET["id_desa"]);
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

    $stmt->bind_result($id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, $negara_id, 
        $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $code, $photo, $location, $active_status);
    $data = [];

    while($stmt->fetch()) {
        array_push($data, [
            "id" => $id,
            "desa_adat" => [
                "id" => $desa_adat_id,
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
                "name" => $desa_adat_name,
                "latitude" => $desa_adat_latitude,
                "longitude" => $desa_adat_longitude,
                "active_status" => boolval($desa_adat_active_status)
            ],
            "code" => $code,
            "photo" => $photo,
            "location" => $location,
            "active_status" => boolval($active_status)
        ]);
    }

    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data sirine desa adat berhasil diperoleh"
    ];

    echo json_encode($response);