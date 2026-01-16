<?php

function layout($title, $content) {
    return "
    <!doctype html>
    <html>
    <head>
        <title>$title</title>

        <!-- Bootstrap -->
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>

        <!-- Animate CSS (UNTUK SEMUA HALAMAN) -->
        <link rel='stylesheet'
              href='https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'>

        <!-- Style Tambahan -->
        <style>
            .hover-scale {
                transition: transform .3s ease, box-shadow .3s ease;
            }
            .hover-scale:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(0,0,0,.15);
            }
        </style>
    </head>

    <body class='bg-light'>

        <nav class='navbar navbar-dark bg-dark mb-4'>
            <div class='container'>
                <a class='navbar-brand fw-bold' href='/slim-order/public'>
                    Slim Order
                </a>
            </div>
        </nav>

        <div class='container'>
            $content
        </div>

    </body>
    </html>
    ";
}


$db = require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

/* HALAMAN AWAL */
$app->get('/', function ($req, $res) {

    return $res->write(
        layout("Slim Order", "
            <div class='text-center py-5'>
                <h1 class='display-4 fw-bold mb-3'>
                    🛒 Slim Order
                </h1>

                <p class='lead text-muted mb-4'>
                    Aplikasi pemesanan sederhana, cepat, dan modern
                </p>

                <a href='/slim-order/public/login'
                   class='btn btn-primary btn-lg px-5'>
                   🔐 Login Sekarang
                </a>
            </div>

            <div class='row mt-5 text-center'>
                <div class='col-md-4'>
                    <div class='card shadow-sm border-0'>
                        <div class='card-body'>
                            <h1>⚡</h1>
                            <h5>Cepat</h5>
                            <p class='text-muted'>
                                Proses order tanpa ribet
                            </p>
                        </div>
                    </div>
                </div>

                <div class='col-md-4'>
                    <div class='card shadow-sm border-0'>
                        <div class='card-body'>
                            <h1>💳</h1>
                            <h5>Mudah</h5>
                            <p class='text-muted'>
                                Pembayaran simulasi sederhana
                            </p>
                        </div>
                    </div>
                </div>

                <div class='col-md-4'>
                    <div class='card shadow-sm border-0'>
                        <div class='card-body'>
                            <h1>📦</h1>
                            <h5>Rapi</h5>
                            <p class='text-muted'>
                                Manajemen order yang jelas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        ")
    );
});

/* HALAMAN LOGIN */
$app->get('/login', function ($req, $res) {

    return $res->write(
        layout("Login", "
            <div class='row justify-content-center align-items-center' style='min-height:80vh'>
                <div class='col-md-4'>
                    <div class='card shadow-lg border-0'>
                        <div class='card-header text-center bg-primary text-white'>
                            <h4 class='mb-0'>🔐 Login</h4>
                        </div>

                        <div class='card-body p-4'>
                            <form method='POST' action='/slim-order/public/login'>

                                <div class='mb-3'>
                                    <label class='form-label'>Email</label>
                                    <input type='email'
                                           name='email'
                                           class='form-control form-control-lg'
                                           placeholder='masukkan email'
                                           required>
                                </div>

                                <div class='mb-4'>
                                    <label class='form-label'>Password</label>
                                    <input type='password'
                                           name='password'
                                           class='form-control form-control-lg'
                                           placeholder='masukkan password'
                                           required>
                                </div>

                                <button class='btn btn-primary btn-lg w-100'>
                                    Masuk
                                </button>
                            </form>
                        </div>

                        <div class='card-footer text-center bg-light'>
                            <small class='text-muted'>
                                Slim Order &copy; 2026
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        ")
    );
});

/* PROSES LOGIN */
$app->post('/login', function ($req, $res) use ($db) {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $user = $db->get("users", "*", [
        "email" => $email
    ]);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        return $res->withRedirect("/slim-order/public/dashboard");
    }

    return $res->write("Login gagal");
});

/* DASHBOARD */
$app->get('/dashboard', function ($req, $res) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    // TOTAL ORDER
    $totalOrder = $db->count("orders", [
        "user_id" => $_SESSION["user"]["id"]
    ]);

    // TOTAL PAID
    $totalPaid = $db->count("orders", [
        "user_id" => $_SESSION["user"]["id"],
        "status" => "paid"
    ]);

    // TOTAL PENDING
    $totalPending = $db->count("orders", [
        "user_id" => $_SESSION["user"]["id"],
        "status" => "pending"
    ]);

    // TOTAL PENDAPATAN
    $totalRevenue = $db->sum("orders", "total", [
        "user_id" => $_SESSION["user"]["id"],
        "status" => "paid"
    ]);

    return $res->write(
        layout("Dashboard", "

        <!-- WELCOME -->
        <div class='row mb-5 justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow-lg border-0 text-center'>
                    <div class='card-body py-5'>

                        <h2 class='fw-bold mb-3'>
                            👋 Selamat Datang
                        </h2>

                        <p class='fs-5 mb-0'>
                            Halo <b class='text-primary'>{$_SESSION['user']['email']}</b>,  
                            <br>
                            Selamat Berbelanja & Transaksi Terpercaya 🚀
                        </p>

                    </div>
                </div>
            </div>
        </div>

        
        <!-- STATISTIK -->
        <div class='row mb-4 text-center'>

            <div class='col-md-3 mb-3'>
                <div class='card border-0 shadow-lg'>
                    <div class='card-body'>
                        <h1 class='fw-bold'>{$totalOrder}</h1>
                        <p class='text-muted mb-0'>Total Order</p>
                    </div>
                </div>
            </div>

            <div class='col-md-3 mb-3'>
                <div class='card border-0 shadow-lg'>
                    <div class='card-body'>
                        <h1 class='fw-bold text-success'>{$totalPaid}</h1>
                        <p class='text-muted mb-0'>Order Paid</p>
                    </div>
                </div>
            </div>

            <div class='col-md-3 mb-3'>
                <div class='card border-0 shadow-lg'>
                    <div class='card-body'>
                        <h1 class='fw-bold text-warning'>{$totalPending}</h1>
                        <p class='text-muted mb-0'>Order Pending</p>
                    </div>
                </div>
            </div>

            <div class='col-md-3 mb-3'>
                <div class='card border-0 shadow-lg'>
                    <div class='card-body'>
                        <h4 class='fw-bold text-primary'>
                            Rp {$totalRevenue}
                        </h4>
                        <p class='text-muted mb-0'>Total Pembayaran</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- MENU -->
        <div class='row'>

            <div class='col-md-4'>
                <div class='card text-center shadow-sm mb-3'>
                    <div class='card-body'>
                        <h1>🛒</h1>
                        <h5>Produk</h5>
                        <a href='/slim-order/public/products'
                           class='btn btn-primary w-100'>
                           Lihat Produk
                        </a>
                    </div>
                </div>
            </div>

            <div class='col-md-4'>
                <div class='card text-center shadow-sm mb-3'>
                    <div class='card-body'>
                        <h1>📦</h1>
                        <h5>Order Saya</h5>
                        <a href='/slim-order/public/orders'
                           class='btn btn-success w-100'>
                           Lihat Order
                        </a>
                    </div>
                </div>
            </div>

            <div class='col-md-4'>
                <div class='card text-center shadow-sm mb-3 border-danger'>
                    <div class='card-body'>
                        <h1>🚪</h1>
                        <h5>Logout</h5>
                        <a href='/slim-order/public/logout'
                           class='btn btn-danger w-100'>
                           Keluar
                        </a>
                    </div>
                </div>
            </div>

        </div>
        ")
    );
});


/* LOGOUT - KONFIRMASI */
$app->get('/logout', function ($req, $res) {

    return $res->write(
        layout("Konfirmasi Logout", "
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card border-0 shadow-lg text-center animate__animated animate__fadeInDown'>
                        <div class='card-body p-5'>
                            <h1 class='mb-3'>⚠️</h1>
                            <h3 class='fw-bold mb-3'>Konfirmasi Logout</h3>
                            <p class='text-muted mb-4'>
                                Apakah kamu yakin ingin keluar dari aplikasi?
                            </p>

                            <div class='d-flex gap-3 justify-content-center'>
                                <a href='/slim-order/public/dashboard'
                                   class='btn btn-outline-secondary btn-lg'>
                                   ❌ Batal
                                </a>

                                <a href='/slim-order/public/logout-confirm'
                                   class='btn btn-danger btn-lg'>
                                   ✅ Ya, Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ")
    );
});

/* LOGOUT CONFIRM */
$app->get('/logout-confirm', function ($req, $res) {
    session_destroy();

    return $res->write(
        layout("Logout Berhasil", "
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card border-0 shadow-lg text-center animate__animated animate__fadeInUp'>
                        <div class='card-body p-5'>
                            <h1 class='mb-3'>👋</h1>
                            <h3 class='fw-bold mb-3'>Kamu Berhasil Logout</h3>
                            <p class='text-muted mb-4'>
                                Terima kasih sudah menggunakan aplikasi ini
                            </p>

                            <a href='/slim-order/public/login'
                               class='btn btn-primary btn-lg'>
                               🔐 Login Lagi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        ")
    );
});


/* HALAMAN PRODUK */
$app->get('/products', function ($req, $res) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    $products = $db->select("products", "*");

    $html = "
        <div class='d-flex justify-content-between align-items-center mb-4'>
            <h3>🛍️ Daftar Produk</h3>
            <a href='/slim-order/public/dashboard' class='btn btn-outline-secondary'>
                ← Dashboard
            </a>
        </div>

        <div class='row'>
    ";

    foreach ($products as $p) {
        $html .= "
            <div class='col-md-4'>
                <div class='card h-100 shadow-sm border-0 mb-4'>
                    <div class='card-body d-flex flex-column'>
                        <h5 class='card-title'>{$p['name']}</h5>
                        <p class='card-text text-muted mb-3'>
                            Harga: <b class='text-dark'>Rp {$p['price']}</b>
                        </p>
                        <a href='/slim-order/public/order/{$p['id']}'
                           class='btn btn-primary mt-auto'>
                           🛒 Beli Sekarang
                        </a>
                    </div>
                </div>
            </div>
        ";
    }

    $html .= "</div>";

    return $res->write(layout("Produk", $html));
});

/* HALAMAN ORDER */
$app->get('/order/{id}', function ($req, $res, $args) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    $product = $db->get("products", "*", [
        "id" => $args["id"]
    ]);

    $html = "
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='card shadow-sm'>
                    <div class='card-body'>
                        <h4 class='mb-3'>📝 Order Produk</h4>

                        <ul class='list-group mb-3'>
                            <li class='list-group-item'>
                                <b>Produk:</b> {$product['name']}
                            </li>
                            <li class='list-group-item'>
                                <b>Harga:</b> Rp {$product['price']}
                            </li>
                        </ul>

                        <form method='POST'>
                            <div class='mb-3'>
                                <label class='form-label'>Jumlah</label>
                                <input type='number' name='qty' value='1' min='1'
                                       class='form-control' required>
                            </div>

                            <button class='btn btn-primary w-100'>
                                🛒 Buat Pesanan
                            </button>
                        </form>

                        <a href='/slim-order/public/products'
                           class='btn btn-link mt-3'>
                           ← Kembali ke Produk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    ";

    return $res->write(layout("Order Produk", $html));
});

$app->post('/order/{id}', function ($req, $res, $args) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    $qty = $_POST["qty"];

    $product = $db->get("products", "*", [
        "id" => $args["id"]
    ]);

    $total = $qty * $product["price"];

// SIMPAN ORDER
    $db->insert("orders", [
        "user_id" => $_SESSION["user"]["id"],
        "total" => $total,
        "status" => "pending"
    ]);

    $orderId = $db->id();

   return $res->write(
    layout("Order Disimpan", "
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='card shadow-sm border-success'>
                    <div class='card-body text-center'>
                        <h4 class='text-success mb-3'>✅ Order Berhasil</h4>

                        <ul class='list-group mb-3 text-start'>
                            <li class='list-group-item'>
                                <b>Produk:</b> {$product['name']}
                            </li>
                            <li class='list-group-item'>
                                <b>Total:</b>
                                <span class='fw-bold text-success'>
                                    Rp {$total}
                                </span>
                            </li>
                            <li class='list-group-item'>
                                <b>Status:</b>
                                <span class='badge bg-warning text-dark'>
                                    Pending
                                </span>
                            </li>
                        </ul>

                        <a href='/slim-order/public/pay/{$orderId}'
                           class='btn btn-success w-100 mb-2'>
                           💳 Pembayaran
                        </a>

                        <a href='/slim-order/public/products'
                           class='btn btn-outline-secondary w-100'>
                           ← Kembali ke Produk
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ")
    );
});

// Bayar
$app->get('/pay/{id}', function ($req, $res, $args) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    $order = $db->get("orders", "*", [
        "id" => $args["id"]
    ]);

    $notif = "";
if (isset($_SESSION['success'])) {
    $notif = "
    <div class='alert alert-success text-center'>
        {$_SESSION['success']}
    </div>
    ";
    unset($_SESSION['success']); // hapus setelah disimpan
}

return $res->write(
    layout("Pembayaran", "

    <!-- 🔔 NOTIFIKASI -->
    $notif
        
        <div class='row justify-content-center'>
            <div class='col-md-5'>
                <div class='card shadow-sm'>
                    <div class='card-body text-center'>
                        <h4 class='mb-3'>💳 Pembayaran</h4>

                        <p>Total yang harus dibayar:</p>
                        <h3 class='text-success mb-4'>
                            Rp {$order['total']}
                        </h3>

                        <!-- CREATE MOCK PAYMENT (PENDING) -->
                        <form method='post' action='/slim-order/public/payment/mock/create'>
                            <input type='hidden' name='order_id' value='{$order['id']}'>
                            <button type='submit' class='btn btn-primary w-100'>
                                ✅ Bayar (Mock Payment)
                            </button>
                        </form>

                        <!-- CALLBACK MOCK PAYMENT (PAID) -->
                        <form method='post' action='/slim-order/public/payment/mock/callback' class='mt-2'>
                            <input type='hidden' name='order_id' value='{$order['id']}'>
                            <button type='submit' class='btn btn-success w-100'>
                                ✔ Simulasi Pembayaran Berhasil
                            </button>
                        </form>

                        <a href='/slim-order/public/dashboard'
                           class='btn btn-link mt-3'>
                           ← Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ")
    );
});

//Bayar Berhasil
$app->get('/pay-success/{id}', function ($req, $res, $args) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    $db->update("orders", [
        "status" => "paid"
    ], [
        "id" => $args["id"]
    ]);

    return $res->write(
    layout("Pembayaran Berhasil", "
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='card shadow-sm border-success'>
                    <div class='card-body text-center'>
                        <h3 class='text-success'>🎉 Pembayaran Berhasil</h3>

                        <p class='mt-3'>
                            Status order:
                            <span class='badge bg-success'>PAID</span>
                        </p>

                        <a href='/slim-order/public/dashboard'
                           class='btn btn-primary mt-3'>
                           Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ")
    );
});

/* LIHAT DAFTAR ORDER */
$app->get('/orders', function ($req, $res) use ($db) {

    if (!isLogin()) {
        return $res->withRedirect("/slim-order/public/login");
    }

    $orders = $db->select("orders", "*", [
        "user_id" => $_SESSION["user"]["id"]
    ]);

    $html = "
        <div class='d-flex justify-content-between align-items-center mb-4'>
            <h3>📦 Daftar Order</h3>
            <a href='/slim-order/public/dashboard' class='btn btn-outline-secondary'>
                ← Dashboard
            </a>
        </div>
    ";

    if (!$orders) {

        $html .= "
            <div class='alert alert-info text-center'>
                Belum ada order
            </div>
        ";

    } else {

        $html .= "
            <div class='card shadow-sm'>
                <div class='card-body'>
                    <table class='table table-hover align-middle mb-0'>
                        <thead class='table-light'>
                            <tr>
                                <th>Order</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
        ";

        foreach ($orders as $o) {

            $badge = $o['status'] === 'paid'
                ? "<span class='badge bg-success'>PAID</span>"
                : "<span class='badge bg-warning text-dark'>PENDING</span>";

            $html .= "
                <tr>
                    <td>Order #{$o['id']}</td>
                    <td>Rp {$o['total']}</td>
                    <td>$badge</td>
                </tr>
            ";
        }

        $html .= "
                        </tbody>
                    </table>
                </div>
            </div>
        ";
    }

    return $res->write(layout('Daftar Order', $html));
});

// ==================================
// MOCK PAYMENT GATEWAY
// ==================================

// create transaction (pending)
$app->post('/payment/mock/create', function ($req, $res) use ($db) {
    $data = $req->getParsedBody();
    $orderId = $data['order_id'];

    $db->update("orders", [
        "status" => "pending"
    ], [
        "id" => $orderId
    ]);

    // FLASH MESSAGE
    $_SESSION['success'] = "Transaksi berhasil dibuat. Menunggu pembayaran.";

    return $res->withRedirect("/slim-order/public/pay/" . $orderId);
});

// callback simulation (paid)
$app->post('/payment/mock/callback', function ($req, $res) use ($db) {
    $data = $req->getParsedBody();
    $orderId = $data['order_id'];

    $db->update("orders", [
        "status" => "paid"
    ], [
        "id" => $orderId
    ]);

    return $res->withRedirect("/slim-order/public/dashboard");
});

