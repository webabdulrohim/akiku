<?php
/**
 * Core Stone Indonesia - Categories Page
 * Daftar semua kategori
 */
require_once 'config/config.php';

$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                           GROUP BY c.id 
                           ORDER BY c.created_at DESC")->fetchAll();

$pageTitle = 'Kategori Produk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Core Stone Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <span>📍 Jakarta, Indonesia</span>
                <span>📞 0812-1493-2916 | ✉️ info@corestone.id</span>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <a href="index.php" class="logo">
                    <div class="logo-icon">💎</div>
                    <span>Core Stone Indonesia</span>
                </a>
                <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="products.php">Produk</a></li>
                    <li><a href="categories.php" class="active">Kategori</a></li>
                    <li><a href="about.php">Tentang</a></li>
                    <li><a href="contact.php">Kontak</a></li>
                </ul>
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="btn btn-outline">🛒 Keranjang (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
                        <a href="account.php" class="btn btn-primary">Akun Saya</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1><?= $pageTitle ?></h1>
            <p>Jelajahi berbagai kategori batu akik premium kami</p>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="category-grid-large">
                <?php foreach ($categories as $category): ?>
                <a href="category.php?slug=<?= $category['slug'] ?>" class="category-card-large">
                    <div class="category-icon-large">💎</div>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                    <span class="product-count"><?= $category['product_count'] ?> Produk</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <div class="logo-icon">💎</div>
                        <span>Core Stone Indonesia</span>
                    </div>
                    <p>Marketplace batu akik terpercaya di Indonesia.</p>
                </div>
                <div class="footer-links">
                    <h4>Navigasi</h4>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="products.php">Produk</a></li>
                        <li><a href="categories.php">Kategori</a></li>
                        <li><a href="about.php">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Akun</h4>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Daftar</a></li>
                        <li><a href="cart.php">Keranjang</a></li>
                        <li><a href="orders.php">Pesanan Saya</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Kontak</h4>
                    <ul>
                        <li>📞 0812-1493-2916</li>
                        <li>✉️ info@corestone.id</li>
                        <li>📍 Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Core Stone Indonesia. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/6281214932916?text=Halo%20saya%20butuh%20bantuan%20memilih%20kategori" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>
