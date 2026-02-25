<?php
// ১. মেইন স্লাইডারের জন্য ডাটা (Left Large Slider)
$main_slider_res = $conn->query("SELECT * FROM sliders WHERE status = 'Active' AND position = 'Main' ORDER BY id DESC");

// ২. ডান পাশের উপরের ব্যানারের জন্য ডাটা (Right_Up)
$right_up_res = $conn->query("SELECT * FROM sliders WHERE status = 'Active' AND position = 'Right_Up' ORDER BY id DESC LIMIT 1");
$right_up = $right_up_res->fetch_assoc();

// ৩. ডান পাশের নিচের ব্যানারের জন্য ডাটা (Right_Down)
$right_down_res = $conn->query("SELECT * FROM sliders WHERE status = 'Active' AND position = 'Right_Down' ORDER BY id DESC LIMIT 1");
$right_down = $right_down_res->fetch_assoc();

// ৪. স্লাইডারের টাইমিং
$timer_query = $conn->query("SELECT timer FROM sliders WHERE status = 'Active' LIMIT 1");
$timer_row = $timer_query->fetch_assoc();
$display_timer = $timer_row['timer'] ?? 4000; 
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    /* মেইন কন্টেইনার */
    .hero-layout {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 20px;
    }

    /* ডেসকটপ লেআউট */
    @media (min-width: 992px) {
        .hero-layout {
            flex-direction: row;
            height: 450px; 
        }
    }

    /* বাম পাশের স্লাইডার - কর্নার শার্প করার জন্য border-radius: 0 */
    .left-slider-wrapper {
        flex: 2;
        width: 100%;
        height: 300px;
        border-radius: 0px !important; 
        overflow: hidden;
    }

    @media (min-width: 992px) {
        .left-slider-wrapper {
            height: 100%;
        }
    }

    .main-swiper {
        width: 100%;
        height: 100%;
    }

    /* ডান পাশের ব্যানার কলাম */
    .right-banners {
        flex: 1; 
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    @media (min-width: 992px) {
        .right-banners {
            height: 100%;
        }
    }

    /* ব্যানারের আইটেম - কর্নার শার্প করার জন্য border-radius: 0 */
    .side-banner-item {
        flex: 1;
        position: relative;
        overflow: hidden;
        border-radius: 0px !important; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .slider-img, .side-banner-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
        border-radius: 0px !important;
    }

    .side-banner-item:hover .side-banner-img {
        transform: scale(1.05);
    }

    /* Swiper নেভিগেশন বাটন */
    .swiper-button-next, .swiper-button-prev {
        background: rgba(255, 255, 255, 0.8);
        width: 35px !important;
        height: 35px !important;
        border-radius: 50%; 
        color: #333 !important;
    }
    
    .swiper-button-next:after, .swiper-button-prev:after { font-size: 14px !important; }

    /* ইমেজ নাম্বার বা Pagination হাইড করা হয়েছে */
    .swiper-pagination {
        display: none !important;
    }
</style>

<div class="container mx-auto px-4">
    <div class="hero-layout">
        
        <div class="left-slider-wrapper">
            <div class="swiper main-swiper">
                <div class="swiper-wrapper">
                    <?php if($main_slider_res && $main_slider_res->num_rows > 0): ?>
                        <?php while($row = $main_slider_res->fetch_assoc()): ?>
                            <div class="swiper-slide">
                                <?php 
                                    $img = $row['image'];
                                    $src = (filter_var($img, FILTER_VALIDATE_URL)) ? $img : "public/uploads/sliders/" . $img;
                                ?>
                                <img src="<?= $src ?>" class="slider-img" alt="Slider Image">
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="swiper-slide">
                            <img src="https://placehold.co/800x450?text=No+Slider+Image" class="slider-img">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <div class="right-banners">
            <div class="side-banner-item">
                <?php 
                    if($right_up) {
                        $up_src = (filter_var($right_up['image'], FILTER_VALIDATE_URL)) ? $right_up['image'] : "public/uploads/sliders/" . $right_up['image'];
                        echo '<img src="'.$up_src.'" class="side-banner-img" alt="Top Banner">';
                    } else {
                        echo '<img src="https://placehold.co/500x215?text=Banner+1" class="side-banner-img">';
                    }
                ?>
            </div>

            <div class="side-banner-item">
                <?php 
                    if($right_down) {
                        $down_src = (filter_var($right_down['image'], FILTER_VALIDATE_URL)) ? $right_down['image'] : "public/uploads/sliders/" . $right_down['image'];
                        echo '<img src="'.$down_src.'" class="side-banner-img" alt="Bottom Banner">';
                    } else {
                        echo '<img src="https://placehold.co/500x215?text=Banner+2" class="side-banner-img">';
                    }
                ?>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper(".main-swiper", {
            loop: true,
            speed: 800,
            autoplay: { 
                delay: <?= (int)$display_timer ?>, 
                disableOnInteraction: false 
            },
            // Pagination (Image Number) সেকশনটি এখান থেকে রিমুভ করা হয়েছে
            navigation: { 
                nextEl: ".swiper-button-next", 
                prevEl: ".swiper-button-prev" 
            },
        });
    });
</script>