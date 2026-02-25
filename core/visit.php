<?php
// core/visit.php
require_once 'db.php';

// Visitor er IP address neya
$visitor_ip = $_SERVER['REMOTE_ADDR'];
$visit_date = date("Y-m-d");

// Check kora ajke ei IP theke keu esheche kina
$check_query = "SELECT * FROM site_stats WHERE ip_address = '$visitor_ip' AND visit_date = '$visit_date'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows == 0) {
    // Jodi notun hoy, tobe database-e insert kora
    $insert_query = "INSERT INTO site_stats (ip_address, visit_date) VALUES ('$visitor_ip', '$visit_date')";
    $conn->query($insert_query);
}
?>