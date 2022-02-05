<?php
    require_once("../config.php");

    $mysqli = connect_db();
    $query = "SELECT p.id, p.maks_laporan_tidak_valid FROM pengaturan p";
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

    $stmt->bind_result($id, $max_invalid_report);
    $stmt->fetch();
    $stmt->close();

    $response = [
        "status_code" => 200,
        "data" => ["id" => $id, "max_invalid_report" => $max_invalid_report],
        "message" => "Data pengaturan berhasil diperoleh"
    ];
    echo json_encode($response);