<?php
/**
 * Project: Prime Admin - Notification Processor
 * Logic: Bulk Actions & Status Updates
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../core/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ১. ইন্ডিভিজুয়াল ডিলিট লজিক
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $conn->query("DELETE FROM notifications WHERE id = $id");
        echo "success"; exit;
    }

    // ২. বাল্ক অ্যাকশন লজিক
    if (isset($_POST['bulk'])) {
        $action = $_POST['bulk'];

        if ($action === 'MARK_READ') {
            $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
        } elseif ($action === 'DELETE_ALL') {
            $conn->query("DELETE FROM notifications");
        }
        echo "success"; exit;
    }
}
?>