<?php
    require_once("../../config.php");
    
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Halaman tidak ditemukan"
        ];
        echo json_encode($response);
        exit();
    }
    
    if (!(isset($_GET["id_instansi"]) && $_GET["id_instansi"])) {
        $response = [
            "status_code" => 400,
            "data" => null,
            "message" => "Mohon lengkapi semua kolom"
        ];
        echo json_encode($response);
        exit();
    }
    
    $cur_year = date("Y");
    $cur_month = date("m");
    $cur_day = date("d");
    
    if (isset($_GET["year"]) && $_GET["year"]) {
        $cur_year = $_GET["year"];
    }
    
    if (isset($_GET["month"]) && $_GET["month"]) {
        $cur_month = strlen($_GET["month"]) == 1 ? "0".$_GET["month"] : $_GET["month"];
    }
    
    if (isset($_GET["day"]) && $_GET["day"]) {
        $cur_day = strlen($_GET["day"]) == 1 ? "0".$_GET["day"] : $_GET["day"];
    }
    
    $now = "$cur_year-$cur_month-$cur_day";
    $now_dt = new DateTime($now);
    $last_day_of_month = intval($now_dt->format("t"));
    $id_instansi = $_GET["id_instansi"];
    
    $mysqli = connect_db();
    $query = "
        SELECT COUNT(*) 
        FROM petugas pt 
        WHERE pt.id_instansi = ? 
        AND pt.status_aktif = 1
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_instansi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_petugas);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT ip.status_pelaporan
        FROM instansi_petugas ip
        WHERE ip.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_instansi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($report_status);
    $stmt->fetch();
    $stmt->close();
    
    $report_types = [];
    $query = "
        SELECT opi.id_jenis_pelaporan
        FROM otoritas_pelaporan_instansi opi
        WHERE opi.id_instansi = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_instansi);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($jenis_pelaporan_id);
    
    while ($stmt->fetch()) {
        array_push($report_types, $jenis_pelaporan_id);
    }
    
    $stmt->close();
    
    $total_today_pelaporan = 0;
    $total_today_pelaporan_tamu = 0;
    $total_today_pelaporan_darurat = 0;
    $total_today_pelaporan_darurat_tamu = 0;
    
    if (intval($report_status) > 0) {
        $query = "
            SELECT pl.id, pl.id_jenis_pelaporan
            FROM pelaporan pl
            WHERE DATE(pl.waktu) = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $now);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdapl.status, COUNT(pdapl.status) AS total 
                FROM pecalang_pelaporan pdapl 
                WHERE pdapl.id_pelaporan = ? 
                GROUP BY pdapl.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }

            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_today_pelaporan += 1;
            }
        }
        
        $stmt->close();
        
        $query = "
            SELECT plt.id, plt.id_jenis_pelaporan
            FROM pelaporan_tamu plt
            WHERE DATE(plt.waktu) = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $now);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
                FROM pecalang_pelaporan_tamu pdaplt 
                WHERE pdaplt.id_pelaporan_tamu = ? 
                GROUP BY pdaplt.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }
            
            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_today_pelaporan_tamu += 1;
            }
        }
        
        $stmt->close();
        
        $query = "
            SELECT pldr.id, pldr.id_jenis_pelaporan
            FROM pelaporan_darurat pldr
            WHERE DATE(pldr.waktu) = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $now);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdapldr.status, COUNT(pdapldr.status) AS total 
                FROM pecalang_pelaporan_darurat pdapldr 
                WHERE pdapldr.id_pelaporan_darurat = ? 
                GROUP BY pdapldr.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }

            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_today_pelaporan_darurat += 1;
            }
        }
        
        $stmt->close();
        
        $query = "
            SELECT pldrt.id, pldrt.id_jenis_pelaporan
            FROM pelaporan_darurat_tamu pldrt
            WHERE DATE(pldrt.waktu) = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $now);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
                FROM pecalang_pelaporan_darurat_tamu pdapldrt 
                WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
                GROUP BY pdapldrt.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }
            
            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_today_pelaporan_darurat_tamu += 1;
            }
        }
        
        $stmt->close();
    }
    
    $month_names = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", 
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    $day_names = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
    
    $this_year_pelaporan = [];
    $this_year_pelaporan_darurat = [];
    $this_month_pelaporan = [];
    $this_month_pelaporan_darurat = [];
    $this_week_pelaporan = [];
    $this_week_pelaporan_darurat = [];
    
    for ($i = 1; $i <= count($month_names); $i++) {
        $this_year_pelaporan[$month_names[$i - 1]] = 0;
        $this_year_pelaporan_darurat[$month_names[$i - 1]] = 0;
        
        if (intval($report_status) > 0) {
            $query = "
                SELECT pl.id, pl.id_jenis_pelaporan
                FROM pelaporan pl
                WHERE YEAR(pl.waktu) = ?
                AND MONTH(pl.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $cur_year, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapl.status, COUNT(pdapl.status) AS total 
                    FROM pecalang_pelaporan pdapl 
                    WHERE pdapl.id_pelaporan = ? 
                    GROUP BY pdapl.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_year_pelaporan[$month_names[$i - 1]] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT plt.id, plt.id_jenis_pelaporan
                FROM pelaporan_tamu plt
                WHERE YEAR(plt.waktu) = ?
                AND MONTH(plt.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $cur_year, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
                    FROM pecalang_pelaporan_tamu pdaplt 
                    WHERE pdaplt.id_pelaporan_tamu = ? 
                    GROUP BY pdaplt.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_year_pelaporan[$month_names[$i - 1]] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT pldr.id, pldr.id_jenis_pelaporan
                FROM pelaporan_darurat pldr
                WHERE YEAR(pldr.waktu) = ?
                AND MONTH(pldr.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $cur_year, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapldr.status, COUNT(pdapldr.status) AS total 
                    FROM pecalang_pelaporan_darurat pdapldr 
                    WHERE pdapldr.id_pelaporan_darurat = ? 
                    GROUP BY pdapldr.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_year_pelaporan_darurat[$month_names[$i - 1]] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT pldrt.id, pldrt.id_jenis_pelaporan
                FROM pelaporan_darurat_tamu pldrt
                WHERE YEAR(pldrt.waktu) = ?
                AND MONTH(pldrt.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $cur_year, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
                    FROM pecalang_pelaporan_darurat_tamu pdapldrt 
                    WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
                    GROUP BY pdapldrt.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_year_pelaporan_darurat[$month_names[$i - 1]] += 1;
                }
            }
            
            $stmt->close();
        }
    }
    
    for ($i = 1; $i <= $last_day_of_month; $i++) {
        $this_month_pelaporan[$i] = 0;
        $this_month_pelaporan_darurat[$i] = 0;
        
        if (intval($report_status) > 0) {
            $query = "
                SELECT pl.id, pl.id_jenis_pelaporan
                FROM pelaporan pl
                WHERE YEAR(pl.waktu) = ?
                AND MONTH(pl.waktu) = ?
                AND DAY(pl.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $cur_year, $cur_month, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapl.status, COUNT(pdapl.status) AS total 
                    FROM pecalang_pelaporan pdapl 
                    WHERE pdapl.id_pelaporan = ? 
                    GROUP BY pdapl.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_month_pelaporan[$i] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT plt.id, plt.id_jenis_pelaporan
                FROM pelaporan_tamu plt
                WHERE YEAR(plt.waktu) = ?
                AND MONTH(plt.waktu) = ?
                AND DAY(plt.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $cur_year, $cur_month, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
                    FROM pecalang_pelaporan_tamu pdaplt 
                    WHERE pdaplt.id_pelaporan_tamu = ? 
                    GROUP BY pdaplt.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_month_pelaporan[$i] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT pldr.id, pldr.id_jenis_pelaporan
                FROM pelaporan_darurat pldr
                WHERE YEAR(pldr.waktu) = ?
                AND MONTH(pldr.waktu) = ?
                AND DAY(pldr.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $cur_year, $cur_month, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapldr.status, COUNT(pdapldr.status) AS total 
                    FROM pecalang_pelaporan_darurat pdapldr 
                    WHERE pdapldr.id_pelaporan_darurat = ? 
                    GROUP BY pdapldr.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_month_pelaporan_darurat[$i] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT pldrt.id, pldrt.id_jenis_pelaporan
                FROM pelaporan_darurat_tamu pldrt
                WHERE YEAR(pldrt.waktu) = ?
                AND MONTH(pldrt.waktu) = ?
                AND DAY(pldrt.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $cur_year, $cur_month, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
                    FROM pecalang_pelaporan_darurat_tamu pdapldrt 
                    WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
                    GROUP BY pdapldrt.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_month_pelaporan_darurat[$i] += 1;
                }
            }
            
            $stmt->close();
        }
    }
    
    for ($i = 0; $i < count($day_names); $i++) {
        $this_week_pelaporan[$day_names[$i]] = 0;
        $this_week_pelaporan_darurat[$day_names[$i]] = 0;
        
        if (intval($report_status) > 0) {
            $query = "
                SELECT pl.id, pl.id_jenis_pelaporan
                FROM pelaporan pl
                WHERE YEAR(pl.waktu) = ?
                AND MONTH(pl.waktu) = ?
                AND WEEK(pl.waktu) = WEEK(?)
                AND WEEKDAY(pl.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssss", $cur_year, $cur_month, $now, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapl.status, COUNT(pdapl.status) AS total 
                    FROM pecalang_pelaporan pdapl 
                    WHERE pdapl.id_pelaporan = ? 
                    GROUP BY pdapl.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_week_pelaporan[$day_names[$i]] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT plt.id, plt.id_jenis_pelaporan
                FROM pelaporan_tamu plt
                WHERE YEAR(plt.waktu) = ?
                AND MONTH(plt.waktu) = ?
                AND WEEK(plt.waktu) = WEEK(?)
                AND WEEKDAY(plt.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssss", $cur_year, $cur_month, $now, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
                    FROM pecalang_pelaporan_tamu pdaplt 
                    WHERE pdaplt.id_pelaporan_tamu = ? 
                    GROUP BY pdaplt.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_week_pelaporan[$day_names[$i]] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT pldr.id, pldr.id_jenis_pelaporan
                FROM pelaporan_darurat pldr
                WHERE YEAR(pldr.waktu) = ?
                AND MONTH(pldr.waktu) = ?
                AND WEEK(pldr.waktu) = WEEK(?)
                AND WEEKDAY(pldr.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssss", $cur_year, $cur_month, $now, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapldr.status, COUNT(pdapldr.status) AS total 
                    FROM pecalang_pelaporan_darurat pdapldr 
                    WHERE pdapldr.id_pelaporan_darurat = ? 
                    GROUP BY pdapldr.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_week_pelaporan_darurat[$day_names[$i]] += 1;
                }
            }
            
            $stmt->close();
            
            $query = "
                SELECT pldrt.id, pldrt.id_jenis_pelaporan
                FROM pelaporan_darurat_tamu pldrt
                WHERE YEAR(pldrt.waktu) = ?
                AND MONTH(pldrt.waktu) = ?
                AND WEEK(pldrt.waktu) = WEEK(?)
                AND WEEKDAY(pldrt.waktu) = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssss", $cur_year, $cur_month, $now, $i);
            
            if (!$stmt->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt->store_result();
            $stmt->bind_result($report_id, $jenis_pelaporan_id);
            
            while ($stmt->fetch()) {
                $status = null;
                $query = "
                    SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
                    FROM pecalang_pelaporan_darurat_tamu pdapldrt 
                    WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
                    GROUP BY pdapldrt.status 
                    ORDER BY total DESC
                    LIMIT 1
                ";
                $stmt2 = $mysqli->prepare($query);
                $stmt2->bind_param("s", $report_id);
                
                if (!$stmt2->execute()) {
                    $response = [
                        "status_code" => 500,
                        "data" => null,
                        "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                    ];
                    echo json_encode($response);
                    exit();
                }
                
                $stmt2->bind_result($status, $total);
                $stmt2->fetch();
                $stmt2->close();

                if (!$status || intval($status) < 1) {
                    continue;
                }

                if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                    $this_week_pelaporan_darurat[$day_names[$i]] += 1;
                }
            }
            
            $stmt->close();
        }
    }
    
    $total_entire_pelaporan = 0;
    $total_entire_pelaporan_tamu = 0;
    $total_entire_pelaporan_darurat = 0;
    $total_entire_pelaporan_darurat_tamu = 0;
    
    if (intval($report_status) > 0) {
        $query = "
            SELECT pl.id, pl.id_jenis_pelaporan
            FROM pelaporan pl
        ";
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
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdapl.status, COUNT(pdapl.status) AS total 
                FROM pecalang_pelaporan pdapl 
                WHERE pdapl.id_pelaporan = ? 
                GROUP BY pdapl.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }

            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_entire_pelaporan += 1;
            }
        }
        
        $stmt->close();
        
        $query = "
            SELECT plt.id, plt.id_jenis_pelaporan
            FROM pelaporan_tamu plt
        ";
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
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdaplt.status, COUNT(pdaplt.status) AS total 
                FROM pecalang_pelaporan_tamu pdaplt 
                WHERE pdaplt.id_pelaporan_tamu = ? 
                GROUP BY pdaplt.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }

            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_entire_pelaporan_tamu += 1;
            }
        }
        
        $stmt->close();
        
        $query = "
            SELECT pldr.id, pldr.id_jenis_pelaporan
            FROM pelaporan_darurat pldr
        ";
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
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdapldr.status, COUNT(pdapldr.status) AS total 
                FROM pecalang_pelaporan_darurat pdapldr 
                WHERE pdapldr.id_pelaporan_darurat = ? 
                GROUP BY pdapldr.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }

            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_entire_pelaporan_darurat += 1;
            }
        }
        
        $stmt->close();
        
        $query = "
            SELECT pldrt.id, pldrt.id_jenis_pelaporan
            FROM pelaporan_darurat_tamu pldrt
        ";
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
        
        $stmt->store_result();
        $stmt->bind_result($report_id, $jenis_pelaporan_id);
        
        while ($stmt->fetch()) {
            $status = null;
            $query = "
                SELECT pdapldrt.status, COUNT(pdapldrt.status) AS total 
                FROM pecalang_pelaporan_darurat_tamu pdapldrt 
                WHERE pdapldrt.id_pelaporan_darurat_tamu = ? 
                GROUP BY pdapldrt.status 
                ORDER BY total DESC
                LIMIT 1
            ";
            $stmt2 = $mysqli->prepare($query);
            $stmt2->bind_param("s", $report_id);
            
            if (!$stmt2->execute()) {
                $response = [
                    "status_code" => 500,
                    "data" => null,
                    "message" => "Terjadi kesalahan pada server: ".$mysqli->error
                ];
                echo json_encode($response);
                exit();
            }
            
            $stmt2->bind_result($status, $total);
            $stmt2->fetch();
            $stmt2->close();

            if (!$status || intval($status) < 1) {
                continue;
            }

            if (intval($report_status) == 2 || array_search($jenis_pelaporan_id, $report_types) !== false) {
                $total_entire_pelaporan_darurat_tamu += 1;
            }
        }
        
        $stmt->close();
    }
    
    $response = [
        "status_code" => 200,
        "data" => [
            "this_year_pelaporan" => $this_year_pelaporan,
            "this_year_pelaporan_darurat" => $this_year_pelaporan_darurat,
            "this_month_pelaporan" => $this_month_pelaporan,
            "this_month_pelaporan_darurat" => $this_month_pelaporan_darurat,
            "this_week_pelaporan" => $this_week_pelaporan,
            "this_week_pelaporan_darurat" => $this_week_pelaporan_darurat,
            "total_petugas" => $total_petugas,
            "total_today_pelaporan" => $total_today_pelaporan + $total_today_pelaporan_tamu + $total_today_pelaporan_darurat + $total_today_pelaporan_darurat_tamu,
            "total_pelaporan" => $total_entire_pelaporan + $total_entire_pelaporan_tamu,
            "total_pelaporan_darurat" => $total_entire_pelaporan_darurat + $total_entire_pelaporan_darurat_tamu
        ],
        "message" => "Data statistik berhasil diperoleh"
    ];
    echo json_encode($response);