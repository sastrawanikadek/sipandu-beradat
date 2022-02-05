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
    
    if (!(isset($_GET["id_desa"]) && $_GET["id_desa"])) {
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
    $id_desa = $_GET["id_desa"];
    
    $mysqli = connect_db();
    $query = "
        SELECT COUNT(*) 
        FROM pecalang pda, masyarakat m, banjar b
        WHERE pda.status_aktif = 1
        AND pda.id_masyarakat = m.id
        AND m.id_banjar = b.id
        AND b.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_pecalang);
    $stmt->fetch();
    $stmt->close();
    
    $query = "SELECT COUNT(*) FROM sirine_desa sd WHERE sd.status_aktif = 1 AND sd.id_desa = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_sirine);
    $stmt->fetch();
    $stmt->close();
    
    $categories = ["Krama Wid", "Krama Tamiu", "Tamiu"];
    $masyarakat_categories = [];
    $total_masyarakat = 0;
    $query = "
        SELECT m.kategori, COUNT(m.kategori) AS total
        FROM masyarakat m, banjar b
        WHERE m.id_status_aktif = (SELECT id FROM status_aktif_masyarakat WHERE nama = 'Aktif')
        AND m.status_valid = 1
        AND m.id_banjar = b.id
        AND b.id_desa = ?
        GROUP BY m.kategori
        ORDER BY total DESC
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($category, $total_category);
    
    while ($stmt->fetch()) {
        $masyarakat_categories[$categories[intval($category)]] = $total_category;
        $total_masyarakat += $total_category;
    }
    
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan pl
        WHERE DATE(pl.waktu) = ?
        AND pl.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $now, $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_today_pelaporan);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_darurat pldr
        WHERE DATE(pldr.waktu) = ?
        AND pldr.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $now, $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_today_pelaporan_darurat);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_tamu plt
        WHERE DATE(plt.waktu) = ?
        AND plt.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $now, $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_today_pelaporan_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_darurat_tamu pldrt
        WHERE DATE(pldrt.waktu) = ?
        AND pldrt.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $now, $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_today_pelaporan_darurat_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $total_block = 0;
    $query = "SELECT m.id FROM masyarakat m WHERE m.id_status_aktif = (SELECT id FROM status_aktif_masyarakat WHERE nama = 'Aktif')";
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
    $stmt->bind_result($masyarakat_id);
    
    while($stmt->fetch()) {
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
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("ss", $masyarakat_id, $masyarakat_id);
        
        if (!$stmt2->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt2->bind_result($block_counter);
        $stmt2->fetch();
        $stmt2->close();
        
        if ($block_counter >= 3) {
            $total_block += 1;
        }
    }
    
    $stmt->close();
    
    $query = "SELECT COUNT(*) FROM banjar b WHERE b.status_aktif = 1 AND b.id_desa = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_banjar);
    $stmt->fetch();
    $stmt->close();
    
    
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
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan pl
            WHERE YEAR(pl.waktu) = ?
            AND MONTH(pl.waktu) = ?
            AND pl.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $cur_year, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan);
        $stmt->fetch();
        $stmt->close();
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_tamu plt
            WHERE YEAR(plt.waktu) = ?
            AND MONTH(plt.waktu) = ?
            AND plt.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $cur_year, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_tamu);
        $stmt->fetch();
        $stmt->close();
        
        $this_year_pelaporan[$month_names[$i - 1]] += $total_pelaporan;
        $this_year_pelaporan[$month_names[$i - 1]] += $total_pelaporan_tamu;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat pldr
            WHERE YEAR(pldr.waktu) = ?
            AND MONTH(pldr.waktu) = ?
            AND pldr.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $cur_year, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_darurat);
        $stmt->fetch();
        $stmt->close();
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat_tamu pldrt
            WHERE YEAR(pldrt.waktu) = ?
            AND MONTH(pldrt.waktu) = ?
            AND pldrt.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $cur_year, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_darurat_tamu);
        $stmt->fetch();
        $stmt->close();
        
        $this_year_pelaporan_darurat[$month_names[$i - 1]] += $total_pelaporan_darurat;
        $this_year_pelaporan_darurat[$month_names[$i - 1]] += $total_pelaporan_darurat_tamu;
    }
    
    for ($i = 1; $i <= $last_day_of_month; $i++) {
        $this_month_pelaporan[$i] = 0;
        $this_month_pelaporan_darurat[$i] = 0;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan pl
            WHERE YEAR(pl.waktu) = ?
            AND MONTH(pl.waktu) = ?
            AND DAY(pl.waktu) = ?
            AND pl.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $cur_year, $cur_month, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan);
        $stmt->fetch();
        $stmt->close();
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_tamu plt
            WHERE YEAR(plt.waktu) = ?
            AND MONTH(plt.waktu) = ?
            AND DAY(plt.waktu) = ?
            AND plt.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $cur_year, $cur_month, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_tamu);
        $stmt->fetch();
        $stmt->close();
        
        $this_month_pelaporan[$i] += $total_pelaporan;
        $this_month_pelaporan[$i] += $total_pelaporan_tamu;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat pldr
            WHERE YEAR(pldr.waktu) = ?
            AND MONTH(pldr.waktu) = ?
            AND DAY(pldr.waktu) = ?
            AND pldr.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $cur_year, $cur_month, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_darurat);
        $stmt->fetch();
        $stmt->close();
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat_tamu pldrt
            WHERE YEAR(pldrt.waktu) = ?
            AND MONTH(pldrt.waktu) = ?
            AND DAY(pldrt.waktu) = ?
            AND pldrt.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $cur_year, $cur_month, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_darurat_tamu);
        $stmt->fetch();
        $stmt->close();
        
        $this_month_pelaporan_darurat[$i] += $total_pelaporan_darurat;
        $this_month_pelaporan_darurat[$i] += $total_pelaporan_darurat_tamu;
    }
    
    for ($i = 0; $i < count($day_names); $i++) {
        $this_week_pelaporan[$day_names[$i]] = 0;
        $this_week_pelaporan_darurat[$day_names[$i]] = 0;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan pl
            WHERE YEAR(pl.waktu) = ?
            AND MONTH(pl.waktu) = ?
            AND WEEK(pl.waktu) = WEEK(?)
            AND WEEKDAY(pl.waktu) = ?
            AND pl.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $cur_year, $cur_month, $now, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan);
        $stmt->fetch();
        $stmt->close();
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_tamu plt
            WHERE YEAR(plt.waktu) = ?
            AND MONTH(plt.waktu) = ?
            AND WEEK(plt.waktu) = WEEK(?)
            AND WEEKDAY(plt.waktu) = ?
            AND plt.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $cur_year, $cur_month, $now, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_tamu);
        $stmt->fetch();
        $stmt->close();
        
        $this_week_pelaporan[$day_names[$i]] += $total_pelaporan;
        $this_week_pelaporan[$day_names[$i]] += $total_pelaporan_tamu;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat pldr
            WHERE YEAR(pldr.waktu) = ?
            AND MONTH(pldr.waktu) = ?
            AND WEEK(pldr.waktu) = WEEK(?)
            AND WEEKDAY(pldr.waktu) = ?
            AND pldr.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $cur_year, $cur_month, $now, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_darurat);
        $stmt->fetch();
        $stmt->close();
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat_tamu pldrt
            WHERE YEAR(pldrt.waktu) = ?
            AND MONTH(pldrt.waktu) = ?
            AND WEEK(pldrt.waktu) = WEEK(?)
            AND WEEKDAY(pldrt.waktu) = ?
            AND pldrt.id_desa = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $cur_year, $cur_month, $now, $i, $id_desa);
        
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($total_pelaporan_darurat_tamu);
        $stmt->fetch();
        $stmt->close();
        
        $this_week_pelaporan_darurat[$day_names[$i]] += $total_pelaporan_darurat;
        $this_week_pelaporan_darurat[$day_names[$i]] += $total_pelaporan_darurat_tamu;
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan pl
        WHERE pl.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_entire_pelaporan);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_tamu plt
        WHERE plt.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_entire_pelaporan_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_darurat pldr
        WHERE pldr.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_entire_pelaporan_darurat);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_darurat_tamu pldrt
        WHERE pldrt.id_desa = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id_desa);
    
    if (!$stmt->execute()) {
        $response = [
            "status_code" => 500,
            "data" => null,
            "message" => "Terjadi kesalahan pada server: ".$mysqli->error
        ];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($total_entire_pelaporan_darurat_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => [
            "masyarakat_categories" => $masyarakat_categories,
            "this_year_pelaporan" => $this_year_pelaporan,
            "this_year_pelaporan_darurat" => $this_year_pelaporan_darurat,
            "this_month_pelaporan" => $this_month_pelaporan,
            "this_month_pelaporan_darurat" => $this_month_pelaporan_darurat,
            "this_week_pelaporan" => $this_week_pelaporan,
            "this_week_pelaporan_darurat" => $this_week_pelaporan_darurat,
            "total_pecalang" => $total_pecalang,
            "total_sirine" => $total_sirine,
            "total_masyarakat" => $total_masyarakat,
            "total_today_pelaporan" => $total_today_pelaporan + $total_today_pelaporan_tamu + $total_today_pelaporan_darurat + $total_today_pelaporan_darurat_tamu,
            "total_pelaporan" => $total_entire_pelaporan + $total_entire_pelaporan_tamu + $total_entire_pelaporan_darurat + $total_entire_pelaporan_darurat_tamu,
            "total_block" => $total_block,
            "total_banjar" => $total_banjar
        ],
        "message" => "Data statistik berhasil diperoleh"
    ];
    echo json_encode($response);