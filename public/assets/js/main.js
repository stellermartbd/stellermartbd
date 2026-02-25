/**
 * Turjo Site - Main JavaScript
 * Final Fix: Auto-cart open disabled [cite: 2026-02-11]
 */

function updateCartSidebar(html, total, count) {
    const cartItems = document.getElementById('cart-items');
    const badge = document.getElementById('cart-count');
    const totalElement = document.getElementById('cart-total');
    const headerTotal = document.getElementById('cart-total-header');
    const headerCountText = document.getElementById('header-cart-count-text');

    // ১. কার্ট আইটেম লিস্ট আপডেট [cite: 2026-02-11]
    if (html && cartItems) {
        cartItems.innerHTML = html;
    }
    
    // ২. ব্যাজ আপডেট [cite: 2026-02-11]
    if (badge) {
        badge.innerText = count;
    }

    // ৩. হেডারের "X Item(s)" আপডেট [cite: 2026-02-11]
    if (headerCountText) {
        headerCountText.innerText = count + " Item(s)";
        console.log("Header text updated to: " + count);
    } else {
        console.error("Error: 'header-cart-count-text' element not found!");
    }

    // ৪. প্রাইস আপডেট (৳ ফরম্যাট) [cite: 2026-02-11]
    const formattedPrice = '৳' + new Intl.NumberFormat().format(total);
    if (totalElement) totalElement.innerText = formattedPrice;
    if (headerTotal) headerTotal.innerText = formattedPrice;
}

function addToCart(id, name, price, image) {
    let formData = new URLSearchParams();
    formData.append('action', 'add');
    formData.append('id', id);
    formData.append('qty', 1);

    fetch('core/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // রিয়েল-টাইমে UI আপডেট [cite: 2026-02-11]
            updateCartSidebar(data.cart_html, data.total_price, data.total_items);
            
            // সাকসেস নোটিফিকেশন দেখাবে [cite: 2026-02-11]
            if (typeof Swal !== 'undefined') {
                Swal.fire({ 
                    title: 'ADDED!', 
                    text: name + ' added to cart.', 
                    icon: 'success', 
                    toast: true, 
                    position: 'bottom-end', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
            }
            
            // অটোমেটিক কার্ট ওপেন সিস্টেম অফ করে দেওয়া হয়েছে [cite: 2026-02-11]
            // toggleCart(true); 
        }
    }).catch(err => console.error(err));
}

function removeFromCart(id) {
    if(!confirm('সরিয়ে ফেলতে চান?')) return;
    let formData = new URLSearchParams();
    formData.append('action', 'remove');
    formData.append('id', id);

    fetch('core/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // রিয়েল-টাইমে UI আপডেট [cite: 2026-02-11]
            updateCartSidebar(data.cart_html, data.total_price, data.total_items);
        }
    }).catch(err => console.error(err));
}

function toggleCart(forceOpen = null) {
    const sidebar = document.getElementById('cart-sidebar');
    const overlay = document.getElementById('cart-overlay');
    if (!sidebar || !overlay) return;

    if (forceOpen === true) {
        sidebar.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
    } else if (forceOpen === false) {
        sidebar.classList.add('translate-x-full');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.toggle('translate-x-full');
        overlay.classList.toggle('hidden');
    }
}