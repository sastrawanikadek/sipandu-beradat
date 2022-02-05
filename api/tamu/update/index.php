<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    require_once("../../helpers/file.php");

    decode_jwt_token(["admin_akomodasi"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"] &&
        isset($_POST["id_akomodasi"]) && $_POST["id_akomodasi"] &&
        isset($_POST["id_negara"]) && $_POST["id_negara"] &&
        isset($_POST["name"]) && $_POST["name"] &&
        isset($_POST["email"]) && $_POST["email"] &&
        isset($_POST["phone"]) && $_POST["phone"] &&
        isset($_POST["date_of_birth"]) && $_POST["date_of_birth"] &&
        isset($_POST["identity_type"]) && ($_POST["identity_type"] || $_POST["identity_type"] == 0) &&
        isset($_POST["identity_number"]) && $_POST["identity_number"] &&
        isset($_POST["gender"]) && $_POST["gender"] &&
        isset($_POST["active_status"]) && isset($_POST["valid_status"]))) {
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
    
    if ($_POST["date_of_birth"] > date("Y-m-d")) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Tanggal lahir tidak valid"
        ];
        echo json_encode($response);
        exit();
    }
    
    if ($_POST["gender"] != "l" && $_POST["gender"] != "p") {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Jenis kelamin harus salah satu dari l atau p"
        ];
        echo json_encode($response);
        exit();
    }
    
    $identity_types = ["Identity Card", "Driving License", "Passport"];

    $id = $_POST["id"];
    $id_akomodasi = $_POST["id_akomodasi"];
    $id_negara = $_POST["id_negara"];
    $name = $_POST["name"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $phone = $_POST["phone"];
    $date_of_birth = $_POST["date_of_birth"];
    $identity_type = $_POST["identity_type"];
    $identity_number = $_POST["identity_number"];
    $gender = $_POST["gender"];
    $active_status = intval(json_decode($_POST["active_status"]));
    $valid_status = intval(json_decode($_POST["valid_status"]));
    
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
        SELECT n.id, n.nama, n.bendera, n.status_aktif, p.id, p.nama, p.status_aktif, 
            kab.id, kab.nama, kab.status_aktif, kec.id, kec.nama, kec.status_aktif,
            da.id, da.nama, da.latitude, da.longitude, da.status_aktif, a.foto_cover, 
            a.deskripsi, a.logo, a.nama, a.alamat, a.status_aktif
        FROM negara n, provinsi p, kabupaten kab, kecamatan kec, desa_adat da, akomodasi a
        WHERE a.id_desa = da.id
        AND da.id_kecamatan = kec.id
        AND kec.id_kabupaten = kab.id
        AND kab.id_provinsi = p.id
        AND p.id_negara = n.id
        AND a.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_akomodasi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_id, $negara_name, $negara_flag, $negara_active_status, 
        $provinsi_id, $provinsi_name, $provinsi_active_status, $kabupaten_id, 
        $kabupaten_name, $kabupaten_active_status, $kecamatan_id, $kecamatan_name, 
        $kecamatan_active_status, $desa_adat_id, $desa_adat_name, $desa_adat_latitude, 
        $desa_adat_longitude, $desa_adat_active_status, $akomodasi_cover, 
        $akomodasi_description, $akomodasi_logo, $akomodasi_name, $akomodasi_location, 
        $akomodasi_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$akomodasi_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data akomodasi tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "SELECT nt.nama, nt.bendera, nt.status_aktif FROM negara nt WHERE nt.id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_negara);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($negara_tamu_name, $negara_tamu_flag, $negara_tamu_active_status);
    $stmt->fetch();
    $stmt->close();
    
    if (!$negara_tamu_name) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Data negara tidak ada"
        ];

        echo json_encode($response);
        exit();
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM tamu t 
        WHERE t.id_akomodasi = ?
        AND ((t.username = ?) OR (t.jenis_identitas = ? AND t.no_identitas = ? AND t.id_negara = ?))
        AND t.id <> ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id_akomodasi, $username, $identity_type, 
        $identity_number, $id_negara, $id);
    
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
    
    if (isset($_FILES["avatar"])) {
        $avatar = upload_image("avatar");
    } else {
        $query = "SELECT t.avatar FROM tamu t WHERE t.id = ?";
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
        
        $stmt->bind_result($avatar);
        $stmt->fetch();
        $stmt->close();
    }
    
    $query = "
        UPDATE tamu t
        SET t.id_akomodasi = ?,
            t.id_negara = ?,
            t.nama = ?,
            t.email = ?,
            t.avatar = ?,
            t.no_telp = ?,
            t.tanggal_lahir = ?,
            t.jenis_identitas = ?,
            t.no_identitas = ?,
            t.jenis_kelamin = ?,
            t.status_aktif = ?,
            t.status_valid = ?
        WHERE t.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssssssssss", $id_akomodasi, $id_negara, $name, $email, $avatar, 
        $phone, $date_of_birth, $identity_type, $identity_number, $gender, $active_status, $valid_status, $id);

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
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $id);
    
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
    
    $response = [
        "status_code" => 200, 
        "data" => [
            "id" => $id,
            "akomodasi" => [
                "id" => $id_akomodasi,
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
                "id" => $id_negara,
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
        "message" => "Data tamu berhasil diubah"
    ];

    echo json_encode($response);