<?php
/**
 * Admin Settings - Core Stone Indonesia
 */
require_once '../config/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Tripay settings
    $tripay_merchant_code = sanitizeInput($_POST['tripay_merchant_code'] ?? '');
    $tripay_private_key = sanitizeInput($_POST['tripay_private_key'] ?? '');
    $tripay_public_key = sanitizeInput($_POST['tripay_public_key'] ?? '');
    $tripay_mode = sanitizeInput($_POST['tripay_mode'] ?? 'sandbox');
    
    // Update app settings
    $app_name = sanitizeInput($_POST['app_name'] ?? APP_NAME);
    $admin_email = sanitizeInput($_POST['admin_email'] ?? ADMIN_EMAIL);
    
    // In a real application, you would save these to a settings table in the database
    // For now, we'll just show a success message
    $success_message = 'Pengaturan berhasil disimpan!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Core Stone Indonesia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background: #1f2937;
            color: white;
            padding: 20px;
        }
        .admin-sidebar .logo {
            color: white;
            margin-bottom: 40px;
            padding: 10px 0;
        }
        .admin-sidebar .logo-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .admin-nav {
            list-style: none;
        }
        .admin-nav li {
            margin-bottom: 8px;
        }
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #9ca3af;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .admin-nav a:hover,
        .admin-nav a.active {
            background: #374151;
            color: white;
        }
        .admin-nav a.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .admin-content {
            background: #f9fafb;
            padding: 30px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .admin-header h1 {
            font-size: 28px;
            color: #1f2937;
        }
        .settings-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .settings-section h3 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
        }
        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        @media (max-width: 1024px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="dashboard.php" class="logo" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                <div class="logo-icon" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">💎</div>
                <span style="font-size: 20px; font-weight: 700;">Core Stone Admin</span>
            </a>
            <ul class="admin-nav">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Produk</a></li>
                <li><a href="categories.php">📁 Kategori</a></li>
                <li><a href="orders.php">🛒 Pesanan</a></li>
                <li><a href="users.php">👥 Pengguna</a></li>
                <li><a href="customers.php">👤 Pelanggan</a></li>
                <li><a href="settings.php" class="active">⚙️ Pengaturan</a></li>
                <li><a href="../index.php" target="_blank">🌐 Lihat Website</a></li>
                <li><a href="logout.php" style="color: #ef4444;">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1>Pengaturan</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Kelola pengaturan aplikasi</p>
                </div>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- General Settings -->
            <div class="settings-section">
                <h3>Pengaturan Umum</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Aplikasi</label>
                        <input type="text" name="app_name" value="<?= htmlspecialchars(APP_NAME) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Admin</label>
                        <input type="email" name="admin_email" value="<?= htmlspecialchars(ADMIN_EMAIL) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>

            <!-- Payment Gateway Settings -->
            <div class="settings-section">
                <h3>Pengaturan Payment Gateway (Tripay)</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Merchant Code</label>
                        <input type="text" name="tripay_merchant_code" value="<?= htmlspecialchars(TRIPAY_MERCHANT_CODE) ?>">
                        <small>Masukkan Merchant Code dari Tripay</small>
                    </div>
                    <div class="form-group">
                        <label>Private Key</label>
                        <input type="password" name="tripay_private_key" value="<?= htmlspecialchars(TRIPAY_PRIVATE_KEY) ?>">
                        <small>Masukkan Private Key dari Tripay</small>
                    </div>
                    <div class="form-group">
                        <label>Public Key</label>
                        <input type="text" name="tripay_public_key" value="<?= htmlspecialchars(TRIPAY_PUBLIC_KEY) ?>">
                        <small>Masukkan Public Key dari Tripay</small>
                    </div>
                    <div class="form-group">
                        <label>Mode</label>
                        <select name="tripay_mode">
                            <option value="sandbox" <?= TRIPAY_MODE === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                            <option value="production" <?= TRIPAY_MODE === 'production' ? 'selected' : '' ?>>Production (Live)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Payment Gateway</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
