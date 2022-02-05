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
    $mysqli = connect_db();
    
    $query = "
        SELECT opi.id, ip.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, jip.id, jip.nama, jip.status_aktif, ip.nama, 
            ip.status_pelaporan, ip.status_aktif, jp.id, jp.nama, jp.icon, 
            jp.status_darurat, jp.status_aktif
        FROM otoritas_pelaporan_instansi opi, instansi_petugas ip, kecamatan kec, 
            kabupaten kab, provinsi p, negara n, jenis_instansi_petugas jip, 
            jenis_pelaporan jp
        WHERE opi.id_instansi = ip.id
        AND opi.id_jenis_pelaporan = jp.id
        AND ip.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND ip.id_jenis_instansi = jip.id
    ";
    
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
    
    $stmt->bind_result($id, $instansi_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, 
        $provinsi_active_status, $kabupaten_name, $kabupaten_active_status, 
        $kecamatan_name, $kecamatan_active_status, $jenis_instansi_id, 
        $jenis_instansi_name, $jenis_instansi_active_status, $instansi_name, 
        $instansi_report_status, $instansi_active_status, 
        $jenis_pelaporan_id, $jenis_pelaporan_name, $jenis_pelaporan_icon, 
        $jenis_pelaporan_emergency_status, $jenis_pelaporan_active_status);
    
    while($stmt->fetch()) {
        array_push($data, [
            "id" => $id,
            "instansi_petugas" => [
                "id" => $instansi_id,
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
                "jenis_instansi" => [
                    "id" => $jenis_instansi_id,
                    "name" => $jenis_instansi_name,
                    "active_status" => boolval($jenis_instansi_active_status)
                ],
                "name" => $instansi_name,
                "report_status" => $instansi_report_status,
                "active_status" => boolval($instansi_active_status)
            ],
            "jenis_pelaporan" => [
                "id" => $jenis_pelaporan_id,
                "name" => $jenis_pelaporan_name,
                "icon" => $jenis_pelaporan_icon,
                "emergency_status" => boolval($jenis_pelaporan_emergency_status),
                "active_status" => boolval($jenis_pelaporan_active_status)
            ]
        ]);
    }
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data otoritas pelaporan instansi berhasil diperoleh"
    ];
    echo json_encode($response);