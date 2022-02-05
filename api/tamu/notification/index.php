<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");
    
    $role = decode_jwt_token(["tamu"]);
    $id = $role["id"];
    
    $notifications = [];
    $mysqli = connect_db();
    $query = "
        SELECT nt.id, nt.foto, nt.judul, nt.keterangan, nt.jenis, nt.data
        FROM notifikasi_tamu nt
        WHERE nt.id_tamu = ?
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
    
    $stmt->bind_result($id, $photo, $title, $description, $type, $data);
    
    while ($stmt->fetch()) {
        array_push($notifications, [
            "id" => $id,
            "photo" => $photo,
            "title" => $title,
            "description" => $description,
            "type" => intval($type),
            "data" => $data
        ]);
    }
    
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => $notifications,
        "message" => "Data notifikasi berhasil diperoleh"
    ];
    echo json_encode($response);