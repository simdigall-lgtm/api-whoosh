<?php
/**
 * Whoosh API - Robust Version
 * Handles all actions from WhooshApiService.kt
 */

// 1. Initial Headers & Error Config
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable HTML error output

$response = new stdClass();
$response->status = "error"; // Default status

try {
    // 2. Database Connection
    require_once 'config.php';

    // Ensure Basic Tables Exist
    $conn->query("CREATE TABLE IF NOT EXISTS users (user_id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), email VARCHAR(100) UNIQUE, phone VARCHAR(20), password VARCHAR(255), role VARCHAR(20) DEFAULT 'user')");
    $conn->query("CREATE TABLE IF NOT EXISTS stations (station_id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), city VARCHAR(100))");
    $conn->query("CREATE TABLE IF NOT EXISTS schedules (schedule_id INT AUTO_INCREMENT PRIMARY KEY, train_code VARCHAR(50), dep_station_id INT, arr_station_id INT, departure_time DATETIME, arrival_time DATETIME, price INT)");
    $conn->query("CREATE TABLE IF NOT EXISTS bookings (booking_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, schedule_id INT, booking_code VARCHAR(20) UNIQUE, total_price BIGINT, status ENUM('PENDING', 'PAID', 'CANCELLED') DEFAULT 'PENDING', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $conn->query("CREATE TABLE IF NOT EXISTS booking_details (detail_id INT AUTO_INCREMENT PRIMARY KEY, booking_id INT, passenger_name VARCHAR(100), id_card_number VARCHAR(50), seat_number VARCHAR(10), carriage_number INT)");
    $conn->query("CREATE TABLE IF NOT EXISTS passengers (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, name VARCHAR(100), id_card VARCHAR(50), type VARCHAR(20) DEFAULT 'Adult')");

    // SCHEMA STANDARDIZATION
    // 1. Fix 'passengers' table
    $res_p = $conn->query("SHOW COLUMNS FROM passengers");
    if ($res_p) {
        $p_cols = []; while($c = $res_p->fetch_assoc()) { $p_cols[] = $c['Field']; }
        if (!in_array('id', $p_cols) && in_array('passenger_id', $p_cols)) {
            $conn->query("ALTER TABLE passengers CHANGE passenger_id id INT AUTO_INCREMENT");
        }
        if (!in_array('name', $p_cols)) {
            if (in_array('passenger_name', $p_cols)) $conn->query("ALTER TABLE passengers CHANGE passenger_name name VARCHAR(100)");
            else $conn->query("ALTER TABLE passengers ADD COLUMN name VARCHAR(100) AFTER user_id");
        }
        if (!in_array('id_card', $p_cols)) {
            if (in_array('id_card_number', $p_cols)) $conn->query("ALTER TABLE passengers CHANGE id_card_number id_card VARCHAR(50)");
            else $conn->query("ALTER TABLE passengers ADD COLUMN id_card VARCHAR(50) AFTER name");
        }
        if (!in_array('type', $p_cols)) $conn->query("ALTER TABLE passengers ADD COLUMN type VARCHAR(20) DEFAULT 'Adult'");
    }

    // 2. Fix 'booking_details' table
    $res_bd = $conn->query("SHOW COLUMNS FROM booking_details");
    if ($res_bd) {
        $bd_cols = []; while($c = $res_bd->fetch_assoc()) { $bd_cols[] = $c['Field']; }
        if (!in_array('detail_id', $bd_cols) && in_array('id', $bd_cols)) {
            $conn->query("ALTER TABLE booking_details CHANGE id detail_id INT AUTO_INCREMENT");
        }
        if (!in_array('passenger_name', $bd_cols)) {
            if (in_array('name', $bd_cols)) $conn->query("ALTER TABLE booking_details CHANGE name passenger_name VARCHAR(100)");
            else $conn->query("ALTER TABLE booking_details ADD COLUMN passenger_name VARCHAR(100) AFTER booking_id");
        }
        if (!in_array('id_card_number', $bd_cols)) {
            if (in_array('id_card', $bd_cols)) $conn->query("ALTER TABLE booking_details CHANGE id_card id_card_number VARCHAR(50)");
            else $conn->query("ALTER TABLE booking_details ADD COLUMN id_card_number VARCHAR(50) AFTER passenger_name");
        }
        if (!in_array('seat_number', $bd_cols)) $conn->query("ALTER TABLE booking_details ADD COLUMN seat_number VARCHAR(10) AFTER id_card_number");
        if (!in_array('carriage_number', $bd_cols)) $conn->query("ALTER TABLE booking_details ADD COLUMN carriage_number INT AFTER seat_number");
    }

    // Dynamic Column Detection for Users (name vs username)
    $res = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
    $uCol = ($res && $res->num_rows > 0) ? "name" : "username";

    // 3. API Actions
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_stations':
            $res = $conn->query("SELECT * FROM stations");
            $data = [];
            while ($r = $res->fetch_assoc()) {
                $r['station_id'] = (int)$r['station_id'];
                $data[] = $r;
            }
            // Seed if empty
            if (empty($data)) {
                $conn->query("INSERT INTO stations (name, city) VALUES ('Halim','Jakarta'), ('Karawang','Karawang'), ('Padalarang','Bandung'), ('Tegalluar','Bandung')");
                $res = $conn->query("SELECT * FROM stations");
                while ($r = $res->fetch_assoc()) { $r['station_id'] = (int)$r['station_id']; $data[] = $r; }
            }
            $response = $data;
            break;

        case 'register':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';

            $check = $conn->query("SELECT * FROM users WHERE email='$email'");
            if ($check->num_rows > 0) {
                $response->status = "error";
                $response->message = "Email sudah terdaftar";
            } else {
                $stmt = $conn->prepare("INSERT INTO users ($uCol, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $phone, $password);
                if ($stmt->execute()) {
                    $response->status = "success";
                    $response->user = ["user_id" => (int)$conn->insert_id, "name" => $name, "email" => $email, "phone" => $phone, "role" => "user"];
                } else {
                    $response->status = "error"; $response->message = $conn->error;
                }
            }
            break;

        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $res = $conn->query("SELECT * FROM users WHERE email='$email'");
            if ($u = $res->fetch_assoc()) {
                if ($u['password'] == $password) {
                    $response->status = "success";
                    $response->user = [
                        "user_id" => (int)$u['user_id'],
                        "name" => $u[$uCol] ?? "User",
                        "email" => $u['email'],
                        "phone" => $u['phone'] ?? "",
                        "role" => $u['role'] ?? "user"
                    ];
                } else {
                    $response->status = "error"; $response->message = "Password salah";
                }
            } else {
                $response->status = "error"; $response->message = "User tidak ditemukan";
            }
            break;

        case 'search_schedules':
            $from = (int)($_GET['from'] ?? 1);
            $to = (int)($_GET['to'] ?? 4);
            $date = $_GET['date'] ?? '2026-05-12';

            $sql = "SELECT s.*, st1.name as dep_station_name, st2.name as arr_station_name
                    FROM schedules s
                    JOIN stations st1 ON s.dep_station_id = st1.station_id
                    JOIN stations st2 ON s.arr_station_id = st2.station_id
                    WHERE s.dep_station_id=$from AND s.arr_station_id=$to";

            $res = $conn->query($sql);
            $data = [];
            while ($r = $res->fetch_assoc()) {
                $r['schedule_id'] = (int)$r['schedule_id'];
                $r['price'] = (int)$r['price'];
                $data[] = $r;
            }

            // Dummy if empty for testing
            if (empty($data)) {
                $price = 150000 + (rand(1, 10) * 15000);
                $conn->query("INSERT INTO schedules (train_code, dep_station_id, arr_station_id, departure_time, arrival_time, price) VALUES ('WSH-".rand(100,999)."', $from, $to, '$date 06:30:00', '$date 07:15:00', $price)");
                $res = $conn->query($sql);
                while ($r = $res->fetch_assoc()) { $r['schedule_id'] = (int)$r['schedule_id']; $r['price'] = (int)$r['price']; $data[] = $r; }
            }
            $response = $data;
            break;

        case 'get_seats':
            $seats = [];
            $classes = ["First Class", "Business Class", "Premium Economy Class"];
            foreach ($classes as $class) {
                for ($i = 1; $i <= 10; $i++) {
                    $seats[] = [
                        "seat_id" => count($seats) + 1,
                        "seat_number" => substr($class, 0, 1) . $i,
                        "class_type" => $class,
                        "is_available" => (rand(0, 10) > 2)
                    ];
                }
            }
            $response = $seats;
            break;

        case 'add_booking':
            $uid = (int)($_POST['user_id'] ?? 1);
            $sid = (int)$_POST['schedule_id'] ?? 0;
            $code = $_POST['booking_code'] ?? '';
            $total = (int)$_POST['total_price'] ?? 0;
            $passengers = json_decode($_POST['passengers'] ?? '[]', true);

            $stmt = $conn->prepare("INSERT INTO bookings (user_id, schedule_id, booking_code, total_price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $uid, $sid, $code, $total);
            if ($stmt->execute()) {
                $bid = $conn->insert_id;

                if (is_array($passengers)) {
                    foreach ($passengers as $p) {
                        $stmt_d = $conn->prepare("INSERT INTO booking_details (booking_id, passenger_name, id_card_number, seat_number, carriage_number) VALUES (?, ?, ?, ?, ?)");
                        $seat = $p['seat_id'] ?? $p['seat_number'] ?? '1A';
                        $carriage = (int)($p['carriage_number'] ?? 1);
                        $p_name = $p['passenger_name'] ?? $p['name'] ?? '';
                        $p_card = $p['id_card_number'] ?? $p['id_card'] ?? '';
                        $stmt_d->bind_param("isssi", $bid, $p_name, $p_card, $seat, $carriage);
                        $stmt_d->execute();
                    }
                }
                $response->status = "success"; $response->booking_code = $code;
            } else {
                $response->status = "error"; $response->message = $conn->error;
            }
            break;

        case 'pay_booking':
            $code = $_POST['booking_code'] ?? '';
            if ($conn->query("UPDATE bookings SET status='PAID' WHERE booking_code='$code'")) {
                $response->status = "success";
            } else {
                $response->status = "error"; $response->message = $conn->error;
            }
            break;

        case 'cancel_booking':
            $code = $_POST['booking_code'] ?? '';
            if ($conn->query("UPDATE bookings SET status='CANCELLED' WHERE booking_code='$code'")) {
                $response->status = "success";
            } else {
                $response->status = "error"; $response->message = $conn->error;
            }
            break;

        case 'get_passengers':
            $uid = (int)($_GET['user_id'] ?? 0);
            $data = [];

            // 1. Get from saved passengers
            $res1 = $conn->query("SELECT id, name, id_card, type FROM passengers WHERE user_id=$uid");
            if ($res1) {
                while ($r = $res1->fetch_assoc()) {
                    $r['id'] = (int)$r['id'];
                    $data[$r['id_card']] = $r;
                }
            }

            // 2. Get from previous booking details
            $res2 = $conn->query("SELECT bd.detail_id as id, bd.passenger_name as name, bd.id_card_number as id_card
                                 FROM booking_details bd JOIN bookings b ON bd.booking_id = b.booking_id
                                 WHERE b.user_id=$uid");
            if ($res2) {
                while ($r = $res2->fetch_assoc()) {
                    if (!isset($data[$r['id_card']])) {
                        $r['id'] = (int)$r['id'];
                        $r['type'] = 'Adult';
                        $data[$r['id_card']] = $r;
                    }
                }
            }
            $response = array_values($data);
            break;

        case 'add_passenger':
            $uid = (int)($_POST['user_id'] ?? 1);
            $name = $_POST['name'] ?? '';
            $id_card = $_POST['id_card'] ?? '';

            $stmt = $conn->prepare("INSERT INTO passengers (user_id, name, id_card) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $uid, $name, $id_card);
            if ($stmt->execute()) {
                $response->status = "success";
            } else {
                $response->status = "error"; $response->message = $conn->error;
            }
            break;

        case 'add_station':
            $name = $_POST['name'] ?? '';
            $city = $_POST['city'] ?? '';
            $stmt = $conn->prepare("INSERT INTO stations (name, city) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $city);
            if ($stmt->execute()) { $response->status = "success"; }
            else { $response->status = "error"; $response->message = $conn->error; }
            break;

        case 'delete_station':
            $id = (int)($_POST['id'] ?? 0);
            if ($conn->query("DELETE FROM stations WHERE station_id=$id")) { $response->status = "success"; }
            else { $response->status = "error"; $response->message = $conn->error; }
            break;

        case 'add_schedule':
            $code = $_POST['train_code'] ?? '';
            $dep = (int)($_POST['dep_id'] ?? 0);
            $arr = (int)($_POST['arr_id'] ?? 0);
            $dTime = $_POST['dep_time'] ?? '';
            $aTime = $_POST['arr_time'] ?? '';
            $price = (int)($_POST['price'] ?? 0);
            $stmt = $conn->prepare("INSERT INTO schedules (train_code, dep_station_id, arr_station_id, departure_time, arrival_time, price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siissi", $code, $dep, $arr, $dTime, $aTime, $price);
            if ($stmt->execute()) { $response->status = "success"; }
            else { $response->status = "error"; $response->message = $conn->error; }
            break;

        case 'delete_schedule':
            $id = (int)($_POST['id'] ?? 0);
            if ($conn->query("DELETE FROM schedules WHERE schedule_id=$id")) { $response->status = "success"; }
            else { $response->status = "error"; $response->message = $conn->error; }
            break;

        case 'get_banners':
            $res = $conn->query("SELECT * FROM banners WHERE is_active = 1");
            $data = [];
            while ($r = $res->fetch_assoc()) { $data[] = $r; }
            if (empty($data)) {
                $data = [
                    ["image_url" => "http://10.10.45.49/whoosh_api/uploads/kereta.png", "title" => "Banner 1"],
                    ["image_url" => "http://10.10.45.49/whoosh_api/uploads/kereta2.png", "title" => "Banner 2"],
                    ["image_url" => "http://10.10.45.49/whoosh_api/uploads/kereta3.png", "title" => "Banner 3"]
                ];
            }
            $response = $data;
            break;

        case 'get_promotions':
            $res = $conn->query("SELECT * FROM promotions");
            $data = [];
            while ($r = $res->fetch_assoc()) { $data[] = $r; }
            if (empty($data)) {
                $data = [
                    ["title" => "Diskon Ramadhan", "description" => "Hemat hingga 20% untuk mudik.", "bg_color" => "#FFFFEBEE"],
                    ["title" => "Feeder Bus Gratis", "description" => "Nikmati fasilitas bus gratis.", "bg_color" => "#E3F2FD"]
                ];
            }
            $response = $data;
            break;

        case 'add_banner':
            $url = $_POST['image_url'] ?? '';
            $title = $_POST['title'] ?? '';
            $stmt = $conn->prepare("INSERT INTO banners (image_url, title, is_active) VALUES (?, ?, 1)");
            $stmt->bind_param("ss", $url, $title);
            if ($stmt->execute()) { $response->status = "success"; }
            else { $response->status = "error"; $response->message = $conn->error; }
            break;

        case 'delete_banner':
            $id = (int)($_POST['id'] ?? 0);
            if ($conn->query("DELETE FROM banners WHERE id=$id")) { $response->status = "success"; }
            break;

        case 'add_promotion':
            $title = $_POST['title'] ?? '';
            $desc = $_POST['description'] ?? '';
            $color = $_POST['bg_color'] ?? '#FFFFEBEE';
            $stmt = $conn->prepare("INSERT INTO promotions (title, description, bg_color) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $desc, $color);
            if ($stmt->execute()) { $response->status = "success"; }
            break;

        case 'delete_promotion':
            $id = (int)($_POST['id'] ?? 0);
            if ($conn->query("DELETE FROM promotions WHERE id=$id")) { $response->status = "success"; }
            break;

        case 'get_my_tickets':
            $uid = (int)($_GET['user_id'] ?? 0);
            $sql = "SELECT b.*, s.train_code, s.departure_time, s.arrival_time, st1.name as dep_station_name, st2.name as arr_station_name
                    FROM bookings b JOIN schedules s ON b.schedule_id = s.schedule_id
                    JOIN stations st1 ON s.dep_station_id = st1.station_id
                    JOIN stations st2 ON s.arr_station_id = st2.station_id WHERE b.user_id=$uid";
            $res = $conn->query($sql);
            $data = [];
            while ($r = $res->fetch_assoc()) {
                $r['total_price'] = (int)$r['total_price'];
                $r['status'] = strtolower($r['status']); // Tambahkan ini agar sinkron dengan Android
                $data[] = $r;
            }
            $response = $data;
            break;

        default:
            $response->status = "error";
            $response->message = "Unknown action: $action";
            break;
    }

} catch (Throwable $t) {
    // Catch all errors and return as JSON
    $response = new stdClass();
    $response->status = "error";
    $response->message = "Server exception: " . $t->getMessage();
}

// Final output
echo json_encode($response);
