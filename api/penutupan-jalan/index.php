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
    
    $now = date("Y-m-d H:i:s");
    $query = "
        SELECT pj.id, pda.id, m.id, b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, 
            m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, 
            m.nik, m.jenis_kelamin, m.kategori, m.status_valid, 
            pda.otoritas_sirine, pda.status_prajuru, pda.status_aktif, pj.judul, pj.foto, pj.waktu_awal, pj.waktu_akhir
        FROM penutupan_jalan pj, pecalang pda, masyarakat m, banjar b, desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n, status_aktif_masyarakat sam
        WHERE pj.id_pecalang = pda.id
        AND pda.id_masyarakat = m.id
        AND m.id_status_aktif = sam.id
        AND m.id_banjar = b.id
        AND b.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND pj.waktu_akhir > ?
    ";
    
    if (isset($_GET["id_desa"])) {
        $query .= " AND da.id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $now, $_GET["id_desa"]);
    } else {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $now);
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
    $stmt->bind_result($id, $pecalang_id, $masyarakat_id, $banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, $active_status_active_status, $masyarakat_name, $masyarakat_email, $masyarakat_avatar, $masyarakat_phone, $masyarakat_date_of_birth, $masyarakat_nik, $masyarakat_gender, $masyarakat_category, $masyarakat_valid_status, $pecalang_sirine_authority, $pecalang_prajuru_status, $pecalang_active_status, $title, $cover, $start_time, $end_time);
    
    while ($stmt->fetch()) {
        $category_text = $masyarakat_category == 0 ? "Krama Wid" : "Krama Tamiu";
    
        if ($masyarakat_category == 2) {
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
        $stmt2->bind_param("sss", $pecalang_id, $cur_date, $cur_date);
        
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
        
        $points = [];
        $query = "
            SELECT tpj.id, tpj.tipe 
            FROM titik_penutupan_jalan tpj 
            WHERE tpj.id_penutupan_jalan = ?
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
        
        $stmt2->store_result();
        $stmt2->bind_result($point_id, $point_type);
        
        while ($stmt2->fetch()) {
            $coordinates = [];
            $query = "
                SELECT dtpj.id, dtpj.latitude, dtpj.longitude
                FROM detail_titik_penutupan_jalan dtpj
                WHERE dtpj.id_titik_penutupan_jalan = ?
            ";
            $stmt3 = $mysqli->prepare($query);
            $stmt3->bind_param("s", $point_id);
            
            if (!$stmt3->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt3->bind_result($detail_id, $latitude, $longitude);
            
            while ($stmt3->fetch()) {
                array_push($coordinates, [
                    "id" => $detail_id, 
                    "latitude" => $latitude, 
                    "longitude" => $longitude
                ]);
            }
            
            $stmt3->close();
            
            array_push($points, [
                "id" => $point_id,
                "coordinates" => $coordinates,
                "type" => intval($point_type)
            ]);
        }
        
        $stmt2->close();
        
        array_push($data, [
           "id" => $id,
           "pecalang" => [
               "id" => $pecalang_id,
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
                    "name" => $masyarakat_name,
                    "email" => $masyarakat_email,
                    "avatar" => $masyarakat_avatar,
                    "phone" => $masyarakat_phone,
                    "date_of_birth" => $masyarakat_date_of_birth,
                    "nik" => $masyarakat_nik,
                    "gender" => $masyarakat_gender,
                    "category" => $category_text,
                    "block_status" => $block_counter < $max_invalid_report ? false : true,
                    "valid_status" => boolval($masyarakat_valid_status)
                ],
                "sirine_authority" => boolval($pecalang_sirine_authority),
                "prajuru_status" => boolval($pecalang_prajuru_status),
                "working_status" => $schedule > 0 ? true : false,
                "active_status" => boolval($pecalang_active_status)
            ],
            "points" => $points,
            "title" => $title,
            "cover" => $cover,
            "start_time" => $start_time,
            "end_time" => $end_time
        ]);
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data informasi penutupan jalan berhasil diperoleh"
    ];
    echo json_encode($response);