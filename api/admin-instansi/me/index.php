<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["admin_petugas"]);
    $id = $payload["id"];
    
    $mysqli = connect_db();
    
    $query = "
        SELECT pt.id, ip.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif,  jip.id, jip.nama, jip.status_aktif, ip.nama, 
            ip.status_pelaporan, ip.status_aktif,
            pt.nama, pt.email, pt.username, pt.avatar, pt.no_telp, pt.tanggal_lahir, pt.nik, 
            pt.jenis_kelamin, pt.status_aktif, ap.status_aktif, 
            ap.status_super_admin
        FROM admin_petugas ap, petugas pt, instansi_petugas ip, jenis_instansi_petugas jip,
            kecamatan kec, kabupaten kab, provinsi p, negara n
        WHERE ap.id_petugas = pt.id
        AND pt.id_instansi = ip.id
        AND ip.id_jenis_instansi = jip.id
        AND ip.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND ap.id = ?
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
    
    $stmt->bind_result($petugas_id, $instansi_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $jenis_instansi_id, $jenis_instansi_name, $jenis_instansi_active_status, 
        $instansi_name, $instansi_report_status, $instansi_active_status,
        $petugas_name, $petugas_email, $petugas_username, $petugas_avatar, $petugas_phone, 
        $petugas_date_of_birth, $petugas_nik, $petugas_gender, $petugas_active_status, 
        $active_status, $super_admin_status);
    $stmt->fetch();
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "petugas" => [
                "id" => $petugas_id,
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
                "name" => $petugas_name,
                "email" => $petugas_email,
                "username" => $petugas_username,
                "avatar" => $petugas_avatar,
                "phone" => $petugas_phone,
                "date_of_birth" => $petugas_date_of_birth,
                "nik" => $petugas_nik,
                "gender" => $petugas_gender,
                "active_status" => boolval($petugas_active_status)
            ],
            "active_status" => boolval($active_status),
            "super_admin_status" => boolval($super_admin_status)
        ],
        "message" => "Data admin instansi berhasil diperoleh"
    ];
    
    echo json_encode($response);