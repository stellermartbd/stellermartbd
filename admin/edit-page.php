<?php 
require_once '../core/db.php'; 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

$id = (int)$_GET['id'];
$page = $conn->query("SELECT * FROM cms_pages WHERE id = $id")->fetch_assoc();
?>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 transition-all duration-300">
    <div class="flex-1 overflow-y-auto p-8">
        <form action="handlers/cms-handler.php" method="POST" class="max-w-[1000px] mx-auto space-y-6">
            <input type="hidden" name="page_id" value="<?php echo $page['id']; ?>">
            
            <div class="flex items-center justify-between bg-white dark:bg-theme-card p-6 rounded-3xl border border-theme-border shadow-sm">
                <div>
                    <h2 class="text-xl font-bold text-white uppercase italic">Editing: <?php echo $page['page_name']; ?></h2>
                </div>
                <button type="submit" name="update_page" class="px-8 py-3 bg-rose-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:scale-105 transition active:scale-95 shadow-lg shadow-rose-600/20">
                    Save Changes
                </button>
            </div>

            <div class="bg-white dark:bg-theme-card p-8 rounded-[2rem] border border-theme-border shadow-sm">
                <textarea id="page_content" name="page_content" class="min-h-[500px]">
                    <?php echo $page['page_content']; ?>
                </textarea>
            </div>
        </form>
    </div>
</main>

<script>
    tinymce.init({
        selector: '#page_content',
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
        toolbar_mode: 'floating',
        skin: 'oxide-dark',
        content_css: 'dark',
        height: 600,
        border: 0
    });
</script>

<?php include 'includes/footer.php'; ?>