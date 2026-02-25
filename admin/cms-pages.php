<?php
/**
 * Advanced CMS Pages Management
 * Project: Turjo Site | Performance Optimized
 */

// ১. কোর ফাইল ও সিকিউরিটি কনফিগারেশন
require_once __DIR__ . '/../core/db.php'; 
require_once __DIR__ . '/../core/functions.php'; 
require_once __DIR__ . '/../core/csrf.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// এরর কন্ট্রোল
error_reporting(E_ALL);
ini_set('display_errors', 1);

// লগইন ভ্যালিডেশন
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: login.php'); 
    exit; 
}

// ২. ডাটা ফেচিং লজিক (With Search Filter)
$search = $_GET['search'] ?? '';
$pages = [];

try {
    $sql = "SELECT * FROM pages";
    if (!empty($search)) {
        $search_safe = $conn->real_escape_string($search);
        $sql .= " WHERE title LIKE '%$search_safe%' OR slug LIKE '%$search_safe%'";
    }
    $sql .= " ORDER BY id DESC";
    
    $result = $conn->query($sql);
    if ($result) {
        while($row = $result->fetch_assoc()) { $pages[] = $row; }
    }
} catch (Exception $e) {
    // Silent fail if table doesn't exist
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 font-sans">
    
    <header class="h-24 flex items-center justify-between px-10 bg-white/80 dark:bg-theme-card/80 backdrop-blur-2xl border-b dark:border-theme-border sticky top-0 z-40 shrink-0">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight uppercase">CMS Engine</h2>
            </div>
            <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.3em] opacity-70">Architecture by **Turjo Site**</p>
        </div>

        <div class="flex items-center gap-6">
            <form action="" method="GET" class="relative hidden md:block">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search pages..." class="bg-gray-100 dark:bg-theme-dark border-none rounded-2xl py-3 pl-12 pr-6 text-xs font-bold dark:text-white focus:ring-2 focus:ring-rose-500/30 transition-all w-64">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </form>
            
            <a href="add-page.php" class="group bg-rose-600 hover:bg-rose-700 text-white px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-2xl shadow-rose-500/30 flex items-center gap-3 active:scale-95">
                <i class="fas fa-plus transition-transform group-hover:rotate-90"></i>
                New Page
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-10 custom-scrollbar">
        <div class="max-w-[1400px] mx-auto">
            
            <?php if(isset($_GET['success'])): ?>
                <div class="mb-8 p-5 bg-green-500/10 border border-green-500/20 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4">
                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white"><i class="fas fa-check"></i></div>
                    <p class="text-xs font-black text-green-600 uppercase tracking-widest">Action completed successfully!</p>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-theme-card rounded-[3rem] border dark:border-theme-border shadow-[0_20px_50px_rgba(0,0,0,0.02)] dark:shadow-none overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-theme-dark/50">
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Page Details</th>
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Link Visibility</th>
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">System Health</th>
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-theme-border">
                            <?php if (!empty($pages)): ?>
                                <?php foreach($pages as $page): ?>
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-theme-dark/40 transition-all group">
                                    <td class="px-10 py-7">
                                        <div class="flex items-center gap-5">
                                            <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-rose-500/10 to-rose-600/5 flex items-center justify-center text-rose-500 shadow-inner group-hover:scale-110 transition-all duration-500">
                                                <i class="fas fa-file-invoice text-lg"></i>
                                            </div>
                                            <div>
                                                <span class="block font-black text-base dark:text-white tracking-tight mb-1"><?= htmlspecialchars($page['title']); ?></span>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter italic">Rev: 2.1</span>
                                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Modified: <?= date('M d, Y', strtotime($page['updated_at'] ?? 'now')); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-7">
                                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-theme-dark rounded-2xl border dark:border-theme-border shadow-sm group-hover:border-rose-500/30 transition-all">
                                            <span class="text-rose-500 text-[10px] font-black uppercase">turjo.site/</span>
                                            <span class="text-gray-600 dark:text-gray-300 text-[10px] font-bold"><?= htmlspecialchars($page['slug']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-7">
                                        <div class="flex items-center gap-2 text-green-500">
                                            <div class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                                            <span class="text-[10px] font-black uppercase tracking-widest">Publicly Live</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-7 text-right">
                                        <div class="flex justify-end gap-3 translate-x-4 group-hover:translate-x-0 transition-transform duration-500 opacity-0 group-hover:opacity-100">
                                            <a href="edit-page.php?id=<?= $page['id']; ?>" class="w-11 h-11 flex items-center justify-center bg-gray-100 dark:bg-theme-dark text-gray-400 hover:text-white hover:bg-rose-600 rounded-2xl transition-all" title="Edit Content">
                                                <i class="fas fa-pen-nib text-xs"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?= $page['id']; ?>)" class="w-11 h-11 flex items-center justify-center bg-gray-100 dark:bg-theme-dark text-gray-400 hover:text-white hover:bg-rose-600 rounded-2xl transition-all" title="Delete Permanent">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-10 py-32 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-4">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-rose-500/20 blur-3xl rounded-full"></div>
                                                <i class="fas fa-folder-open text-6xl text-gray-200 dark:text-theme-border relative z-10"></i>
                                            </div>
                                            <h3 class="text-lg font-black text-gray-400 uppercase tracking-[0.3em]">No Assets Found</h3>
                                            <p class="text-xs text-gray-500 max-w-xs mx-auto italic">Start building your content infrastructure by creating your first page above.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
/**
 * CSRF Protected Delete Request
 */
function confirmDelete(id) {
    if (confirm('CRITICAL ACTION: Are you sure you want to permanently delete this page?')) {
        // Professional redirect to delete handler
        window.location.href = 'delete-page.php?id=' + id + '&csrf=<?= $_SESSION['csrf_token'] ?? '' ?>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>