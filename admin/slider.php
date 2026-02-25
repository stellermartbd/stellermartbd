<?php 
require_once '../core/db.php';
require_once __DIR__ . '/../core/functions.php';
include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 transition-all duration-300">
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-theme-dark/80 backdrop-blur-md border-b border-gray-200 dark:border-[#251d33] sticky top-0 z-20">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white uppercase">Slider Management</h2>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Control Home Banners</p>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1200px] mx-auto space-y-8">
            
            <div class="bg-white dark:bg-theme-card p-8 rounded-[2rem] border border-gray-100 dark:border-theme-border shadow-sm">
                <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-tight">Add New Slider</h3>
                <form action="handlers/slider-handler.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="add_slider" value="1">
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Slider Title (Optional)</label>
                        <input type="text" name="title" placeholder="e.g. ChatGPT Premium" class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-theme-border p-4 rounded-xl text-sm text-white outline-none focus:border-rose-500 transition">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-rose-400 uppercase ml-2 uppercase tracking-widest">Display Position</label>
                        <select name="position" class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-theme-border p-4 rounded-xl text-sm text-white outline-none focus:border-rose-500 transition cursor-pointer">
                            <option value="Main" class="bg-theme-dark">Main Slider (Left Large)</option>
                            <option value="Right_Up" class="bg-theme-dark">Right Banner (Top Small)</option>
                            <option value="Right_Down" class="bg-theme-dark">Right Banner (Bottom Small)</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Interval Time (ms)</label>
                        <input type="number" name="timer" value="4000" class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-theme-border p-4 rounded-xl text-sm text-white outline-none focus:border-rose-500 transition">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-bold text-rose-400 uppercase ml-2">Online Image URL (Direct Link)</label>
                        <input type="text" name="image_url" placeholder="https://example.com/banner.jpg" class="w-full bg-gray-50 dark:bg-white/5 border border-rose-500/20 dark:border-rose-500/20 p-4 rounded-xl text-sm text-white outline-none focus:border-rose-500 transition shadow-inner">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">OR Upload Local Image</label>
                        <input type="file" name="image" class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-theme-border p-3 rounded-xl text-sm text-gray-400 outline-none">
                    </div>

                    <button type="submit" class="md:col-span-2 bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest transition shadow-lg shadow-rose-600/20 active:scale-95">
                        Add Slider Now
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php 
                $res = $conn->query("SELECT * FROM sliders ORDER BY id DESC");
                while($row = $res->fetch_assoc()):
                    // পজিশন অনুযায়ী কালার সেট করা
                    $pos_color = 'bg-gray-500';
                    if($row['position'] == 'Main') $pos_color = 'bg-blue-600';
                    if($row['position'] == 'Right_Up') $pos_color = 'bg-emerald-600';
                    if($row['position'] == 'Right_Down') $pos_color = 'bg-orange-600';
                ?>
                <div class="relative group rounded-[2rem] overflow-hidden border border-white/5 bg-theme-card shadow-xl h-48">
                    <?php 
                    $img_src = $row['image'];
                    if (!filter_var($img_src, FILTER_VALIDATE_URL)) {
                        $img_src = "../../public/uploads/sliders/" . $img_src;
                    }
                    ?>
                    <img src="<?php echo $img_src; ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition duration-500" onerror="this.src='https://placehold.co/600x300/1e162e/rose500?text=Broken+Link'">
                    
                    <div class="absolute inset-0 p-6 flex flex-col justify-between bg-gradient-to-t from-black/80 via-transparent to-transparent">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-2">
                                <span class="bg-rose-500 text-[8px] font-black px-2 py-1 rounded w-fit uppercase text-white shadow-lg"><?php echo $row['timer']; ?>ms</span>
                                <span class="<?= $pos_color ?> text-[8px] font-black px-2 py-1 rounded w-fit uppercase text-white shadow-lg">Pos: <?php echo $row['position']; ?></span>
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="handlers/slider-handler.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this slider?')" class="w-10 h-10 bg-rose-600 text-white flex items-center justify-center rounded-xl hover:bg-rose-700 transition shadow-xl">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-white font-bold uppercase italic truncate pr-10"><?php echo !empty($row['title']) ? $row['title'] : 'Banner'; ?></h4>
                            <p class="text-gray-400 text-[8px] uppercase tracking-widest">Position: <?php echo $row['position']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>