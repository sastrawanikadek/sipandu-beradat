<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    decode_jwt_token(["admin_desa"]);
    
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
        DELETE ltv
        FROM laporan_tidak_valid ltv, pelaporan p
        WHERE ltv.id_pelaporan = p.id
        AND p.id_masyarakat = ?
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
        DELETE ldtv
        FROM laporan_darurat_tidak_valid ldtv, pelaporan_darurat pd
        WHERE ldtv.id_pelaporan_darurat = pd.id
        AND pd.id_masyarakat = ?
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
    $query = "INSERT INTO riwayat_buka_blokir_masyarakat VALUES (NULL, ?, ?)";
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
        "message" => "Status blokir masyarakat telah berhasil dibuka"
    ];

    echo json_encode($response);