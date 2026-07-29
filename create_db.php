<?php
$dsn = 'mysql:host=gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com;port=4000';
$user = '7i7y5jnB4C5ez9W.root';
$password = 'ZvaAF9CCIiE6cpQr';
$options = [
    PDO::MYSQL_ATTR_SSL_CA => 'cacert.pem',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];
try {
    $pdo = new PDO($dsn, $user, $password, $options);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS buku_tamu');
    echo "Database created successfully\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
