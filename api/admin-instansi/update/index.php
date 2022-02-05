<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["admin_petugas"]);
    $user_id = $payload["id"];
    
    $mysqli = connect_db();
    
    $query = "SELECT ap.status_super_admin FROM admin_petugas ap WHERE ap.id = ?";
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
    
    $stmt->bind_result($super_admin_status);
    $stmt->fetch();
    $stmt->close();
    
    if ($super_admin_status == 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_petugas"]) && $_POST["id_petugas"] &&
        isset($_POST["active_status"]))) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    $id_petugas = $_POST["id_petugas"];
    $active_status = intval(json_decode($_POST["active_status"]));
    
    $query = "
        SELECT ip.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, p.nama, 
            p.status_aktif, kab.nama, kab.status_aktif, kec.nama, kec.status_aktif, 
            jip.id, jip.nama, jip.status_aktif, ip.nama, ip.status_pelaporan, 
            ip.status_aktif, pt.nama, pt.email, pt.avatar, pt.no_telp, pt.tanggal_lahir, pt.nik, 
            pt.jenis_kelamin, pt.status_aktif
        FROM petugas pt, instansi_petugas ip, jenis_instansi_petugas jip, kecamatan kec, 
            kabupaten kab, provinsi p, negara n
        WHERE pt.id_instansi = ip.id
        AND ip.id_jenis_instansi = jip.id
        AND ip.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND pt.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_petugas);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($instansi_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $jenis_instansi_id, $jenis_instansi_name, $jenis_instansi_active_status,
        $instansi_name, $instansi_report_status, $instansi_active_status,
        $name, $email, $avatar, $phone, $date_of_birth, $nik, $gender, $petugas_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data petugas tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM admin_petugas ap WHERE ap.id_petugas = ? AND ap.id <> ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_petugas, $id);
    
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
    
    if ($total > 0) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data sudah ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT ap.status_super_admin FROM admin_petugas ap WHERE ap.id = ?";
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
    
    $stmt->bind_result($super_admin_status);
    $stmt->fetch();
    $stmt->close();
    
    $query = "UPDATE admin_petugas ap SET ap.id_petugas = ?, ap.status_aktif = ? WHERE ap.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $id_petugas, $active_status, $id);
    
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
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $id,
            "petugas" => [
                "id" => $id_petugas,
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
                "name" => $name,
                "email" => $email,
                "avatar" => $avatar,
                "phone" => $phone,
                "date_of_birth" => $date_of_birth,
                "nik" => $nik,
                "gender" => $gender,
                "active_status" => boolval($petugas_active_status)
            ],
            "active_status" => boolval($active_status),
            "super_admin_status" => boolval($super_admin_status)
        ],
        "message" => "Data admin instansi berhasil diubah"
    ];
    
    echo json_encode($response);