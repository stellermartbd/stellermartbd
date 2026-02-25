<?php
session_start();
// ডাটাবেস পাথ ফিক্স
require_once '../../core/db.php'; 

// ১. স্লাইডার অ্যাড করার লজিক
if (isset($_POST['add_slider'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $sub_title = isset($_POST['sub_title']) ? mysqli_real_escape_string($conn, $_POST['sub_title']) : '';
    $timer = (int)$_POST['timer'];
    $position = mysqli_real_escape_string($conn, $_POST['position']); // নতুন পজিশন ভ্যালু
    $link = "#"; 
    
    // অনলাইন ইমেজ লিঙ্ক (যদি ইনপুট ফিল্ড থেকে আসে)
    $image_url = isset($_POST['image_url']) ? mysqli_real_escape_string($conn, $_POST['image_url']) : '';

    $final_image = "";

    // কন্ডিশন ১: যদি অনলাইনের লিঙ্ক দেওয়া থাকে
    if (!empty($image_url)) {
        $final_image = $image_url;
    } 
    // কন্ডিশন ২: যদি কম্পিউটার থেকে ফাইল আপলোড করা হয়
    elseif (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . str_replace(' ', '-', $_FILES['image']['name']); // স্পেস রিমুভ ও ইউনিক নাম
        $upload_dir = "../../../public/uploads/sliders/";
        $folder = $upload_dir . $image_name;

        // ফোল্ডার না থাকলে তৈরি করা
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $folder)) {
            $final_image = $image_name; // শুধুমাত্র নাম সেভ হবে
        }
    }

    // ডাটাবেসে সেভ করা (position কলাম যোগ করা হয়েছে)
    if (!empty($final_image)) {
        $sql = "INSERT INTO sliders (image, title, sub_title, link, timer, position, status) 
                VALUES ('$final_image', '$title', '$sub_title', '$link', '$timer', '$position', 'Active')";
        
        if ($conn->query($sql)) {
            header("Location: ../slider.php?success=Slider added successfully!");
        } else {
            echo "Database Error: " . $conn->error;
        }
    } else {
        header("Location: ../slider.php?error=No image or link provided!");
    }
}

// ২. স্লাইডার ডিলিট করার লজিক
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $res = $conn->query("SELECT image FROM sliders WHERE id = $id");
    $data = $res->fetch_assoc();
    
    if ($data) {
        $img_path = $data['image'];
        // যদি এটি লোকাল ফাইল হয় তবেই সার্ভার থেকে ডিলিট করবে
        if (!filter_var($img_path, FILTER_VALIDATE_URL)) {
            $file_to_delete = "../../../public/uploads/sliders/" . $img_path;
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }
        
        $conn->query("DELETE FROM sliders WHERE id = $id");
        header("Location: ../slider.php?success=Slider deleted!");
    }
}
?>