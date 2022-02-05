<?php
    require_once("../../config.php");
    require_once("../../helpers/jwt.php");

    decode_jwt_token(["superadmin"]);

    if (!(isset($_POST["max_invalid_report"]) && $_POST["max_invalid_report"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }

    $max_invalid_report = intval($_POST["max_invalid_report"]);

    if ($max_invalid_report < 1) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Nilai maksimum laporan tidak valid harus lebih dari 0"
        ];
        echo json_encode($response);
        exit();
    }

    $mysqli = connect_db();
    $query = "SELECT COUNT(*) FROM pengaturan";
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

    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    if ($total > 0) {
        $query = "DELETE FROM pengaturan";
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
    }

    $query = "INSERT INTO pengaturan VALUES (NULL, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $max_invalid_report);

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
    $stmt->close();

    $response = [
        "status_code" => 200,
        "data" => ["id" => $id, "max_invalid_report" => $max_invalid_report],
        "message" => "Pengaturan telah berhasil ditambahkan"
    ];
    echo json_encode($response);
    exit();