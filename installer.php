<?php
/**
 * Core Stone Indonesia - Installer Script
 * Run this file to setup database and initial admin account
 */

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;
$error = '';
$db_created = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'core_stone_db';
    
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_name = $_POST['admin_name'] ?? 'Admin';
    
    try {
        // Connect without database
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        $db_created = true;
        
        // Create tables
        $sql = "
        -- Users table
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- Categories table
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- Products table
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(200) UNIQUE NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock INT DEFAULT 0,
            image VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- Orders table
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            tripay_reference VARCHAR(100),
            shipping_address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- Order items table
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- Settings table
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sql);
        
        // Insert default admin user
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$admin_name, $admin_email, $hashed_password]);
        
        // Insert default categories
        $categories = [
            ['Batu Akik Merah', 'batu-akik-merah', 'Koleksi batu akik berwarna merah'],
            ['Batu Akik Hijau', 'batu-akik-hijau', 'Koleksi batu akik berwarna hijau'],
            ['Batu Akik Biru', 'batu-akik-biru', 'Koleksi batu akik berwarna biru'],
            ['Batu Akik Hitam', 'batu-akik-hitam', 'Koleksi batu akik berwarna hitam'],
            ['Batu Akik Putih', 'batu-akik-putih', 'Koleksi batu akik berwarna putih'],
            ['Batu Mulia', 'batu-mulia', 'Koleksi batu mulia premium']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
        
        // Insert default settings
        $settings = [
            ['site_name', 'Core Stone Indonesia'],
            ['site_description', 'Marketplace Batu Akik Terpercaya di Indonesia'],
            ['whatsapp_number', '6281214932916'],
            ['tripay_merchant_code', ''],
            ['tripay_private_key', ''],
            ['tripay_public_key', '']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        
        // Create config file
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '$db_host');\n";
        $config_content .= "define('DB_USER', '$db_user');\n";
        $config_content .= "define('DB_PASS', '$db_pass');\n";
        $config_content .= "define('DB_NAME', '$db_name');\n";
        $config_content .= "define('APP_NAME', 'Core Stone Indonesia');\n";
        $config_content .= "define('APP_URL', 'http://' . \$_SERVER['HTTP_HOST'] . '/core-stone');\n";
        $config_content .= "define('ADMIN_EMAIL', '$admin_email');\n";
        $config_content .= "define('TRIPAY_MERCHANT_CODE', '');\n";
        $config_content .= "define('TRIPAY_PRIVATE_KEY', '');\n";
        $config_content .= "define('TRIPAY_PUBLIC_KEY', '');\n";
        $config_content .= "define('TRIPAY_MODE', 'sandbox');\n";
        $config_content .= "\n";
        $config_content .= "ini_set('session.cookie_httponly', 1);\n";
        $config_content .= "session_start();\n";
        $config_content .= "\n";
        $config_content .= "try {\n";
        $config_content .= "    \$pdo = new PDO(\n";
        $config_content .= "        \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\",\n";
        $config_content .= "        DB_USER,\n";
        $config_content .= "        DB_PASS,\n";
        $config_content .= "        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]\n";
        $config_content .= "    );\n";
        $config_content .= "} catch (PDOException \$e) {\n";
        $config_content .= "    die(\"Database connection failed: \" . \$e->getMessage());\n";
        $config_content .= "}\n";
        $config_content .= "\n";
        $config_content .= "function redirect(\$url) { header(\"Location: \" . \$url); exit(); }\n";
        $config_content .= "function isLoggedIn() { return isset(\$_SESSION['user_id']); }\n";
        $config_content .= "function isAdmin() { return isset(\$_SESSION['user_role']) && \$_SESSION['user_role'] === 'admin'; }\n";
        $config_content .= "function formatRupiah(\$amount) { return 'Rp ' . number_format(\$amount, 0, ',', '.'); }\n";
        $config_content .= "function sanitizeInput(\$data) { return htmlspecialchars(strip_tags(trim(\$data))); }\n";
        $config_content .= "?>";
        
        file_put_contents(__DIR__ . '/config/config.php', $config_content);
        
        $success = true;
        
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Core Stone Indonesia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 600;
            font-size: 14px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-install {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }
        .section-title {
            color: #2d3748;
            font-size: 18px;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .info-box {
            background: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box p {
            color: #2c5282;
            font-size: 13px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php if ($success): ?>
        <div class="installer-box">
            <div class="alert alert-success">
                <h2 style="margin-bottom: 10px;">🎉 Instalasi Berhasil!</h2>
                <p>Database dan tabel telah dibuat dengan sukses.</p>
                <p style="margin-top: 10px;"><strong>Admin Email:</strong> <?= htmlspecialchars($admin_email) ?></p>
                <p><strong>Password:</strong> Gunakan password yang Anda masukkan</p>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #9ae6b4;">
                    <p style="font-weight: 600; margin-bottom: 10px;">Langkah Selanjutnya:</p>
                    <ol style="margin-left: 20px; line-height: 2;">
                        <li>Hapus file <code>installer.php</code> untuk keamanan</li>
                        <li>Login ke panel admin di <code>/admin/</code></li>
                        <li>Konfigurasi Tripay di pengaturan</li>
                        <li>Tambahkan produk batu akik Anda</li>
                    </ol>
                </div>
                <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 12px 30px; background: #48bb78; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Ke Halaman Utama</a>
            </div>
        </div>
    <?php else: ?>
        <div class="installer-box">
            <h1>🔧 Core Stone Indonesia</h1>
            <p class="subtitle">Installer Marketplace Batu Akik</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($db_created): ?>
                <div class="alert alert-success">
                    ✅ Database berhasil dibuat! Silakan lengkapi data admin.
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <p><strong>Penting:</strong> Pastikan MySQL/MariaDB sudah berjalan. Untuk production, gunakan credentials database yang aman.</p>
            </div>
            
            <form method="POST">
                <h3 class="section-title">📊 Database Configuration</h3>
                
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label>Database Username</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                
                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_pass" placeholder="Leave empty if no password">
                </div>
                
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" value="core_stone_db" required>
                </div>
                
                <h3 class="section-title">👤 Admin Account</h3>
                
                <div class="form-group">
                    <label>Admin Name</label>
                    <input type="text" name="admin_name" value="Admin" required>
                </div>
                
                <div class="form-group">
                    <label>Admin Email</label>
                    <input type="email" name="admin_email" required>
                </div>
                
                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="password" name="admin_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn-install">
                    🚀 Install Sekarang
                </button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
