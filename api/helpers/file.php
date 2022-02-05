<?php
    function upload_image(string $name) {
        global $res_dir;
        $allowed_mimes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
        
        if (!isset($_FILES[$name])) {
            $response = [
                "status_code" => 400,
                "data" => null,
                "message" => "Gambar tidak boleh kosong"
            ];
            echo json_encode($response);
            exit();
        }
        
        if (!in_array($_FILES[$name]["type"], $allowed_mimes)) {
            $response = [
                "status_code" => 400,
                "data" => null,
                "message" => "Gambar harus memiliki ekstensi .jpeg, .jpg, .png, atau .gif"
            ];
            echo json_encode($response);
            exit();
        }
        
        if ($_FILES[$name]["size"] > 5 * 1024 * 1024) {
            $response = [
                "status_code" => 400,
                "data" => null,
                "message" => "Gambar harus kurang dari 5MB"
            ];
            echo json_encode($response);
            exit();
        }
        
        $name_tokens = explode(".", $_FILES[$name]["name"]);
        $ext = end($name_tokens);
        $fname = time()."-".rand().".$ext";
        $secure_url = "https://sipanduberadat.com/api/res/images/$fname";
        move_uploaded_file($_FILES[$name]["tmp_name"], "$res_dir/images/$fname");
        return $secure_url;
    }