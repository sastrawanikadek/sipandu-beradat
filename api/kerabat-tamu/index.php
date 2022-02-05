<?php
    require_once("../config.php");
    require_once("../helpers/jwt.php");
    
    $payload = decode_jwt_token(["tamu"]);
    $user_id = $payload["id"];
    
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
        SELECT COUNT(*)
        FROM tamu t
        WHERE t.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $user_id);
    
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
    
    if ($total == 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data tamu tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $families = [];
    $query = "
        SELECT kt.id, t.id, a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif, nt.id, nt.nama, nt.bendera, nt.status_aktif, t.nama, t.username, t.avatar, t.no_telp, t.tanggal_lahir, t.jenis_identitas, t.no_identitas, t.jenis_kelamin, t.status_aktif, IF(kt.id_tamu=?, 1, 0)
        FROM kerabat_tamu kt, tamu t, akomodasi a, desa_adat da, kecamatan kec, 
            kabupaten kab, provinsi p, negara n, negara nt
        WHERE ((? = '1' AND kt.id_tamu = ? AND kt.id_kerabat = t.id) OR (kt.id_kerabat = ? AND kt.id_tamu = t.id))
        AND t.id_akomodasi = a.id
        AND t.id_negara = nt.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND kt.status = ?
    ";
    
    $family_status = 1;
    
    if (isset($_GET["family_status"])) {
        $family_status = intval($_GET["family_status"]);
    }
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $user_id, $family_status, $user_id, $user_id, $family_status);
    
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
    $stmt->bind_result($id, $kerabat_id, $kerabat_akomodasi_id, $kerabat_desa_adat_id, $kerabat_kecamatan_id, $kerabat_kabupaten_id, $kerabat_provinsi_id, $kerabat_negara_id, $kerabat_negara_name, $kerabat_negara_flag, $kerabat_negara_active_status, $kerabat_provinsi_name, $kerabat_provinsi_active_status, $kerabat_kabupaten_name, $kerabat_kabupaten_active_status, $kerabat_kecamatan_name, $kerabat_kecamatan_active_status, $kerabat_desa_adat_name, $kerabat_desa_adat_latitude, $kerabat_desa_adat_longitude, $kerabat_desa_adat_active_status, $kerabat_akomodasi_cover, $kerabat_akomodasi_description, $kerabat_akomodasi_logo, $kerabat_akomodasi_name, $kerabat_akomodasi_location, $kerabat_akomodasi_active_status, $kerabat_negara_tamu_id, $kerabat_negara_tamu_name, $kerabat_negara_tamu_flag, $kerabat_negara_tamu_active_status, $kerabat_name, $kerabat_username, $kerabat_avatar, $kerabat_phone, $kerabat_date_of_birth, $kerabat_identity_type, $kerabat_identity_number, $kerabat_gender, $kerabat_active_status, $initiator_status);
    
    while ($stmt->fetch()) {
        $query = "
            SELECT
                (SELECT COUNT(*) 
                FROM laporan_darurat_tidak_valid_tamu ldtvt, pelaporan_darurat_tamu pldrt
                WHERE ldtvt.id_pelaporan_darurat_tamu = pldrt.id
                AND pldrt.id_tamu = ?) + 
                (SELECT COUNT(*)
                FROM laporan_tidak_valid_tamu ltvt, pelaporan_tamu plt
                WHERE ltvt.id_pelaporan_tamu = plt.id
                AND plt.id_tamu = ?)
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("ss", $kerabat_id, $kerabat_id);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->bind_result($kerabat_block_counter);
        $stmt2->fetch();
        $stmt2->close();
        
        $pelaporans = [];
        $query = "
            SELECT plt.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, 
                n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif,
                kec.nama, kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif,
                jp.id, jp.nama, jp.icon, jp.status_darurat, jp.status_aktif, plt.judul, plt.foto, 
                plt.keterangan, plt.waktu, plt.latitude, plt.longitude
            FROM pelaporan_tamu plt, desa_adat da, kecamatan kec, kabupaten kab, 
                provinsi p, negara n, jenis_pelaporan jp
            WHERE plt.id_jenis_pelaporan = jp.id
            AND plt.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND plt.id_tamu = ?
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("s", $kerabat_id);
        
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
        $stmt2->bind_result($report_id, $report_desa_adat_id, $report_kecamatan_id, $report_kabupaten_id, 
            $report_provinsi_id, $report_negara_id, $report_negara_name, $report_negara_flag, $report_negara_active_status, 
            $report_provinsi_name, $report_provinsi_active_status, $report_kabupaten_name, 
            $report_kabupaten_active_status, $report_kecamatan_name, $report_kecamatan_active_status, 
            $report_desa_adat_name, $report_desa_adat_latitude, $report_desa_adat_longitude, 
            $report_desa_adat_active_status, $report_jenis_pelaporan_id, $report_jenis_pelaporan_name, 
            $report_jenis_pelaporan_icon, $report_jenis_pelaporan_emergency_status, 
            $report_jenis_pelaporan_active_status, $report_title, $report_photo, $report_description, $report_time, 
            $report_latitude, $report_longitude);
        
        while ($stmt2->fetch()) {
            $status = null;
            
            $query = "
                SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
                FROM pecalang_pelaporan_tamu pdaplt 
                WHERE pdaplt.id_pelaporan_tamu = ? 
                GROUP BY pdaplt.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt3 = $mysqli->prepare($query);
            $stmt3->bind_param("s", $report_id);
            
            if (!$stmt3->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt3->bind_result($status, $total);
            $stmt3->fetch();
            $stmt3->close();
            
            if (!$status) {
                $report_status = 0;
            } else {
                $report_status = intval($status) == -1 ? -1 : 0;
                
                if (intval($status) == 1) {
                    $status = null;
                    
                    $query = "
                        SELECT ptplt.status, COUNT(ptplt.status) AS total 
                        FROM petugas_pelaporan_tamu ptplt 
                        WHERE ptplt.id_pelaporan_tamu = ? 
                        GROUP BY ptplt.status 
                        ORDER BY total DESC
                        LIMIT 1
                    ";
                    $stmt3 = $mysqli->prepare($query);
                    $stmt3->bind_param("s", $report_id);
                    
                    if (!$stmt3->execute()) {
                        $response = [
                            "status_code" => 500,
                            "data" => null,
                            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                        ];
                        echo json_encode($response);
                        exit();
                    }
                    
                    $stmt3->bind_result($status, $total);
                    $stmt3->fetch();
                    $stmt3->close();
                    
                    if (!$status) {
                        $report_status = 1;
                    } else {
                        $report_status = intval($status) == 1 ? 2 : 1;
                    }
                }
            }
            
            array_push($pelaporans, [
                "id" => $report_id,
                "tamu" => [
                    "id" => $kerabat_id,
                    "akomodasi" => [
                        "id" => $kerabat_akomodasi_id,
                        "desa_adat" => [
                            "id" => $kerabat_desa_adat_id,
                            "kecamatan" => [
                                "id" => $kerabat_kecamatan_id,
                                "kabupaten" => [
                                    "id" => $kerabat_kabupaten_id,
                                    "provinsi" => [
                                        "id" => $kerabat_provinsi_id,
                                        "negara" => [
                                            "id" => $kerabat_negara_id,
                                            "name" => $kerabat_negara_name,
                                            "flag" => $kerabat_negara_flag,
                                            "active_status" => boolval($kerabat_negara_active_status)
                                        ],
                                        "name" => $kerabat_provinsi_name,
                                        "active_status" => boolval($kerabat_provinsi_active_status)
                                    ],
                                    "name" => $kerabat_kabupaten_name,
                                    "active_status" => boolval($kerabat_kabupaten_active_status)
                                ],
                                "name" => $kerabat_kecamatan_name,
                                "active_status" => boolval($kerabat_kecamatan_active_status)
                            ],
                            "name" => $kerabat_desa_adat_name,
                            "latitude" => $kerabat_desa_adat_latitude,
                            "longitude" => $kerabat_desa_adat_longitude,
                            "active_status" => boolval($kerabat_desa_adat_active_status)
                        ],
                        "cover" => $kerabat_akomodasi_cover,
                        "description" => $kerabat_akomodasi_description,
                        "logo" => $kerabat_akomodasi_logo,
                        "name" => $kerabat_akomodasi_name,
                        "location" => $kerabat_akomodasi_location,
                        "active_status" => boolval($kerabat_akomodasi_active_status)
                    ],
                    "negara" => [
                        "id" => $kerabat_negara_tamu_id,
                        "name" => $kerabat_negara_tamu_name,
                        "flag" => $kerabat_negara_tamu_flag,
                        "active_status" => boolval($kerabat_negara_tamu_active_status)
                    ],
                    "name" => $kerabat_name,
                    "username" => $kerabat_username,
                    "avatar" => $kerabat_avatar,
                    "phone" => $kerabat_phone,
                    "date_of_birth" => $kerabat_date_of_birth,
                    "identity_type" => $identity_types[intval($kerabat_identity_type)],
                    "identity_number" => $kerabat_identity_number,
                    "gender" => $kerabat_gender,
                    "block_status" => $kerabat_block_counter < $max_invalid_report ? false : true,
                    "active_status" => boolval($kerabat_active_status)
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
                    "id" => $report_jenis_pelaporan_id,
                    "name" => $report_jenis_pelaporan_name,
                    "icon" => $report_jenis_pelaporan_icon,
                    "emergency_status" => boolval($report_jenis_pelaporan_emergency_status),
                    "active_status" => boolval($report_jenis_pelaporan_active_status)
                ],
                "title" => $report_title,
                "photo" => $report_photo,
                "description" => $report_description,
                "time" => $report_time,
                "latitude" => $report_latitude,
                "longitude" => $report_longitude,
                "status" => $report_status
            ]);
        }
        
        $stmt2->close();
        
        $query = "
            SELECT pldrt.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, 
                n.status_aktif, p.nama, p.status_aktif, kab.nama, kab.status_aktif,
                kec.nama, kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif,
                jp.id, jp.nama, jp.icon, jp.status_darurat, jp.status_aktif, pldrt.waktu, 
                pldrt.latitude, pldrt.longitude
            FROM pelaporan_darurat_tamu pldrt, desa_adat da, kecamatan kec, kabupaten kab, 
                provinsi p, negara n, jenis_pelaporan jp
            WHERE pldrt.id_jenis_pelaporan = jp.id
            AND pldrt.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND pldrt.id_tamu = ?
        ";
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("s", $kerabat_id);
        
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
        $stmt2->bind_result($emergency_report_id, $emergency_report_desa_adat_id, 
            $emergency_report_kecamatan_id, $emergency_report_kabupaten_id, $emergency_report_provinsi_id, 
            $emergency_report_negara_id, $emergency_report_negara_name, $emergency_report_negara_flag, $emergency_report_negara_active_status, 
            $emergency_report_provinsi_name, $emergency_report_provinsi_active_status, 
            $emergency_report_kabupaten_name, $emergency_report_kabupaten_active_status, 
            $emergency_report_kecamatan_name, $emergency_report_kecamatan_active_status, 
            $emergency_report_desa_adat_name, $emergency_report_desa_adat_latitude, 
            $emergency_report_desa_adat_longitude, $emergency_report_desa_adat_active_status, 
            $emergency_report_jenis_pelaporan_id, $emergency_report_jenis_pelaporan_name, 
            $emergency_report_jenis_pelaporan_icon, $emergency_report_jenis_pelaporan_emergency_status, 
            $emergency_report_jenis_pelaporan_active_status, $emergency_report_time, $emergency_report_latitude, 
            $emergency_report_longitude);
            
        while ($stmt2->fetch()) {
            $emergency_status = null;
            
            $query = "
                SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
                FROM pecalang_pelaporan_darurat_tamu pdapldrt 
                WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
                GROUP BY pdapldrt.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt3 = $mysqli->prepare($query);
            $stmt3->bind_param("s", $emergency_report_id);
            
            if (!$stmt3->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt3->bind_result($emergency_status, $total);
            $stmt3->fetch();
            $stmt3->close();
            
            if (!$emergency_status) {
                $emergency_report_status = 0;
            } else {
                $emergency_report_status = intval($emergency_status) == -1 ? -1 : 0;
                
                if (intval($emergency_status) == 1) {
                    $emergency_status = null;
                    
                    $query = "
                        SELECT ptpldrt.status, COUNT(ptpldrt.status) AS total 
                        FROM petugas_pelaporan_darurat_tamu ptpldrt 
                        WHERE ptpldrt.id_pelaporan_darurat_tamu = ? 
                        GROUP BY ptpldrt.status 
                        ORDER BY total DESC
                        LIMIT 1
                    ";
                    $stmt3 = $mysqli->prepare($query);
                    $stmt3->bind_param("s", $emergency_report_id);
                    
                    if (!$stmt3->execute()) {
                        $response = [
                            "status_code" => 500,
                            "data" => null,
                            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                        ];
                        echo json_encode($response);
                        exit();
                    }
                    
                    $stmt3->bind_result($emergency_status, $total);
                    $stmt3->fetch();
                    $stmt3->close();
                    
                    if (!$emergency_status) {
                        $emergency_report_status = 1;
                    } else {
                        $emergency_report_status = intval($emergency_status) == 1 ? 2 : 1;
                    }
                }
            }
            
            array_push($pelaporans, [
                "id" => $emergency_report_id,
                "tamu" => [
                    "id" => $kerabat_id,
                    "akomodasi" => [
                        "id" => $kerabat_akomodasi_id,
                        "desa_adat" => [
                            "id" => $kerabat_desa_adat_id,
                            "kecamatan" => [
                                "id" => $kerabat_kecamatan_id,
                                "kabupaten" => [
                                    "id" => $kerabat_kabupaten_id,
                                    "provinsi" => [
                                        "id" => $kerabat_provinsi_id,
                                        "negara" => [
                                            "id" => $kerabat_negara_id,
                                            "name" => $kerabat_negara_name,
                                            "flag" => $kerabat_negara_flag,
                                            "active_status" => boolval($kerabat_negara_active_status)
                                        ],
                                        "name" => $kerabat_provinsi_name,
                                        "active_status" => boolval($kerabat_provinsi_active_status)
                                    ],
                                    "name" => $kerabat_kabupaten_name,
                                    "active_status" => boolval($kerabat_kabupaten_active_status)
                                ],
                                "name" => $kerabat_kecamatan_name,
                                "active_status" => boolval($kerabat_kecamatan_active_status)
                            ],
                            "name" => $kerabat_desa_adat_name,
                            "latitude" => $kerabat_desa_adat_latitude,
                            "longitude" => $kerabat_desa_adat_longitude,
                            "active_status" => boolval($kerabat_desa_adat_active_status)
                        ],
                        "cover" => $kerabat_akomodasi_cover,
                        "description" => $kerabat_akomodasi_description,
                        "logo" => $kerabat_akomodasi_logo,
                        "name" => $kerabat_akomodasi_name,
                        "location" => $kerabat_akomodasi_location,
                        "active_status" => boolval($kerabat_akomodasi_active_status)
                    ],
                    "negara" => [
                        "id" => $kerabat_negara_tamu_id,
                        "name" => $kerabat_negara_tamu_name,
                        "flag" => $kerabat_negara_tamu_flag,
                        "active_status" => boolval($kerabat_negara_tamu_active_status)
                    ],
                    "name" => $kerabat_name,
                    "username" => $kerabat_username,
                    "avatar" => $kerabat_avatar,
                    "phone" => $kerabat_phone,
                    "date_of_birth" => $kerabat_date_of_birth,
                    "identity_type" => $identity_types[intval($kerabat_identity_type)],
                    "identity_number" => $kerabat_identity_number,
                    "gender" => $kerabat_gender,
                    "block_status" => $kerabat_block_counter < $max_invalid_report ? false : true,
                    "active_status" => boolval($kerabat_active_status)
                ],
                "desa_adat" => [
                    "id" => $emergency_report_desa_adat_id,
                    "kecamatan" => [
                        "id" => $emergency_report_kecamatan_id,
                        "kabupaten" => [
                            "id" => $emergency_report_kabupaten_id,
                            "provinsi" => [
                                "id" => $emergency_report_provinsi_id,
                                "negara" => [
                                    "id" => $emergency_report_negara_id,
                                    "name" => $emergency_report_negara_name,
                                    "flag" => $emergency_report_negara_flag,
                                    "active_status" => boolval($emergency_report_negara_active_status)
                                ],
                                "name" => $emergency_report_provinsi_name,
                                "active_status" => boolval($emergency_report_provinsi_active_status)
                            ],
                            "name" => $emergency_report_kabupaten_name,
                            "active_status" => boolval($emergency_report_kabupaten_active_status)
                        ],
                        "name" => $emergency_report_kecamatan_name,
                        "active_status" => boolval($emergency_report_kecamatan_active_status)
                    ],
                    "name" => $emergency_report_desa_adat_name,
                    "latitude" => $emergency_report_desa_adat_latitude,
                    "longitude" => $emergency_report_desa_adat_longitude,
                    "active_status" => boolval($emergency_report_desa_adat_active_status)
                ],
                "jenis_pelaporan" => [
                    "id" => $emergency_report_jenis_pelaporan_id,
                    "name" => $emergency_report_jenis_pelaporan_name,
                    "icon" => $emergency_report_jenis_pelaporan_icon,
                    "emergency_status" => boolval($emergency_report_jenis_pelaporan_emergency_status),
                    "active_status" => boolval($emergency_report_jenis_pelaporan_active_status)
                ],
                "time" => $emergency_report_time,
                "latitude" => $emergency_report_latitude,
                "longitude" => $emergency_report_longitude,
                "status" => $emergency_report_status
            ]);
        }
        
        $stmt2->close();
        
        array_push($families, [
            "id" => $id,
            "tamu" => [
                "id" => $kerabat_id,
                "akomodasi" => [
                    "id" => $kerabat_akomodasi_id,
                    "desa_adat" => [
                        "id" => $kerabat_desa_adat_id,
                        "kecamatan" => [
                            "id" => $kerabat_kecamatan_id,
                            "kabupaten" => [
                                "id" => $kerabat_kabupaten_id,
                                "provinsi" => [
                                    "id" => $kerabat_provinsi_id,
                                    "negara" => [
                                        "id" => $kerabat_negara_id,
                                        "name" => $kerabat_negara_name,
                                        "flag" => $kerabat_negara_flag,
                                        "active_status" => boolval($kerabat_negara_active_status)
                                    ],
                                    "name" => $kerabat_provinsi_name,
                                    "active_status" => boolval($kerabat_provinsi_active_status)
                                ],
                                "name" => $kerabat_kabupaten_name,
                                "active_status" => boolval($kerabat_kabupaten_active_status)
                            ],
                            "name" => $kerabat_kecamatan_name,
                            "active_status" => boolval($kerabat_kecamatan_active_status)
                        ],
                        "name" => $kerabat_desa_adat_name,
                        "latitude" => $kerabat_desa_adat_latitude,
                        "longitude" => $kerabat_desa_adat_longitude,
                        "active_status" => boolval($kerabat_desa_adat_active_status)
                    ],
                    "cover" => $kerabat_akomodasi_cover,
                    "description" => $kerabat_akomodasi_description,
                    "logo" => $kerabat_akomodasi_logo,
                    "name" => $kerabat_akomodasi_name,
                    "location" => $kerabat_akomodasi_location,
                    "active_status" => boolval($kerabat_akomodasi_active_status)
                ],
                "negara" => [
                    "id" => $kerabat_negara_tamu_id,
                    "name" => $kerabat_negara_tamu_name,
                    "flag" => $kerabat_negara_tamu_flag,
                    "active_status" => boolval($kerabat_negara_tamu_active_status)
                ],
                "name" => $kerabat_name,
                "username" => $kerabat_username,
                "avatar" => $kerabat_avatar,
                "phone" => $kerabat_phone,
                "date_of_birth" => $kerabat_date_of_birth,
                "identity_type" => $identity_types[intval($kerabat_identity_type)],
                "identity_number" => $kerabat_identity_number,
                "gender" => $kerabat_gender,
                "block_status" => $kerabat_block_counter < $max_invalid_report ? false : true,
                "active_status" => boolval($kerabat_active_status)
            ],
            "pelaporan" => $pelaporans,
            "status" => $family_status,
            "initiator_status" => boolval($initiator_status)
        ]);
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $families,
        "message" => "Data kerabat tamu berhasil diperoleh"
    ];
    
    echo json_encode($response);