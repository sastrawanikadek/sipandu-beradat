<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    decode_jwt_token(["admin_desa"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_desa"]) && $_POST["id_desa"] &&
        isset($_POST["code"]) && $_POST["code"] &&
        isset($_POST["location"]) && $_POST["location"] &&
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
    $id_desa = $_POST["id_desa"];
    $code = $_POST["code"];
    $location = $_POST["location"];
    $active_status = intval(json_decode($_POST["active_status"]));
    
    $mysqli = connect_db();
    $query = "
        SELECT kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, 
            p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, 
            da.nama, da.latitude, da.longitude, da.status_aktif
        FROM desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n
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
    
    $stmt->bind_result($kecamatan_id, $kabupaten_id, $provinsi_id, $negara_id, 
        $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
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
    
    $query = "SELECT COUNT(*) FROM sirine_desa sd WHERE sd.id_desa = ? AND sd.kode = ? AND sd.id <> ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id_desa, $code, $id);
    
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

    if (isset($_FILES["photo"])) {
        $photo = upload_image("photo");
    } else {
        $query = "SELECT sd.foto FROM sirine_desa sd WHERE sd.id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $id);

        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }

        $stmt->bind_result($photo);
        $stmt->fetch();
        $stmt->close();
    }
    
    $query = "
        UPDATE sirine_desa sd
        SET sd.id_desa = ?,
            sd.kode = ?,
            sd.foto = ?,
            sd.alamat = ?,
            sd.status_aktif = ?
        WHERE sd.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id_desa, $code, $photo, $location, $active_status, $id);
    
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
            "code" => $code,
            "photo" => $photo,
            "location" => $location,
            "active_status" => boolval($active_status)
        ],
        "message" => "Data sirine desa adat berhasil diubah"
    ];
    echo json_encode($response);