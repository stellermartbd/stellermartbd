<?php
/**
 * Prime Admin - Tactical Sidebar (Neural 7.0)
 * Project: Turjo Site | Products Hub BD
 * Logic: Dynamic RBAC Visibility & Supreme Admin Bypass
 * Design: Minimalist Toggle, Transformers Split & Smooth Neural Transitions
 */

// ১. বর্তমান পেজের নাম বের করার লজিক
$current_page = basename($_SERVER['PHP_SELF']);

// একটিভ ক্লাস চেক করার ফাংশন
function isActive($pageName, $current_page) {
    return ($pageName == $current_page) 
        ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30 active-menu scale-[1.02]' 
        : 'text-gray-400 hover:bg-white/5 hover:text-gray-200';
}
?>

<aside id="main-sidebar" class="sidebar w-64 hidden md:flex flex-col flex-shrink-0 h-full relative z-30 transition-all duration-500 ease-in-out border-r border-white/5">
    
    <div class="h-20 flex items-center justify-between px-6 mb-4 sidebar-brand shrink-0 relative transition-all duration-500 overflow-hidden">
        
        <div class="flex items-center gap-3 logo-wrapper transition-all duration-500 transform origin-left">
            <div class="logo-icon w-10 h-10 bg-rose-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-rose-600/30 shrink-0">
                <i class="fas fa-bolt text-lg"></i>
            </div>
            <div class="sidebar-text-content">
                <h1 class="font-black text-lg tracking-tight uppercase leading-none text-white">
                    Prime<br><span class="text-rose-500 text-sm">Admin</span>
                </h1>
            </div>
        </div>

        <button id="sidebar-toggle-btn" class="w-9 h-9 flex items-center justify-center bg-[#1e162e] border border-gray-400/20 rounded-xl text-gray-400 hover:text-white hover:border-rose-500/50 transition-all duration-300 shadow-lg cursor-pointer z-50">
            <i id="toggle-icon" class="fas fa-chevron-left text-[11px] transition-transform duration-500"></i>
        </button>
    </div>

    <nav class="flex-1 px-4 py-2 space-y-1 overflow-y-auto custom-scrollbar relative">
        <div id="side-particle-field" class="absolute inset-0 pointer-events-none z-[-1] overflow-hidden opacity-20"></div>

        <p class="nav-label px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 mt-2 transition-opacity duration-300">Main Navigation</p>
        <a href="dashboard.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('dashboard.php', $current_page); ?>">
            <i class="fas fa-th-large w-5 text-center text-blue-400"></i> <span class="nav-text">Dashboard</span>
        </a>

        <p class="nav-label px-4 text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-2 mt-4 transition-opacity duration-300">Beast Intelligence</p>
        <?php if (hasPermission($conn, 'order_automation.view')): ?>
        <a href="order-automation.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('order-automation.php', $current_page); ?>">
            <i class="fas fa-robot w-5 text-center text-amber-500"></i> <span class="nav-text">Order Automation</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'user_intelligence.view')): ?>
        <a href="user-intelligence.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('user-intelligence.php', $current_page); ?>">
            <i class="fas fa-brain w-5 text-center text-purple-500"></i> <span class="nav-text">User Intelligence</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'marketing_intel.view')): ?>
        <a href="marketing-intel.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('marketing-intel.php', $current_page); ?>">
            <i class="fas fa-bullseye w-5 text-center text-rose-400"></i> <span class="nav-text">Marketing Intel</span>
        </a>
        <?php endif; ?>

        <p class="nav-label px-4 text-[10px] font-bold text-purple-500 uppercase tracking-widest mb-2 mt-4 transition-opacity duration-300">Digital Warehouse</p>
        <?php if (hasPermission($conn, 'manage_keys.view')): ?>
        <a href="manage-keys.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('manage-keys.php', $current_page); ?>">
            <i class="fas fa-key w-5 text-center text-purple-400"></i> <span class="nav-text">Manage Keys</span>
        </a>
        <?php endif; ?>

        <p class="nav-label px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 mt-4 transition-opacity duration-300">Inventory & Products</p>
        <?php if (hasPermission($conn, 'category_manage.view')): ?>
        <a href="categories.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('categories.php', $current_page); ?>">
            <i class="fas fa-tags w-5 text-center text-orange-400"></i> <span class="nav-text">Category Manage</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'product_manage.view')): ?>
        <a href="products.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('products.php', $current_page); ?>">
            <i class="fas fa-box w-5 text-center text-indigo-400"></i> <span class="nav-text">Product Manage</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'bulk_productivity.view')): ?>
        <a href="bulk-tools.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('bulk-tools.php', $current_page); ?>">
            <i class="fas fa-tools w-5 text-center text-cyan-400"></i> <span class="nav-text">Bulk Productivity</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'stock_control.view')): ?>
        <a href="inventory.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('inventory.php', $current_page); ?>">
            <i class="fas fa-warehouse w-5 text-center text-emerald-400"></i> <span class="nav-text">Stock Control</span>
        </a>
        <?php endif; ?>

        <p class="nav-label px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 mt-4 transition-opacity duration-300">Orders & Customers</p>
        <?php if (hasPermission($conn, 'order_manage.view')): ?>
        <a href="orders.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('orders.php', $current_page); ?>">
            <i class="fas fa-shopping-cart w-5 text-center text-yellow-500"></i> <span class="nav-text">Order Manage</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'customers.view')): ?>
        <a href="customers.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('customers.php', $current_page); ?>">
            <i class="fas fa-users w-5 text-center text-sky-400"></i> <span class="nav-text">Customers</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'support_hub.view')): ?>
        <a href="support-hub.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('support-hub.php', $current_page); ?>">
            <i class="fas fa-headset w-5 text-center text-green-400"></i> <span class="nav-text">Support Hub</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'coupons.view')): ?>
        <a href="coupons.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('coupons.php', $current_page); ?>">
            <i class="fas fa-ticket-alt w-5 text-center text-pink-400"></i> <span class="nav-text">Coupons</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'reviews.view')): ?>
        <a href="reviews.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('reviews.php', $current_page); ?>">
            <i class="fas fa-star w-5 text-center text-yellow-300"></i> <span class="nav-text">Reviews</span>
        </a>
        <?php endif; ?>

        <p class="nav-label px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 mt-4 transition-opacity duration-300">Reports & Content</p>
        <?php if (hasPermission($conn, 'slider_manage.view')): ?>
        <a href="slider.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('slider.php', $current_page); ?>">
            <i class="fas fa-images w-5 text-center text-violet-400"></i> <span class="nav-text">Slider Manage</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'reports.view')): ?>
        <a href="reports.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('reports.php', $current_page); ?>">
            <i class="fas fa-chart-line w-5 text-center text-lime-400"></i> <span class="nav-text">Reports & Stats</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'cms_pages.view')): ?>
        <a href="cms-pages.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('cms-pages.php', $current_page); ?>">
            <i class="fas fa-file-alt w-5 text-center text-slate-400"></i> <span class="nav-text">CMS Pages</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'notifications.view')): ?>
        <a href="notifications.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('notifications.php', $current_page); ?>">
            <i class="fas fa-bell w-5 text-center text-red-400"></i> <span class="nav-text">Notifications</span>
        </a>
        <?php endif; ?>

        <p class="nav-label px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 mt-4 transition-opacity duration-300">Settings & Security</p>
        <?php if (hasPermission($conn, 'system_health.view')): ?>
        <a href="system-health.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('system-health.php', $current_page); ?>">
            <i class="fas fa-heartbeat w-5 text-center text-rose-500"></i> <span class="nav-text">System Health</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'admins.view')): ?>
        <a href="admins.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('admins.php', $current_page); ?>">
            <i class="fas fa-user-shield w-5 text-center text-teal-400"></i> <span class="nav-text">Admin Roles</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'activity_logs.view')): ?>
        <a href="activity-logs.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('activity-logs.php', $current_page); ?>">
            <i class="fas fa-history w-5 text-center text-amber-600"></i> <span class="nav-text">Activity Logs</span>
        </a>
        <?php endif; ?>

        <?php if (hasPermission($conn, 'settings.view')): ?>
        <a href="settings.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-300 <?php echo isActive('settings.php', $current_page); ?>">
            <i class="fas fa-cog w-5 text-center text-gray-300"></i> <span class="nav-text">Settings</span>
        </a>
        <?php endif; ?>

        <div class="pt-4 border-t border-white/5 mt-4">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-rose-500 hover:bg-rose-500/10 transition-all duration-300">
                <i class="fas fa-sign-out-alt w-5 text-center"></i> <span class="nav-text">Logout</span>
            </a>
        </div>
    </nav>
</aside>

<style>
    /*  */
    #main-sidebar {
        background: #0d0915 !important;
        box-shadow: 10px 0 30px rgba(0, 0, 0, 0.5);
    }

    /* 🎯 Minimized State Design Fixes */
    #main-sidebar.minimized { 
        width: 80px !important; 
    }

    /* Hide logo and texts smoothly when minimized */
    #main-sidebar.minimized .logo-wrapper,
    #main-sidebar.minimized .nav-text,
    #main-sidebar.minimized .nav-label { 
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        width: 0;
        display: none !important;
    }

    /* Header adjustments for center toggle button */
    #main-sidebar.minimized .sidebar-brand { 
        justify-content: center; 
        padding: 0;
    }

    #main-sidebar.minimized .nav-link {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    /* Arrow Icon Enhancement in Minimized Mode */
    #main-sidebar.minimized #sidebar-toggle-btn {
        background: #e11d48;
        color: white;
        border: none;
        transform: scale(1.1);
        margin: 0 auto;
    }

    /* Neural Animations */
    .floating-side-icon {
        position: absolute; color: rgba(225, 29, 72, 0.4);
        z-index: -1; animation: slideUp 10s infinite linear;
    }
    @keyframes slideUp {
        0% { transform: translateY(110%) rotate(0deg); opacity: 0; }
        50% { opacity: 0.3; }
        100% { transform: translateY(-10%) rotate(360deg); opacity: 0; }
    }

    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('main-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle-btn');
        const toggleIcon = document.getElementById('toggle-icon');

        if(toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('minimized');
                
                // Icon Swap logic with Arrow Animation
                if (sidebar.classList.contains('minimized')) {
                    toggleIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                } else {
                    toggleIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                }
            });
        }
    });
</script>