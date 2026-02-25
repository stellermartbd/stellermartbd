<?php
/**
 * InfinityFree MySQL Database Connection
 * হোস্ট এবং ইউজার আপনার ড্যাশবোর্ড থেকে নেয়া হয়েছে।
 */

$host     = "sql210.infinityfree.com"; 
$user     = "if0_40950494"; 
$password = "2lKbJjGtcXVN"; // আপনার নতুন দেয়া পাসওয়ার্ড
$dbname   = "if0_40950494_turjo_website_db"; 

// কানেকশন তৈরি করা
$conn = new mysqli($host, $user, $password, $dbname);

// কানেকশন চেক করা (এরর থাকলে সেটি দেখাবে)
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// বাংলা টেক্সট বা বিশেষ ক্যারেক্টার সাপোর্ট করার জন্য
$conn->set_charset("utf8mb4");

?>