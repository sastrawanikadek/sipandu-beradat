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
        SELECT pldrt.id, t.id, a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif, 
            nt.id, nt.nama, nt.bendera, nt.status_aktif, t.nama, t.email, t.avatar, t.no_telp, t.tanggal_lahir, 
            t.jenis_identitas, t.no_identitas, t.jenis_kelamin, t.status_aktif, t.status_valid, pldrda.id, pldrkec.id, pldrkab.id, pldrp.id, pldrn.id, pldrn.nama, pldrn.bendera, pldrn.status_aktif, 
            pldrp.nama, pldrp.status_aktif, pldrkab.nama, pldrkab.status_aktif, pldrkec.nama, 
            pldrkec.status_aktif, pldrda.nama, pldrda.latitude, pldrda.longitude, pldrda.status_aktif,
            jp.id, jp.nama, jp.icon, jp.status_darurat, jp.status_aktif, pldrt.waktu, pldrt.latitude, pldrt.longitude
        FROM pelaporan_darurat_tamu pldrt, tamu t, akomodasi a, desa_adat da, kecamatan kec, 
            kabupaten kab, provinsi p, negara n, negara nt, desa_adat pldrda, kecamatan pldrkec, 
            kabupaten pldrkab, provinsi pldrp, negara pldrn, jenis_pelaporan jp
        WHERE pldrt.id_tamu = t.id
        AND t.id_akomodasi = a.id
        AND t.id_negara = nt.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND pldrt.id_desa = pldrda.id
        AND pldrda.id_kecamatan = pldrkec.id
        AND pldrkec.id_kabupaten = pldrkab.id
        AND pldrkab.id_provinsi = pldrp.id
        AND pldrp.id_negara = pldrn.id
        AND pldrt.id_jenis_pelaporan = jp.id
    ";
    
    if (isset($_GET["id_tamu"])) {
        $query .= " AND pldrt.id_tamu = ?";
        array_push($params, $_GET["id_tamu"]);
    }
    
    if (isset($_GET["id_desa"])) {
        $query .= " AND pldrt.id_desa = ?";
        array_push($params, $_GET["id_desa"]);
    }
    
    if (isset($_GET["id_akomodasi"])) {
        $query .= " AND t.id_akomodasi = ?";
        array_push($params, $_GET["id_akomodasi"]);
    }
    
    if (isset($_GET["id_jenis_pelaporan"])) {
        $query .= " AND pldrt.id_jenis_pelaporan = ?";
        array_push($params, $_GET["id_jenis_pelaporan"]);
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
    $stmt->bind_result($id, $tamu_id, $akomodasi_id, $desa_adat_id, $kecamatan_id, 
        $kabupaten_id, $provinsi_id, $negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_name, $provinsi_active_status, $kabupaten_name, $kabupaten_active_status, 
        $kecamatan_name, $kecamatan_active_status, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, 
        $akomodasi_description, $akomodasi_logo, $akomodasi_name, $akomodasi_location, 
        $akomodasi_active_status, $negara_tamu_id, $negara_tamu_name, $negara_tamu_flag,
        $negara_tamu_active_status, $name, $email, $avatar, $phone, $date_of_birth, $identity_type, 
        $identity_number, $gender, $active_status, $valid_status, $report_desa_adat_id, $report_kecamatan_id, 
        $report_kabupaten_id, $report_provinsi_id, $report_negara_id, $report_negara_name, $report_negara_flag,
        $report_negara_active_status, $report_provinsi_name, $report_provinsi_active_status, 
        $report_kabupaten_name, $report_kabupaten_active_status, $report_kecamatan_name, 
        $report_kecamatan_active_status, $report_desa_adat_name, $report_desa_adat_latitude, 
        $report_desa_adat_longitude, $report_desa_adat_active_status, $jenis_pelaporan_id, 
        $jenis_pelaporan_name, $jenis_pelaporan_icon, $jenis_pelaporan_emergency_status, 
        $jenis_pelaporan_active_status, $time, $latitude, $longitude);
    
    
    while ($stmt->fetch()) {
        if (isset($_GET["id_instansi"]) && $_GET["id_instansi"]) {
            $id_instansi = $_GET["id_instansi"];
            
            $query = "
                SELECT ip.status_pelaporan 
                FROM instansi_petugas ip 
                WHERE ip.id = ?
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $id_instansi);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($instansi_report_status);
            $stmt2->fetch();
            $stmt2->close();
            
            if (intval($instansi_report_status) == 0) {
                continue;
            } else if (intval($instansi_report_status) == 1) {
                $query = "
                    SELECT COUNT(*) 
                    FROM otoritas_pelaporan_instansi opi 
                    WHERE opi.id_instansi = ? 
                    AND opi.id_jenis_pelaporan = ?
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("ss", $id_instansi, $jenis_pelaporan_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($total);
                $stmt2->fetch();
                $stmt2->close();
                
                if ($total == 0) {
                    continue;
                }
            }
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
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("ss", $tamu_id, $tamu_id);
        
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
        
        $status = null;
        
        $query = "
            SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
            FROM pecalang_pelaporan_darurat_tamu pdapldrt 
            WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
            GROUP BY pdapldrt.status 
            ORDER BY total DESC
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
        
        $stmt2->bind_result($status, $total);
        $stmt2->fetch();
        $stmt2->close();
        
        if (!$status) {
            $report_status = 0;
        } else {
            $report_status = intval($status) == -1 ? -1 : 0;
            
            if (intval($status) == 1) {
                $status = null;
                
                $query = "
                    SELECT ptpldrt.status, COUNT(ptpldrt.status) AS total 
                    FROM petugas_pelaporan_darurat_tamu ptpldrt 
                    WHERE ptpldrt.id_pelaporan_darurat_tamu = ? 
                    GROUP BY ptpldrt.status 
                    ORDER BY total DESC
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
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();
                
                if (!$status) {
                    $report_status = 1;
                } else {
                    $report_status = intval($status) == 1 ? 2 : 1;
                }
            }
        }
        
        if (isset($_GET["id_instansi"]) && $_GET["id_instansi"] && $report_status < 1) {
            continue;
        }
        
        $pecalang_reports = [];
        $query = "
            SELECT pdapldrt.id, pda.id, m.id, b.id, da.id, kec.id, kab.id, 
                p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, m.nik, m.jenis_kelamin, m.kategori, m.status_valid, pda.otoritas_sirine, pda.status_prajuru, pda.status_aktif, pdapldrt.foto, pdapldrt.status
            FROM pecalang_pelaporan_darurat_tamu pdapldrt, pecalang pda, masyarakat m, banjar b, desa_adat da, kecamatan kec, kabupaten kab, provinsi p, negara n, status_aktif_masyarakat sam
            WHERE pdapldrt.id_pecalang = pda.id
            AND pda.id_masyarakat = m.id
            AND m.id_status_aktif = sam.id
            AND m.id_banjar = b.id
            AND b.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND pdapldrt.id_pelaporan_darurat_tamu = ?
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
        $stmt2->bind_result($pecalang_report_id, $pecalang_id, $pecalang_masyarakat_id, $pecalang_banjar_id, $pecalang_desa_adat_id, $pecalang_kecamatan_id, $pecalang_kabupaten_id, $pecalang_provinsi_id, $pecalang_negara_id, $pecalang_negara_name, $pecalang_negara_flag, $pecalang_negara_active_status, $pecalang_provinsi_name, $pecalang_provinsi_active_status, $pecalang_kabupaten_name, $pecalang_kabupaten_active_status, $pecalang_kecamatan_name, $pecalang_kecamatan_active_status, $pecalang_desa_adat_name, $pecalang_desa_adat_latitude, $pecalang_desa_adat_longitude, $pecalang_desa_adat_active_status, $pecalang_banjar_name, $pecalang_banjar_active_status, $pecalang_active_status_id, $pecalang_active_status_name, $pecalang_active_status_status, $pecalang_active_status_active_status, $pecalang_name, $pecalang_email, $pecalang_avatar, $pecalang_phone, $pecalang_date_of_birth, $pecalang_nik, $pecalang_gender, $pecalang_category, $pecalang_valid_status, $pecalang_sirine_authority, $pecalang_prajuru_status, $pecalang_active_status, $pecalang_report_photo, $pecalang_report_status);
        
        while ($stmt2->fetch()) {
            $pecalang_category_text = $pecalang_category == 0 ? "Krama Wid" : "Krama Tamiu";
    
            if ($pecalang_category == 2) {
                $pecalang_category_text = "Tamiu";
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
            $stmt3 = $mysqli->prepare($query);
            $stmt3->bind_param("ss", $pecalang_masyarakat_id, $pecalang_masyarakat_id);
            
            if (!$stmt3->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt3->bind_result($pecalang_block_counter);
            $stmt3->fetch();
            $stmt3->close();
            
            $cur_date = date("Y-m-d");
            $query = "
                SELECT COUNT(*) 
                FROM jadwal_pecalang jpcl 
                WHERE jpcl.id_pecalang = ? 
                AND jpcl.tanggal_mulai <= ?
                AND jpcl.tanggal_selesai >= ?
            ";
            $stmt3 = $mysqli->prepare($query);
            $stmt3->bind_param("sss", $pecalang_id, $cur_date, $cur_date);
            
            if (!$stmt3->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt3->bind_result($pecalang_schedule);
            $stmt3->fetch();
            $stmt3->close();
            
            array_push($pecalang_reports, [
                "id" => $pecalang_report_id,
                "pecalang" => [
                    "id" => $pecalang_id,
                    "masyarakat" => [
                        "id" => $pecalang_masyarakat_id,
                        "banjar" => [
                            "id" => $pecalang_banjar_id,
                            "desa_adat" => [
                                "id" => $pecalang_desa_adat_id,
                                "kecamatan" => [
                                    "id" => $pecalang_kecamatan_id,
                                    "kabupaten" => [
                                        "id" => $pecalang_kabupaten_id,
                                        "provinsi" => [
                                            "id" => $pecalang_provinsi_id,
                                            "negara" => [
                                                "id" => $pecalang_negara_id,
                                                "name" => $pecalang_negara_name,
                                                "flag" => $pecalang_negara_flag,
                                                "active_status" => boolval($pecalang_negara_active_status)
                                            ],
                                            "name" => $pecalang_provinsi_name,
                                            "active_status" => boolval($pecalang_provinsi_active_status)
                                        ],
                                        "name" => $pecalang_kabupaten_name,
                                        "active_status" => boolval($pecalang_kabupaten_active_status)
                                    ],
                                    "name" => $pecalang_kecamatan_name,
                                    "active_status" => boolval($pecalang_kecamatan_active_status)
                                ],
                                "name" => $pecalang_desa_adat_name,
                                "latitude" => $pecalang_desa_adat_latitude,
                                "longitude" => $pecalang_desa_adat_longitude,
                                "active_status" => boolval($pecalang_desa_adat_active_status)
                            ],
                            "name" => $pecalang_banjar_name,
                            "active_status" => boolval($pecalang_banjar_active_status)
                        ],
                        "active_status" => [
                            "id" => $pecalang_active_status_id,
                            "name" => $pecalang_active_status_name,
                            "status" => boolval($pecalang_active_status_status),
                            "active_status" => boolval($pecalang_active_status_active_status)
                        ],
                        "name" => $pecalang_name,
                        "email" => $pecalang_email,
                        "avatar" => $pecalang_avatar,
                        "phone" => $pecalang_phone,
                        "date_of_birth" => $pecalang_date_of_birth,
                        "nik" => $pecalang_nik,
                        "gender" => $pecalang_gender,
                        "category" => $pecalang_category_text,
                        "block_status" => $pecalang_block_counter < $max_invalid_report ? false : true,
                        "valid_status" => boolval($pecalang_valid_status)
                    ],
                    "sirine_authority" => boolval($pecalang_sirine_authority),
                    "prajuru_status" => boolval($pecalang_prajuru_status),
                    "working_status" => $pecalang_schedule > 0 ? true : false,
                    "active_status" => boolval($pecalang_active_status),
                ],
                "photo" => $pecalang_report_photo,
                "status" => $pecalang_report_status
            ]);
        }
        
        $stmt2->close();
        
        $petugas_reports = [];
        $query = "
            SELECT ptpldrt.id, pt.id, ip.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, jip.id, jip.nama, jip.status_aktif, ip.nama, ip.status_pelaporan, ip.status_aktif, pt.nama, pt.email, pt.nik, pt.avatar, pt.no_telp, pt.tanggal_lahir, pt.jenis_kelamin, pt.status_aktif, ptpldrt.foto, ptpldrt.status
            FROM petugas_pelaporan_darurat_tamu ptpldrt, petugas pt, instansi_petugas ip, jenis_instansi_petugas jip, kecamatan kec, kabupaten kab, provinsi p, negara n
            WHERE ptpldrt.id_petugas = pt.id
            AND pt.id_instansi = ip.id
            AND ip.id_jenis_instansi = jip.id
            AND ip.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND ptpldrt.id_pelaporan_darurat_tamu = ?
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
        
        $stmt2->bind_result($petugas_report_id, $petugas_id, $petugas_instansi_id, $petugas_kecamatan_id, $petugas_kabupaten_id, $petugas_provinsi_id, $petugas_negara_id, $petugas_negara_name, $petugas_negara_flag, $petugas_negara_active_status, $petugas_provinsi_name, $petugas_provinsi_active_status, $petugas_kabupaten_name, $petugas_kabupaten_active_status, $petugas_kecamatan_name, $petugas_kecamatan_active_status, $petugas_jenis_instansi_id, $petugas_jenis_instansi_name, $petugas_jenis_instansi_active_status, $petugas_instansi_name, $petugas_instansi_report_status, $petugas_instansi_active_status, $petugas_name, $petugas_email, $petugas_nik, $petugas_avatar, $petugas_phone, $petugas_date_of_birth, $petugas_gender, $petugas_active_status, $petugas_report_photo, $petugas_report_status);
        
        while ($stmt2->fetch()) {
            array_push($petugas_reports, [
                "id" => $petugas_report_id,
                "petugas" => [
                    "id" => $petugas_id,
                    "instansi_petugas" => [
                        "id" => $petugas_instansi_id,
                        "kecamatan" => [
                            "id" => $petugas_kecamatan_id,
                            "kabupaten" => [
                                "id" => $petugas_kabupaten_id,
                                "provinsi" => [
                                    "id" => $petugas_provinsi_id,
                                    "negara" => [
                                        "id" => $petugas_negara_id,
                                        "name" => $petugas_negara_name,
                                        "flag" => $petugas_negara_flag,
                                        "active_status" => boolval($petugas_negara_active_status)
                                    ],
                                    "name" => $petugas_provinsi_name,
                                    "active_status" => boolval($petugas_provinsi_active_status)
                                ],
                                "name" => $petugas_kabupaten_name,
                                "active_status" => boolval($petugas_kabupaten_active_status)
                            ],
                            "name" => $petugas_kecamatan_name,
                            "active_status" => boolval($petugas_kecamatan_active_status)
                        ],
                        "jenis_instansi" => [
                            "id" => $petugas_jenis_instansi_id,
                            "name" => $petugas_jenis_instansi_name,
                            "active_status" => boolval($petugas_jenis_instansi_active_status)
                        ],
                        "report_status" => $petugas_instansi_report_status,
                        "active_status" => boolval($petugas_instansi_active_status)
                    ],
                    "name" => $petugas_name,
                    "email" => $petugas_email,
                    "avatar" => $petugas_avatar,
                    "phone" => $petugas_phone,
                    "date_of_birth" => $petugas_date_of_birth,
                    "nik" => $petugas_nik,
                    "gender" => $petugas_gender,
                    "active_status" => boolval($petugas_active_status)
                ],
                "photo" => $petugas_report_photo,
                "status" => $petugas_report_status
            ]);
        }
        
        $stmt2->close();
        
        array_push($data, [
            "id" => $id,
            "tamu" => [
                "id" => $tamu_id,
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
                "valid_status" => boolval($valid_status)
            ],
            "desa_adat" => [
                "id" => $report_desa_adat_id,
                "kecamatan" => [
                    "id" => $report_kecamatan_id,
                    "kabupaten" => [
                        "id" => $report_kabupaten_id,
                        "provinsi" => [
                            "id" => $report_provinsi_id,
                            "negara" => [
                                "id" => $report_negara_id,
                                "name" => $report_negara_name,
                                "flag" => $report_negara_flag,
                                "active_status" => boolval($report_negara_active_status)
                            ],
                            "name" => $report_provinsi_name,
                            "active_status" => boolval($report_provinsi_active_status)
                        ],
                        "name" => $report_kabupaten_name,
                        "active_status" => boolval($report_kabupaten_active_status)
                    ],
                    "name" => $report_kecamatan_name,
                    "active_status" => boolval($report_kecamatan_active_status)
                ],
                "name" => $report_desa_adat_name,
                "latitude" => $report_desa_adat_latitude,
                "longitude" => $report_desa_adat_longitude,
                "active_status" => boolval($report_desa_adat_active_status)
            ],
            "jenis_pelaporan" => [
                "id" => $jenis_pelaporan_id,
                "name" => $jenis_pelaporan_name,
                "icon" => $jenis_pelaporan_icon,
                "emergency_status" => boolval($jenis_pelaporan_emergency_status),
                "active_status" => boolval($jenis_pelaporan_active_status)
            ],
            "pecalang_reports" => $pecalang_reports,
            "petugas_reports" => $petugas_reports,
            "time" => $time,
            "latitude" => $latitude,
            "longitude" => $longitude,
            "status" => $report_status
        ]);
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data pelaporan darurat tamu berhasil diperoleh"
    ];
    echo json_encode($response);