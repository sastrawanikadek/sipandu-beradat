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
    
    $params = [];
    $data = [];
    $mysqli = connect_db();
    
    $query = "
        SELECT aa.id, pa.id, a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif,
            pa.nama, pa.avatar, pa.no_telp, pa.tanggal_lahir, pa.nik, 
            pa.jenis_kelamin, pa.status_aktif, aa.email, aa.status_aktif, aa.status_super_admin
        FROM admin_akomodasi aa, pegawai_akomodasi pa, akomodasi a, desa_adat da, 
            kecamatan kec, kabupaten kab, provinsi p, negara n
        WHERE aa.id_pegawai = pa.id
        AND pa.id_akomodasi = a.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
    ";
    
    if (isset($_GET["active_status"])) {
        $active_status_param = json_decode($_GET["active_status"]);
        $query .= " AND aa.status_aktif = ?";
        array_push($params, $active_status_param);
    }
    
    if (isset($_GET["id_akomodasi"])) {
        $query .= " AND pa.id_akomodasi = ?";
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
    
    $stmt->bind_result($id, $pegawai_id, $akomodasi_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $akomodasi_cover, $akomodasi_description, $akomodasi_logo, $akomodasi_name, 
        $akomodasi_location, $akomodasi_active_status, $pegawai_name, $pegawai_avatar, $pegawai_phone, 
        $pegawai_date_of_birth, $pegawai_nik, $pegawai_gender, $pegawai_active_status, $email,
        $active_status, $super_admin_status);
    
    while ($stmt->fetch()) {
        array_push($data, [
            "id" => $id,
            "pegawai" => [
                "id" => $pegawai_id,
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
                "name" => $pegawai_name,
                "avatar" => $pegawai_avatar,
                "phone" => $pegawai_phone,
                "date_of_birth" => $pegawai_date_of_birth,
                "nik" => $pegawai_nik,
                "gender" => $pegawai_gender,
                "active_status" => boolval($pegawai_active_status)
            ],
            "email" => $email,
            "active_status" => boolval($active_status),
            "super_admin_status" => boolval($super_admin_status)
        ]);
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $data,
        "message" => "Data admin akomodasi berhasil diperoleh"
    ];
    
    echo json_encode($response);