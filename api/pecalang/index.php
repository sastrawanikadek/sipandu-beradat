<?php
    require_once("../config.php");
    require_once("../helpers/jwt.php");
    
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
        SELECT pda.id, m.id, b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, 
            m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, 
            m.nik, m.jenis_kelamin, m.kategori, m.status_valid, 
            pda.otoritas_sirine, pda.status_prajuru, pda.status_aktif
        FROM pecalang pda, masyarakat m, banjar b, desa_adat da, kecamatan kec, 
            kabupaten kab, provinsi p, negara n, status_aktif_masyarakat sam
        WHERE pda.id_masyarakat = m.id
        AND m.id_status_aktif = sam.id
        AND m.id_banjar = b.id
        AND b.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
    ";
    
    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= " AND pda.status_aktif = ?";
        array_push($params, $active_status_param);
    }
    
    if (isset($_GET["id_desa"])) {
        $query .= " AND b.id_desa = ?";
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
    
    $stmt->store_result();
    $stmt->bind_result($id, $masyarakat_id, $banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, 
        $active_status_active_status, $name, $email, $avatar, $phone, $date_of_birth, $nik, $gender, $category, $valid_status, 
        $sirine_authority, $prajuru_status, $pecalang_active_status);
    
    while($stmt->fetch()) {
        $category_text = $category == 0 ? "Krama Wid" : "Krama Tamiu";
    
        if ($category == 2) {
            $category_text = "Tamiu";
        }
        
        $query = "
            SELECT
                (SELECT COUNT(*) 
                FROM laporan_darurat_tidak_valid ldtv, pelaporan_darurat pd
                WHERE ldtv.id_pelaporan_darurat = pd.id
                AND pd.id_masyarakat = ?) + 
                (SELECT COUNT(*)
                FROM laporan_tidak_valid ltv, pelaporan plr
                WHERE ltv.id_pelaporan = plr.id
                AND plr.id_masyarakat = ?)
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("ss", $masyarakat_id, $masyarakat_id);
        
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
        
        $cur_date = date("Y-m-d");
        $query = "
            SELECT COUNT(*) 
            FROM jadwal_pecalang jpcl 
            WHERE jpcl.id_pecalang = ? 
            AND jpcl.tanggal_mulai <= ?
            AND jpcl.tanggal_selesai >= ?
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("sss", $id, $cur_date, $cur_date);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->bind_result($schedule);
        $stmt2->fetch();
        $stmt2->close();
        
        array_push($data, [
            "id" => $id,
            "masyarakat" => [
                "id" => $masyarakat_id,
                "banjar" => [
                    "id" => $banjar_id,
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
                    "name" => $banjar_name,
                    "active_status" => boolval($banjar_active_status)
                ],
                "active_status" => [
                    "id" => $active_status_id,
                    "name" => $active_status_name,
                    "status" => boolval($active_status_status),
                    "active_status" => boolval($active_status_active_status)
                ],
                "name" => $name,
                "email" => $email,
                "avatar" => $avatar,
                "phone" => $phone,
                "date_of_birth" => $date_of_birth,
                "nik" => $nik,
                "gender" => $gender,
                "category" => $category_text,
                "block_status" => $block_counter < $max_invalid_report ? false : true,
                "valid_status" => boolval($valid_status)
            ],
            "sirine_authority" => boolval($sirine_authority),
            "prajuru_status" => boolval($prajuru_status),
            "working_status" => $schedule > 0 ? true : false,
            "active_status" => boolval($pecalang_active_status),
        ]);
    }
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data pecalang desa adat berhasil diperoleh"
    ];
    
    echo json_encode($response);