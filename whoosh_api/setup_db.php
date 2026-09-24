<?php
$conn = new mysqli("127.0.0.1", "root", "", "whoosh_db");
if ($conn->connect_error) {
    // Jika DB belum ada, buat dulu
    $conn = new mysqli("127.0.0.1", "root", "");
    $conn->query("CREATE DATABASE IF NOT EXISTS whoosh_db");
    $conn->select_db("whoosh_db");
}

try {
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("DROP TABLE IF EXISTS booking_details, bookings, seats, schedules, stations, users");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // 1. USERS
    $conn->query("CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20)
    )");

    // 2. STATIONS
    $conn->query("CREATE TABLE stations (
        station_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        city VARCHAR(100)
    )");

    // 3. SCHEDULES
    $conn->query("CREATE TABLE schedules (
        schedule_id INT AUTO_INCREMENT PRIMARY KEY,
        train_code VARCHAR(50) NOT NULL,
        dep_station_id INT,
        arr_station_id INT,
        departure_time DATETIME,
        arrival_time DATETIME,
        price_base INT,
        FOREIGN KEY (dep_station_id) REFERENCES stations(station_id),
        FOREIGN KEY (arr_station_id) REFERENCES stations(station_id)
    )");

    // 4. SEATS
    $conn->query("CREATE TABLE seats (
        seat_id INT AUTO_INCREMENT PRIMARY KEY,
        train_code VARCHAR(50),
        seat_number VARCHAR(10),
        class_type ENUM('First Class', 'Business Class', 'Premium Economy Class')
    )");

    // 5. BOOKINGS (Header)
    $conn->query("CREATE TABLE bookings (
        booking_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        schedule_id INT,
        booking_code VARCHAR(20) UNIQUE,
        total_price BIGINT,
        status ENUM('PENDING', 'PAID', 'CANCELLED', 'COMPLETED') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id)
    )");

    // 6. BOOKING DETAILS
    $conn->query("CREATE TABLE booking_details (
        detail_id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT,
        passenger_name VARCHAR(100),
        id_card_number VARCHAR(50),
        seat_number VARCHAR(10),
        carriage_number INT,
        FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
    )");

    // ISI DATA AWAL (SEEDING)
    $conn->query("INSERT INTO users (user_id, username, email, password) VALUES (1, 'adi', 'adi@mail.com', '123')");
    $conn->query("INSERT INTO stations (station_id, name, city) VALUES
        (1, 'Halim', 'Jakarta'), (2, 'Karawang', 'Karawang'),
        (3, 'Padalarang', 'Bandung Barat'), (4, 'Tegalluar', 'Bandung')");

    $conn->query("INSERT INTO schedules (train_code, dep_station_id, arr_station_id, departure_time, arrival_time, price_base) VALUES
        ('WSH-115', 1, 3, '2026-05-12 06:30:00', '2026-05-12 07:15:00', 150000),
        ('WSH-116', 3, 1, '2026-05-12 09:00:00', '2026-05-12 09:45:00', 150000)");

    for ($i = 1; $i <= 10; $i++) {
        $conn->query("INSERT INTO seats (train_code, seat_number, class_type) VALUES ('WSH-115', '0{$i}A', 'Premium Economy Class')");
    }

    echo "DATABASE BERHASIL DI-RESET DENGAN RELASI REAL!";

    // Sync Banners & Promos
    $conn->query("CREATE TABLE IF NOT EXISTS banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_url TEXT NOT NULL,
        title VARCHAR(100),
        is_active TINYINT DEFAULT 1
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS promotions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        bg_color VARCHAR(20) DEFAULT '#FFFFEBEE'
    )");

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>