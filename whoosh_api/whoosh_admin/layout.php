<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whoosh Admin Dashboard</title>
    <!-- Ant Design CSS via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/4.24.15/antd.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        body { background-color: #f0f2f5; }
        .logo {
            height: 32px;
            margin: 16px;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        .ant-layout-sider {
            min-height: 100vh;
        }
        .header-user {
            float: right;
            padding-right: 24px;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="ant-layout ant-layout-has-sider">
            <!-- Sidebar -->
            <div class="ant-layout-sider ant-layout-sider-dark">
                <div class="logo">WHOOSH ADMIN</div>
                <ul class="ant-menu ant-menu-dark ant-menu-root ant-menu-inline">
                    <li class="ant-menu-item"><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                    <li class="ant-menu-item"><a href="stations.php"><i class="fas fa-train"></i> <span>Stations</span></a></li>
                    <li class="ant-menu-item"><a href="schedules.php"><i class="fas fa-calendar-alt"></i> <span>Schedules</span></a></li>
                    <li class="ant-menu-item"><a href="banners.php"><i class="fas fa-image"></i> <span>Banners</span></a></li>
                    <li class="ant-menu-item"><a href="promotions.php"><i class="fas fa-tags"></i> <span>Promotions</span></a></li>
                    <li class="ant-menu-item"><a href="../api.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>

            <!-- Main Layout -->
            <div class="ant-layout">
                <header class="ant-layout-header" style="background: #fff; padding: 0 24px;">
                    <div class="header-user">
                        <span class="ant-avatar ant-avatar-circle ant-avatar-icon"><i class="fas fa-user"></i></span>
                        Admin User
                    </div>
                </header>
                <main class="ant-layout-content" style="margin: 24px 16px; padding: 24px; background: #fff; min-height: 280px;">
                    <!-- Content will be injected here -->
                    <?php echo $content; ?>
                </main>
                <footer class="ant-layout-footer" style="text-align: center;">
                    Whoosh High Speed Railway ©2024 Admin Panel
                </footer>
            </div>
        </div>
    </div>

    <!-- Ant Design JS and Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/antd/4.24.15/antd.min.js"></script>
</body>
</html>
