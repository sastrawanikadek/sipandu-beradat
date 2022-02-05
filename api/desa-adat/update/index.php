<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_kecamatan"]) && $_POST["id_kecamatan"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["latitude"]) && $_POST["latitude"] &&
        isset($_POST["longitude"]) && $_POST["longitude"] &&
        isset($_POST["active_status"]))) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    $id_kecamatan = $_POST["id_kecamatan"];
    $name = $_POST["name"];
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    $active_status = intval(json_decode($_POST["active_status"]));

    $mysqli = connect_db();
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec
        WHERE kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND kec.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_kecamatan);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_id, $provinsi_name, $provinsi_active_status, $kabupaten_id, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
        $kecamatan_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$kecamatan_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data kecamatan tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM desa_adat da WHERE da.nama = ? AND da.id_kecamatan = ? AND da.id <> ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $name, $id_kecamatan, $id);
    
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
    
    if ($total > 0) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data sudah ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "
        UPDATE desa_adat da 
        SET
            da.id_kecamatan = ?,
            da.nama = ?,
            da.latitude = ?,
            da.longitude = ?,
            da.status_aktif = ? 
        WHERE da.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id_kecamatan, $name, $latitude, $longitude, $active_status, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $response = [
        "status_code" => 200, 
        "data" => [
            "id" => $id,
            "kecamatan" => [
                "id" => $id_kecamatan,
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
            "name" => $name,
            "latitude" => $latitude,
            "longitude" => $longitude,
            "active_status" => boolval($active_status)
        ],
        "message" => "Data desa adat berhasil diubah"
    ];

    echo json_encode($response);