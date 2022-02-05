<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $payload = decode_jwt_token(["superadmin", "admin_akomodasi"]);
    $id = $payload["id"];
    $role = $payload["role"];
    
    $mysqli = connect_db();
    
    if ($role == "admin_akomodasi") {
        $query = "SELECT aa.status_super_admin FROM admin_akomodasi aa WHERE aa.id = ?";
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
        
        if ($super_admin_status == 0) {
            $response = [
                "status_code" => 400,
                "data" => null,
                "message" => "Halaman tidak ditemukan"
            ];
            echo json_encode($response);
            exit();
        }
    }
    
    if (!(isset($_POST["id_pegawai"]) && $_POST["id_pegawai"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["username"]) && $_POST["username"] &&
        isset($_POST["password"]) && $_POST["password"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!filter_var(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL)) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Email tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    $id_pegawai = $_POST["id_pegawai"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $username = $_POST["username"];
    $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $super_admin_status = $role == "superadmin" ? 1 : 0;
    
    $query = "
        SELECT a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
            p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
            kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
            a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif,
            pa.nama, pa.avatar, pa.no_telp, pa.tanggal_lahir, pa.nik, 
            pa.jenis_kelamin, pa.status_aktif
        FROM pegawai_akomodasi pa, akomodasi a, desa_adat da, kecamatan kec, 
            kabupaten kab, provinsi p, negara n
        WHERE pa.id_akomodasi = a.id
        AND a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND pa.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_pegawai);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($akomodasi_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
        $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
        $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
        $akomodasi_cover, $akomodasi_description, $akomodasi_logo, $akomodasi_name, 
        $akomodasi_location, $akomodasi_active_status, $name, $avatar, $phone, 
        $date_of_birth, $nik, $gender, $active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$name) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Data pegawai tidak ada"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT COUNT(*) FROM admin_akomodasi aa WHERE aa.username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    
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
            "message" => "Nama pengguna sudah terdaftar"
        ];
        echo json_encode($response);
        exit();
    }
    
    $query = "INSERT INTO admin_akomodasi VALUES (NULL, ?, ?, ?, ?, 1, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $id_pegawai, $email, $username, $hash, $super_admin_status);
    
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
    $user_id = $mysqli->insert_id;
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $user_id,
            "pegawai" => [
                "id" => $id_pegawai,
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
                "name" => $name,
                "avatar" => $avatar,
                "phone" => $phone,
                "date_of_birth" => $date_of_birth,
                "nik" => $nik,
                "gender" => $gender,
                "active_status" => boolval($active_status)
            ],
            "email" => $email,
            "active_status" => true,
            "super_admin_status" => boolval($super_admin_status)
        ],
        "message" => "Proses registrasi berhasil dilakukan"
    ];
    
    echo json_encode($response);