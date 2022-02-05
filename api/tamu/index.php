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
    
    $identity_types = ["Identity Card", "Driving License", "Passport"];
    $data = [];
    $params = [];
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
        SELECT t.id, a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, 
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
    ";

    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= " AND t.status_aktif = ?";
        array_push($params, $active_status_param);
    }
    
    if (isset($_GET["id_akomodasi"])) {
        $query .= " AND t.id_akomodasi = ?";
        array_push($params, $_GET["id_akomodasi"]);
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

    $stmt->store_result();
    $stmt->bind_result($id, $akomodasi_id, $desa_adat_id, $kecamatan_id, 
        $kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag,
        $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, $akomodasi_description, 
        $akomodasi_logo, $akomodasi_name, $akomodasi_location, $akomodasi_active_status, 
        $negara_tamu_id, $negara_tamu_name, $negara_tamu_active_status,
        $name, $email, $avatar, $phone, $date_of_birth, $identity_type, $identity_number, 
        $gender, $active_status, $valid_status);

    while($stmt->fetch()) {
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
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("ss", $id, $id);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->bind_result($block_counter);
        $stmt2->fetch();
        $stmt2->close();
        
        if (isset($_GET["block_status"])) {
            $block_status = json_decode($_GET["block_status"]);
            
            if (($block_status && $block_counter < $max_invalid_report) || 
                (!$block_status && $block_counter >= $max_invalid_report)) {
                continue;
            }
        }
        
        $check_in_id = null;
        $check_in_start = null;
        $check_in_end = null;
        
        $query = "
            SELECT cit.id, cit.waktu_mulai, cit.waktu_selesai
            FROM check_in_tamu cit
            WHERE cit.id_tamu = ?
            ORDER BY cit.id DESC
            LIMIT 1
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("s", $id);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->bind_result($check_in_id, $check_in_start, $check_in_end);
        $stmt2->fetch();
        $stmt2->close();
        
        array_push($data, [
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
                "active_status" => boolval($negara_tamu_active_status)
            ],
            "check_in" => [
                "id" => $check_in_id,
                "start_time" => $check_in_start,
                "end_time" => $check_in_end
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
        ]);
    }

    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data tamu berhasil diperoleh"
    ];

    echo json_encode($response);