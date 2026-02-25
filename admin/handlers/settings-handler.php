<?php
session_start();
// handlers ফোল্ডার থেকে ২ ধাপ পেছনে গিয়ে db.php পাবে
require_once __DIR__ . '/../../core/db.php'; 

if (isset($_POST['save_settings'])) {
    $platform_name = $_POST['platform_name'];
    $support_email = $_POST['support_email'];
    $contact_phone = $_POST['contact_phone'];
    $currency_unit = $_POST['currency_unit'];

    // লোগো আপলোড লজিক
    $logo_name = "";
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $logo_name = time() . '_' . $_FILES['logo']['name'];
        // নিশ্চিত করুন public/uploads ফোল্ডারটি আছে
        move_uploaded_file($_FILES['logo']['tmp_name'], '../../public/uploads/' . $logo_name);
    }

    // টেবিল আপডেট করা (ID 1 ফিক্সড রাখা হয়েছে)
    $sql = "UPDATE settings SET platform_name=?, support_email=?, contact_phone=?, currency_unit=?";
    if ($logo_name != "") {
        $sql .= ", logo='$logo_name'";
    }
    $sql .= " WHERE id=1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $platform_name, $support_email, $contact_phone, $currency_unit);

    if ($stmt->execute()) {
        header("Location: ../settings.php?success=1");
    } else {
        header("Location: ../settings.php?error=" . $conn->error);
    }
}