<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["superadmin"]);
    
    if (!(isset($_POST["name"]) && $_POST["name"])) {
        $response = [
            "status_code" => 400, 
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];

        echo json_encode($response);
        exit();
    }

    $name = $_POST["name"];
    
    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM jenis_instansi_petugas jip WHERE jip.nama = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $name);
    
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
    
    $query = "INSERT INTO jenis_instansi_petugas VALUES (NULL, ?, 1)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $name);

    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500, 
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
    
        echo json_encode($response);
        exit();
    }
            
    $id = $mysqli->insert_id;
    $response = [
        "status_code" => 200, 
        "data" => ["id" => $id, "name" => $name, "active_status" => true],
        "message" => "Data jenis instansi petugas berhasil ditambahkan"
    ];

    echo json_encode($response);