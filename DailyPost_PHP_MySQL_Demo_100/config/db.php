<?php
// Local XAMPP settings. These are replaced with the real host's
// credentials at deploy time - see config/db.live.example.php
$db_host='localhost'; $db_name='dailypost'; $db_user='root'; $db_pass='';
try{$pdo=new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",$db_user,$db_pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
catch(PDOException $e){http_response_code(500);die('Database connection failed. Check config/db.php.');}
?>