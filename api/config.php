<?php
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
    require_once(__DIR__."/vendor/autoload.php");
    require_once(__DIR__."/cors.php");
    require_once(__DIR__."/connection.php");
    
    use Dotenv\Dotenv;
    use Cloudinary\Configuration\Configuration;
    use Kreait\Firebase\Factory;

    $res_dir = __DIR__."/res";
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    Configuration::instance([
        'cloud' => [
            'cloud_name' => $_ENV["CLOUDINARY_CLOUD_NAME"], 
            'api_key' => $_ENV["CLOUDINARY_API_KEY"], 
            'api_secret' => $_ENV["CLOUDINARY_SECRET_KEY"]
        ],
        'url' => [
            'secure' => true
        ]
    ]);
    
    $factory = (new Factory)->withServiceAccount(__DIR__."/iottopik-firebase-adminsdk-62102-34524883d9.json")
        ->withDatabaseUri('https://iottopik-default-rtdb.firebaseio.com');

    date_default_timezone_set('Asia/Makassar');