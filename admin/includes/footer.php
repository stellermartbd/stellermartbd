</div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // --- 1. Dark Mode Logic (Unchanged) ---
    const toggleBtn = document.getElementById('theme-toggle');
    const html = document.documentElement;
    if (localStorage.getItem('theme') === 'dark') html.classList.add('dark');

    if(toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    }

    // --- 2. Active Menu Logic (Unchanged) ---
    const currentLoc = location.href;
    const menuLinks = document.querySelectorAll('.nav-link');
    menuLinks.forEach(link => {
        if (link.href === currentLoc) {
            link.classList.add('active');
            const pageTitle = document.getElementById('page-title');
            if(pageTitle) pageTitle.innerText = link.innerText.trim();
        }
    });

    /**
     * 🔥 3. THE BEAST HEARTBEAT (Real-time Stats Engine)
     */
    function initiateBeastPulse() {
        setInterval(() => {
            $.getJSON('handlers/live-stats.php', function(data) {
                const openCount = document.getElementById('live-open-count');
                const refundCount = document.getElementById('live-refund-count');
                const queryDisplay = document.getElementById('live-query-display');

                if(openCount) openCount.innerText = data.tickets;
                if(refundCount) refundCount.innerText = data.refunds;
                if(queryDisplay) queryDisplay.innerText = data.queries.toString().padStart(2, '0');

                const cpuUsage = document.getElementById('cpu-usage');
                const cpuBar = document.getElementById('cpu-bar');
                const ramUsage = document.getElementById('ram-usage');
                const ramBar = document.getElementById('ram-bar');
                const dbLatency = document.getElementById('db-latency');

                if(cpuUsage) {
                    cpuUsage.innerText = data.cpu + '%';
                    cpuBar.style.width = data.cpu + '%';
                    if(data.cpu > 80) cpuBar.classList.replace('bg-blue-500', 'bg-rose-500');
                    else cpuBar.classList.replace('bg-rose-500', 'bg-blue-500');
                }
                if(ramUsage) {
                    ramUsage.innerText = data.ram + '%';
                    ramBar.style.width = data.ram + '%';
                }
                if(dbLatency) dbLatency.innerText = data.latency;
            }).fail(() => console.log("Pulse Lost..."));
        }, 2000);
    }

    /**
     * 🔔 4. BEAST NOTIFICATION ENGINE (Real-time Hub)
     */
    function syncNotifications() {
        $.getJSON('handlers/fetch-notifications.php', function(data) {
            // Badge & Bounce Update
            const badge = $('#notif-badge');
            if(data.count > 0) {
                badge.text(data.count).removeClass('hidden').addClass('animate-bounce');
                // Optonal: Notun alert thakle audio play
                // playBeastAlert(); 
            } else {
                badge.addClass('hidden').removeClass('animate-bounce');
            }

            // Dropdown List Update
            let html = '';
            data.alerts.forEach(alert => {
                const icon = getNotifIcon(alert.type);
                const priorityColor = alert.priority === 'HIGH' ? 'text-rose-500' : 'text-blue-500';
                html += `
                    <a href="${alert.redirect_url || '#'}" class="p-4 flex gap-4 hover:bg-white/5 border-b border-white/5 transition group">
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center shrink-0 border border-white/10 group-hover:border-blue-500/50">
                            <i class="fas ${icon} ${priorityColor} text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-white leading-tight">${alert.title}</p>
                            <p class="text-[9px] text-gray-500 mt-1 line-clamp-1">${alert.message}</p>
                        </div>
                    </a>`;
            });
            $('#notif-list').html(html || '<p class="p-10 text-center text-[10px] uppercase text-gray-600">No new alerts</p>');
        });
    }

    function getNotifIcon(type) {
        switch(type) {
            case 'ORDER': return 'fa-shopping-cart';
            case 'PRODUCT': return 'fa-box';
            case 'SYSTEM': return 'fa-microchip';
            case 'CUSTOMER': return 'fa-user';
            default: return 'fa-bolt';
        }
    }

    function playBeastAlert() {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play().catch(e => console.log("Audio Blocked"));
    }

    // --- Start Engines ---
    $(document).ready(() => {
        initiateBeastPulse();
        syncNotifications();
        setInterval(syncNotifications, 10000); // 10s sync
    });
</script>
</body>
</html>