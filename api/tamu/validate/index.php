<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["admin_akomodasi"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $identity_types = ["Identity Card", "Driving License", "Passport"];

    $id = $_POST["id"];
    
    $mysqli = connect_db();
    $query = "SELECT p.maks_laporan_tidak_valid FROM pengaturan p";
    $stmt = $mysqli->prepare($query);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }

    $stmt->bind_result($max_invalid_report);
    $stmt->fetch();
    $stmt->close();

    $query = "
        SELECT a.id, n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.id, kec.nama, kec.status_aktif,
            da.id, da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, 
            a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif, nt.id, nt.nama, nt.bendera, nt.status_aktif, t.nama, t.email, t.avatar, t.no_telp, t.tanggal_lahir, t.jenis_identitas, t.no_identitas, t.jenis_kelamin, t.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec, desa_adat da, akomodasi a, negara nt, tamu t
        WHERE t.id_negara = nt.id
        AND t.id_akomodasi = a.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND t.id = ?
    ";
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
    
    $stmt->bind_result($akomodasi_id, $negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_id, $provinsi_name, $provinsi_active_status, $kabupaten_id, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_id, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_id, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, 
        $akomodasi_description, $akomodasi_logo, $akomodasi_name, $akomodasi_location, 
        $akomodasi_active_status, $negara_tamu_id, $negara_tamu_name, $negara_tamu_flag, $negara_tamu_active_status, $name, $email, $avatar, $phone, $date_of_birth, $identity_type, $identity_number, $gender, $active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data tamu tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "
        UPDATE tamu t
        SET t.status_valid = 1
        WHERE t.id = ?
    ";
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
            
    $stmt->close();
    
    $query = "
        SELECT
            (SELECT COUNT(*) 
            FROM laporan_darurat_tidak_valid_tamu ldtvt, pelaporan_darurat_tamu pdt
            WHERE ldtvt.id_pelaporan_darurat_tamu = pdt.id
            AND pdt.id_tamu = ?) + 
            (SELECT COUNT(*)
            FROM laporan_tidak_valid_tamu ltvt, pelaporan_tamu plrt
            WHERE ltvt.id_pelaporan_tamu = plrt.id
            AND plrt.id_tamu = ?)
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $id);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($block_counter);
    $stmt->fetch();
    $stmt->close();
    
    $response = [
        "status_code" => 200, 
        "data" => [
            "id" => $id,
            "akomodasi" => [
                "id" => $akomodasi_id,
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
            "negara" => [
                "id" => $negara_tamu_id,
                "name" => $negara_tamu_name,
                "flag" => $negara_tamu_flag,
                "active_status" => boolval($negara_tamu_active_status)
            ],
            "name" => $name,
            "email" => $email,
            "avatar" => $avatar,
            "phone" => $phone,
            "date_of_birth" => $date_of_birth,
            "identity_type" => $identity_types[intval($identity_type)],
            "identity_number" => $identity_number,
            "gender" => $gender,
            "block_status" => $block_counter < $max_invalid_report ? false : true,
            "active_status" => boolval($active_status),
            "valid_status" => true
        ],
        "message" => "Tamu berhasil divalidasi"
    ];

    echo json_encode($response);