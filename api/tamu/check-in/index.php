<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["admin_akomodasi"]);
    
    if (!(isset($_POST["id_tamu"]) && $_POST["id_tamu"] &&
        isset($_POST["start_time"]) && $_POST["start_time"] &&
        isset($_POST["end_time"]) && $_POST["end_time"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $id_tamu = $_POST["id_tamu"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    
    if (new DateTime($start_time) > new DateTime($end_time)) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Waktu mulai harus lebih kecil dari waktu selesai"
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $identity_types = ["Identity Card", "Driving License", "Passport"];
    
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
        SELECT a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, 
            p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, 
            da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, 
            a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif, nt.id, nt.nama, 
            nt.status_aktif, t.nama, t.email, t.avatar, t.no_telp, t.tanggal_lahir, 
            t.jenis_identitas, t.no_identitas, t.jenis_kelamin, t.status_aktif, t.status_valid
        FROM tamu t, akomodasi a, negara nt, desa_adat da, kecamatan kec, 
            kabupaten kab, provinsi p, negara n
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
    $stmt->bind_param("s", $id_tamu);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }

    $stmt->bind_result($akomodasi_id, $desa_adat_id, $kecamatan_id, 
        $kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag,
        $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, $akomodasi_description, 
        $akomodasi_logo, $akomodasi_name, $akomodasi_location, $akomodasi_active_status, 
        $negara_tamu_id, $negara_tamu_name, $negara_tamu_active_status,
        $name, $email, $avatar, $phone, $date_of_birth, $identity_type, $identity_number, 
        $gender, $active_status, $valid_status);
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
    
    $query = "SELECT cit.id FROM check_in_tamu cit WHERE cit.id_tamu = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_tamu);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();
    
    if (!$id) {
        $query = "INSERT INTO check_in_tamu VALUES (NULL, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $id_tamu, $start_time, $end_time);
        
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
        $stmt->close();
    } else {
        $query = "UPDATE check_in_tamu cit SET cit.waktu_mulai = ?, cit.waktu_selesai = ? WHERE cit.id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $start_time, $end_time, $id);
        
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
    }

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
    $stmt->bind_param("ss", $id_tamu, $id_tamu);
    
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
            "tamu" => [
                "id" => $id_tamu,
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
                "valid_status" => boolval($valid_status)
            ],
            "start_time" => $start_time,
            "end_time" => $end_time
        ],
        "message" => "Data check in tamu berhasil diperbaharui"
    ];

    echo json_encode($response);