<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");
    
    $payload = decode_jwt_token(["admin_desa", "admin_akomodasi"]);
    $id = $payload["id"];
    $role = $payload["role"];
    
    if (!(isset($_POST["title"]) && $_POST["title"] &&
        isset($_POST["content"]) && $_POST["content"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $title = $_POST["title"];
    $content = $_POST["content"];
    $time = date("Y-m-d H:i:s");
    
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
    
    if ($role == "admin_desa") {
        $query = "
            SELECT m.id, b.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
                p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
                kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
                b.nama, b.status_aktif, sam.id, sam.nama, sam.status, sam.status_aktif, 
                m.nama, m.email, m.avatar, m.no_telp, m.tanggal_lahir, 
                m.nik, m.jenis_kelamin, m.kategori, m.status_valid, ad.status_aktif, ad.status_super_admin
            FROM admin_desa ad, masyarakat m, banjar b, desa_adat da, kecamatan kec, 
                kabupaten kab, provinsi p, negara n, status_aktif_masyarakat sam
            WHERE ad.id_masyarakat = m.id
            AND m.id_status_aktif = sam.id
            AND m.id_banjar = b.id
            AND b.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND ad.id = ?
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
        
        $stmt->bind_result($masyarakat_id, $banjar_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
            $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
            $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
            $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
            $banjar_name, $banjar_active_status, $active_status_id, $active_status_name, $active_status_status, $active_status_active_status, 
            $name, $email, $avatar, $phone, $date_of_birth, $nik, $gender, $category, $valid_status, $admin_active_status, 
            $super_admin_status);
        $stmt->fetch();
        $stmt->close();
        
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
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $masyarakat_id, $masyarakat_id);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($block_counter);
        $stmt->fetch();
        $stmt->close();
    
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
        
        $author = [
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
            "active_status" => boolval($admin_active_status),
            "super_admin_status" => boolval($super_admin_status)
        ];
    } else {
        $query = "
            SELECT pa.id, a.id, da.id, kec.id, kab.id, p.id, n.id, n.nama, n.bendera, n.status_aktif, 
                p.nama, p.status_aktif, kab.nama, kab.status_aktif, kec.nama, 
                kec.status_aktif, da.nama, da.latitude, da.longitude, da.status_aktif, 
                a.foto_cover, a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif,
                pa.nama, pa.avatar, pa.no_telp, pa.tanggal_lahir, pa.nik, 
                pa.jenis_kelamin, pa.status_aktif, aa.email, aa.status_aktif, 
                aa.status_super_admin
            FROM admin_akomodasi aa, pegawai_akomodasi pa, akomodasi a, desa_adat da, 
                kecamatan kec, kabupaten kab, provinsi p, negara n
            WHERE aa.id_pegawai = pa.id
            AND pa.id_akomodasi = a.id
            AND a.id_desa = da.id
            AND da.id_kecamatan = kec.id
            AND kec.id_kabupaten = kab.id
            AND kab.id_provinsi = p.id
            AND p.id_negara = n.id
            AND aa.id = ?
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
        
        $stmt->bind_result($pegawai_id, $akomodasi_id, $desa_adat_id, $kecamatan_id, $kabupaten_id, $provinsi_id, 
            $negara_id, $negara_name, $negara_flag, $negara_active_status, $provinsi_name, $provinsi_active_status, 
            $kabupaten_name, $kabupaten_active_status, $kecamatan_name, $kecamatan_active_status, 
            $desa_adat_name, $desa_adat_latitude, $desa_adat_longitude, $desa_adat_active_status, 
            $akomodasi_cover, $akomodasi_description, $akomodasi_logo, $akomodasi_name, 
            $akomodasi_location, $akomodasi_active_status, $pegawai_name, $pegawai_avatar, $pegawai_phone, 
            $pegawai_date_of_birth, $pegawai_nik, $pegawai_gender, $pegawai_active_status, $email, $active_status, $super_admin_status);
        $stmt->fetch();
        $stmt->close();
        
        $author = [
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
        ];
    }
    
    $cover = upload_image("cover");
    $table_name = $role == "admin_desa" ? "berita_desa_adat" : "berita_akomodasi";
    
    $query = "INSERT INTO $table_name VALUES (NULL, ?, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssss", $id, $title, $cover, $content, $time);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $news_id = $mysqli->insert_id;
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => [
            "id" => $news_id,
            $role => $author,
            "title" => $title,
            "cover" => $cover,
            "content" => $content,
            "time" => $time,
            "active_status" => true
        ],
        "message" => "Data berita berhasil ditambahkan"
    ];
    
    echo json_encode($response);