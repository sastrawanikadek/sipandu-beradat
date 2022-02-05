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
    
    if (!(isset($_GET["id_akomodasi"]) && $_GET["id_akomodasi"])) {
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
    $id_akomodasi = $_GET["id_akomodasi"];
    
    $mysqli = connect_db();
    $query = "
        SELECT COUNT(*) 
        FROM admin_akomodasi ak, pegawai_akomodasi pa
        WHERE ak.status_aktif = 1
        AND ak.id_pegawai = pa.id
        AND pa.id_akomodasi = ?
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
    
    $stmt->bind_result($total_admin);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pegawai_akomodasi pa
        WHERE pa.status_aktif = 1
        AND pa.id_akomodasi = ?
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
    
    $stmt->bind_result($total_pegawai);
    $stmt->fetch();
    $stmt->close();
    
    $query = "SELECT COUNT(*) FROM sirine_akomodasi sa WHERE sa.status_aktif = 1 AND sa.id_akomodasi = ?";
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
    
    $stmt->bind_result($total_sirine);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*)
        FROM tamu t
        WHERE t.status_aktif = 1
        AND t.status_valid = 1
        AND t.id_akomodasi = ?
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
    
    $stmt->bind_result($total_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_tamu plt, tamu t
        WHERE plt.id_tamu = t.id
        AND t.id_akomodasi = ?
        AND DATE(plt.waktu) = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_akomodasi, $now);
    
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
        FROM pelaporan_darurat_tamu pldrt, tamu t
        WHERE pldrt.id_tamu = t.id
        AND t.id_akomodasi = ?
        AND DATE(pldrt.waktu) = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id_akomodasi, $now);
    
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
    $query = "SELECT t.id FROM tamu t WHERE t.status_aktif = 1";
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
    $stmt->bind_result($tamu_id);
    
    while($stmt->fetch()) {
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
        $stmt2 = $mysqli->prepare($query);
        $stmt2->bind_param("ss", $tamu_id, $tamu_id);
        
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
            FROM pelaporan_tamu plt, tamu t
            WHERE plt.id_tamu = t.id
            AND YEAR(plt.waktu) = ?
            AND MONTH(plt.waktu) = ?
            AND t.id_akomodasi = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $cur_year, $i, $id_akomodasi);
        
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
        
        $this_year_pelaporan[$month_names[$i - 1]] += $total_pelaporan_tamu;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat_tamu pldrt, tamu t
            WHERE pldrt.id_tamu = t.id
            AND YEAR(pldrt.waktu) = ?
            AND MONTH(pldrt.waktu) = ?
            AND t.id_akomodasi = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $cur_year, $i, $id_akomodasi);
        
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
        
        $this_year_pelaporan_darurat[$month_names[$i - 1]] += $total_pelaporan_darurat_tamu;
    }
    
    for ($i = 1; $i <= $last_day_of_month; $i++) {
        $this_month_pelaporan[$i] = 0;
        $this_month_pelaporan_darurat[$i] = 0;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_tamu plt, tamu t
            WHERE plt.id_tamu = t.id
            AND YEAR(plt.waktu) = ?
            AND MONTH(plt.waktu) = ?
            AND DAY(plt.waktu) = ?
            AND t.id_akomodasi = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $cur_year, $cur_month, $i, $id_akomodasi);
        
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
        
        $this_month_pelaporan[$i] += $total_pelaporan_tamu;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat_tamu pldrt, tamu t
            WHERE pldrt.id_tamu = t.id
            AND YEAR(pldrt.waktu) = ?
            AND MONTH(pldrt.waktu) = ?
            AND DAY(pldrt.waktu) = ?
            AND t.id_akomodasi = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $cur_year, $cur_month, $i, $id_akomodasi);
        
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
        
        $this_month_pelaporan_darurat[$i] += $total_pelaporan_darurat_tamu;
    }
    
    for ($i = 0; $i < count($day_names); $i++) {
        $this_week_pelaporan[$day_names[$i]] = 0;
        $this_week_pelaporan_darurat[$day_names[$i]] = 0;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_tamu plt, tamu t
            WHERE plt.id_tamu = t.id
            AND YEAR(plt.waktu) = ?
            AND MONTH(plt.waktu) = ?
            AND WEEK(plt.waktu) = WEEK(?)
            AND WEEKDAY(plt.waktu) = ?
            AND t.id_akomodasi = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $cur_year, $cur_month, $now, $i, $id_akomodasi);
        
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
        
        $this_week_pelaporan[$day_names[$i]] += $total_pelaporan_tamu;
        
        $query = "
            SELECT COUNT(*) 
            FROM pelaporan_darurat_tamu pldrt, tamu t
            WHERE pldrt.id_tamu = t.id
            AND YEAR(pldrt.waktu) = ?
            AND MONTH(pldrt.waktu) = ?
            AND WEEK(pldrt.waktu) = WEEK(?)
            AND WEEKDAY(pldrt.waktu) = ?
            AND t.id_akomodasi = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $cur_year, $cur_month, $now, $i, $id_akomodasi);
        
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
        
        $this_week_pelaporan_darurat[$day_names[$i]] += $total_pelaporan_darurat_tamu;
    }
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_tamu plt, tamu t
        WHERE plt.id_tamu = t.id
        AND t.id_akomodasi = ?
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
    
    $stmt->bind_result($total_pelaporan_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $query = "
        SELECT COUNT(*) 
        FROM pelaporan_darurat_tamu pldrt, tamu t
        WHERE pldrt.id_tamu = t.id
        AND t.id_akomodasi = ?
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
    
    $stmt->bind_result($total_pelaporan_darurat_tamu);
    $stmt->fetch();
    $stmt->close();
    
    $response = [
        "status_code" => 200,
        "data" => [
            "this_year_pelaporan" => $this_year_pelaporan,
            "this_year_pelaporan_darurat" => $this_year_pelaporan_darurat,
            "this_month_pelaporan" => $this_month_pelaporan,
            "this_month_pelaporan_darurat" => $this_month_pelaporan_darurat,
            "this_week_pelaporan" => $this_week_pelaporan,
            "this_week_pelaporan_darurat" => $this_week_pelaporan_darurat,
            "total_admin" => $total_admin,
            "total_pegawai" => $total_pegawai,
            "total_sirine" => $total_sirine,
            "total_tamu" => $total_tamu,
            "total_today_pelaporan" => $total_today_pelaporan_tamu + $total_today_pelaporan_darurat_tamu,
            "total_pelaporan" => $total_pelaporan_tamu + $total_pelaporan_darurat_tamu,
            "total_block" => $total_block
        ],
        "message" => "Data statistik berhasil diperoleh"
    ];
    echo json_encode($response);