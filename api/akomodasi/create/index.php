<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["id_desa"]) && $_POST["id_desa"] &&
        isset($_POST["description"]) && $_POST["description"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["location"]) && $_POST["location"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $id_desa = $_POST["id_desa"];
    $description = $_POST["description"];
    $name = $_POST["name"];
    $location = $_POST["location"];
    
    $mysqli = connect_db();
    
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.id, kec.nama, kec.status_aktif,
            da.nama, da.latitude, da.longitude, da.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec, desa_adat da
        WHERE da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND da.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
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
        $kabupaten_name, $kabupaten_active_status, $kecamatan_id, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$desa_adat_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data desa adat tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM akomodasi a WHERE a.nama = ? AND a.id_desa = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $name, $id_desa);
    
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
    
    $cover = upload_image("cover");
    $logo = upload_image("logo");
    
    $query = "INSERT INTO akomodasi VALUES (NULL, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id_desa, $cover, $description, $logo, $name, $location);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
            
    $id = $mysqli->insert_id;
    $response = [
        "status_code" => 200, 
        "data" => [
            "id" => $id,
            "desa_adat" => [
                "id" => $id_desa,
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
            "cover" => $cover,
            "description" => $description,
            "logo" => $logo,
            "name" => $name,
            "location" => $location,
            "active_status" => true
        ],
        "message" => "Data akomodasi berhasil ditambahkan"
    ];

    echo json_encode($response);