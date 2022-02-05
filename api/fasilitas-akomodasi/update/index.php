<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["superadmin", "admin_akomodasi"]);
    
    if (!(isset($_POST["id_akomodasi"]) && $_POST["id_akomodasi"] &&
        isset($_POST["facilities"]) && gettype(json_decode($_POST["facilities"])) == "array")) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $id_akomodasi = $_POST["id_akomodasi"];
    $facilities_id = json_decode($_POST["facilities"]);
    
    $mysqli = connect_db();
    
    $query = "
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.id, kec.nama, kec.status_aktif,
            da.id, da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, 
            a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec, desa_adat da, 
            akomodasi a
        WHERE a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND a.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_akomodasi);
    
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
        $kecamatan_active_status, $desa_adat_id, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, 
        $akomodasi_description, $akomodasi_logo, $akomodasi_name, 
        $akomodasi_location, $akomodasi_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$akomodasi_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data akomodasi tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "DELETE FROM detail_fasilitas WHERE id_akomodasi = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_akomodasi);
    
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
    
    $facilities = [];
    
    foreach ($facilities_id as $facility_id) {
        $query = "
            SELECT f.icon, f.nama, f.status_aktif 
            FROM fasilitas f 
            WHERE f.status_aktif = 1 
            AND f.id = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $facility_id);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500, 
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
        
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($facility_icon, $facility_name, $facility_active_status);
        $stmt->fetch();
        $stmt->close();
        
        if (!$facility_name) {
            $response = [
                "status_code" => 400, 
                "data" => null,
                "message" => "Data fasilitas tidak ada"
            ];
        
            echo json_encode($response);
            exit();
        }
        
        $query = "INSERT INTO detail_fasilitas VALUES (NULL, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $id_akomodasi, $facility_id);
    
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
        
        array_push($facilities, [
            "id" => $facility_id, 
            "icon" => $facility_icon, 
            "name" => $facility_name, 
            "active_status" => boolval($facility_active_status)
        ]);
    }
    
    $response = [
        "status_code" => 200, 
        "data" => [
            "akomodasi" => [
                "id" => $id_akomodasi,
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
                "cover" => $akomodasi_cover,
                "description" => $akomodasi_description,
                "logo" => $akomodasi_logo,
                "name" => $akomodasi_name,
                "location" => $akomodasi_location,
                "active_status" => boolval($akomodasi_active_status)
            ],
            "facilities" => $facilities
        ],
        "message" => "Data fasilitas akomodasi berhasil diubah"
    ];

    echo json_encode($response);