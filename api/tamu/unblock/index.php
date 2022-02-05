<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["admin_akomodasi"]);
    
    if (!(isset($_POST["id"]) && $_POST["id"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }
    
    $id = $_POST["id"];
    
    $mysqli = connect_db();
    $query = "
        DELETE ltvt
        FROM laporan_tidak_valid_tamu ltvt, pelaporan_tamu pt
        WHERE ltvt.id_pelaporan_tamu = pt.id
        AND pt.id_tamu = ?
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
    
    $stmt->close();
    
    $query = "
        DELETE ldtvt
        FROM laporan_darurat_tidak_valid_tamu ldtvt, pelaporan_darurat_tamu pdt
        WHERE ldtvt.id_pelaporan_darurat_tamu = pdt.id
        AND pdt.id_tamu = ?
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
    
    $stmt->close();

    $now = date("Y-m-d H:i:s");
    $query = "INSERT INTO riwayat_buka_blokir_tamu VALUES (NULL, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id, $now);

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
        "data" => null,
        "message" => "Status blokir tamu telah berhasil dibuka"
    ];

    echo json_encode($response);