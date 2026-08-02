<?php
$db_host='localhost'; $db_name='YOUR_DATABASE_NAME'; $db_user='YOUR_DATABASE_USER'; $db_pass='YOUR_DATABASE_PASSWORD';
try{$pdo=new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",$db_user,$db_pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
catch(PDOException $e){http_response_code(500);die('Database connection failed. Check config/db.php.');}
?>