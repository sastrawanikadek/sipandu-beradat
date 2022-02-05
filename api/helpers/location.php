<?php
    function find_in_radius(string $table, string $id_col, string $lat_col, string $lng_col, 
        float $distance_in_miles, float $latitude, float $longitude) {
        $lat1 = $latitude - ($distance_in_miles / 69);
        $lat2 = $latitude + ($distance_in_miles / 69);
        
        $lng1 = $longitude - ($distance_in_miles / abs(cos(deg2rad($latitude)) * 69));
        $lng2 = $longitude + ($distance_in_miles / abs(cos(deg2rad($latitude)) * 69));
        
        $result = [];
        
        $mysqli = connect_db();
        $query = "
            SELECT t.$id_col, 3956 * 2 * ASIN(SQRT(POWER(SIN((? - t.$lat_col) * 
                pi() / 180 / 2), 2) + COS(? * pi() / 180) * COS(t.$lat_col * 
                pi() / 180) * POWER(SIN((? - t.$lng_col) * pi() /180 / 2), 2))) AS distance
            FROM $table t
            WHERE t.$lng_col BETWEEN ? AND ?
            AND t.$lat_col BETWEEN ? AND ?
            HAVING distance < ?
            ORDER BY distance
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssss", $latitude, $latitude, $longitude, $lng1, 
            $lng2, $lat1, $lat2, $distance_in_miles);
            
        if (!$stmt->execute()) {
            $response = [
                "status_code" => 500,
                "data" => null,
                "message" => "Terjadi kesalahan pada server: ".$mysqli->error
            ];
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_result($id, $distance);
        
        while ($stmt->fetch()) {
            array_push($result, ["id" => $id, "distance" => $distance]);
        }
        
        $stmt->close();
        
        return $result;
    }