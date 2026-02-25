<?php
require_once '../../core/db.php'; 

if (isset($_POST['update_page'])) {
    $page_id = (int)$_POST['page_id'];
    $content = mysqli_real_escape_string($conn, $_POST['page_content']);

    $sql = "UPDATE cms_pages SET page_content = '$content' WHERE id = $page_id";

    if ($conn->query($sql)) {
        header("Location: ../cms-pages.php?success=Page updated successfully!");
    } else {
        header("Location: ../cms-pages.php?error=Update failed!");
    }
}
?>