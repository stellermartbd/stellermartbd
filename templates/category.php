<?php
/**
 * KENA KATA - 3 Level Dynamic Mega Menu
 * Supports: Main -> Sub -> Sub-Sub (e.g., Fashion -> Men -> Shirt)
 */

// ১. ডাটাবেস কানেকশন (পাথ ফিক্স)
$db_file = $_SERVER['DOCUMENT_ROOT'] . '/core/db.php';
if (file_exists($db_file)) {
    require_once $db_file;
}

// ২. রিকার্সিভ ক্যাটাগরি ফেচিং ফাংশন (৩ লেভেল পর্যন্ত ডাটা আনবে)
if (!function_exists('get_nested_categories')) {
    function get_nested_categories($conn, $parent_id = 0) {
        $cats = [];
        // শুধুমাত্র Active ক্যাটাগরিগুলো আনবে, নাম অনুযায়ী সাজানো
        $sql = "SELECT * FROM categories WHERE parent_id = $parent_id AND status = 'Active' ORDER BY name ASC";
        $res = $conn->query($sql);
        
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                // প্রতিটি ক্যাটাগরির ভেতরে ঢুকে দেখবে তার চিলড্রেন আছে কি না
                $row['children'] = get_nested_categories($conn, $row['id']);
                $cats[] = $row;
            }
        }
        return $cats;
    }
}

// ৩. ডাটাবেস থেকে সব সাজানো ডাটা লোড করা
$nav_items = get_nested_categories($conn);
?>

<nav class="bg-[#083b66] border-t border-white/10 shadow-lg relative z-[9999]">
    <div class="container mx-auto px-4">
        <ul class="flex items-center text-white text-[13px] font-bold uppercase tracking-tight">
            
            <li>
                <a href="/index.php" class="block py-3 px-5 hover:bg-white/10 transition border-r border-white/10">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>

            <?php if(!empty($nav_items)): ?>
                <?php foreach ($nav_items as $item): ?>
                    
                    <li class="relative group h-full border-r border-white/10 last:border-0">
                        <a href="category.php?slug=<?php echo $item['slug']; ?>" class="flex items-center gap-2 py-3 px-5 hover:bg-white/10 transition cursor-pointer h-full">
                            <?php echo htmlspecialchars($item['name']); ?>
                            <?php if(!empty($item['children'])): ?>
                                <i class="fas fa-chevron-down text-[10px] opacity-70 group-hover:rotate-180 transition duration-300"></i>
                            <?php endif; ?>
                        </a>

                        <?php if (!empty($item['children'])): ?>
                            <div class="absolute top-full left-0 w-60 bg-white text-gray-800 shadow-2xl rounded-b-lg border-t-4 border-[#fbbf24] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2 z-[9999]">
                                <ul class="py-2">
                                    <?php foreach ($item['children'] as $child): ?>
                                        
                                        <li class="relative sub-group border-b border-gray-50 last:border-0">
                                            <a href="category.php?slug=<?php echo $child['slug']; ?>" class="flex items-center justify-between px-5 py-2.5 hover:bg-gray-100 hover:text-[#083b66] hover:pl-7 transition-all text-[12px] font-semibold w-full">
                                                <span>
                                                    <i class="fas fa-angle-right text-[10px] text-gray-400 mr-2"></i>
                                                    <?php echo htmlspecialchars($child['name']); ?>
                                                </span>
                                                <?php if(!empty($child['children'])): ?>
                                                    <i class="fas fa-caret-right text-gray-400"></i>
                                                <?php endif; ?>
                                            </a>

                                            <?php if (!empty($child['children'])): ?>
                                                <div class="sub-menu absolute left-full top-0 w-56 bg-white text-gray-800 shadow-xl rounded-lg border border-gray-100 opacity-0 invisible transition-all duration-300 transform translate-x-2 z-[10000]">
                                                    <ul class="py-2">
                                                        <?php foreach ($child['children'] as $grandchild): ?>
                                                            <li>
                                                                <a href="category.php?slug=<?php echo $grandchild['slug']; ?>" class="block px-5 py-2 hover:bg-gray-50 hover:text-[#083b66] transition text-[11px] font-medium">
                                                                    <?php echo htmlspecialchars($grandchild['name']); ?>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                            </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="px-5 py-3 text-white/50 text-xs italic">No Categories Active</li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<style>
    /* যখন ২য় লেভেলে মাউস থাকবে, ৩য় লেভেল ভেসে উঠবে */
    .sub-group:hover > .sub-menu {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
        margin-left: 2px; /* একটু গ্যাপ */
    }
</style>