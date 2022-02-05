<?php
    require_once("../../config.php");

    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_GET["id_akomodasi"]) && $_GET["id_akomodasi"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $id_akomodasi = $_GET["id_akomodasi"];
    
    $mysqli = connect_db();
    $query = "
        SELECT da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, 
            p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, 
            da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, 
            a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif
        FROM akomodasi a, desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n
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

    $stmt->bind_result($desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, $negara_id, 
        $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $cover, $description, 
        $logo, $name, $location, $active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data akomodasi tidak ada"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $facilities = [];
    
    $query = "
        SELECT f.id, f.nama, f.icon, f.status_aktif 
        FROM fasilitas f, detail_fasilitas df
        WHERE df.id_fasilitas = f.id
        AND df.id_akomodasi = ?
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
    
    $stmt->bind_result($facility_id, $facility_name, $facility_icon, $facility_active_status);
    
    while ($stmt->fetch()) {
        array_push($facilities, [
            "id" => $facility_id,
            "name" => $facility_name,
            "icon" => $facility_icon,
            "active_status" => boolval($facility_active_status)
        ]);
    }
    
    $stmt->close();

    $response = [
        "status_code" => 200,
        "data" => [
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
            "facilities" => $facilities,
            "cover" => $cover,
            "description" => $description,
            "logo" => $logo,
            "name" => $name,
            "location" => $location,
            "active_status" => boolval($active_status)
        ],
        "message" => "Data profil akomodasi berhasil diperoleh"
    ];

    echo json_encode($response);