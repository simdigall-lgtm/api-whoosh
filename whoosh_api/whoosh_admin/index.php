<?php
ob_start();
?>

<div class="ant-page-header">
    <div class="ant-page-header-heading">
        <span class="ant-page-header-heading-title">Dashboard Overview</span>
    </div>
</div>

<div style="margin-top: 24px;">
    <div class="ant-row" style="margin: -8px;">
        <div class="ant-col ant-col-6" style="padding: 8px;">
            <div class="ant-card ant-card-bordered">
                <div class="ant-card-body">
                    <div class="ant-statistic">
                        <div class="ant-statistic-title">Total Stations</div>
                        <div class="ant-statistic-content" style="color: #1890ff;">
                            <span class="ant-statistic-content-value">4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ant-col ant-col-6" style="padding: 8px;">
            <div class="ant-card ant-card-bordered">
                <div class="ant-card-body">
                    <div class="ant-statistic">
                        <div class="ant-statistic-title">Active Banners</div>
                        <div class="ant-statistic-content" style="color: #52c41a;">
                            <span class="ant-statistic-content-value">3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ant-col ant-col-6" style="padding: 8px;">
            <div class="ant-card ant-card-bordered">
                <div class="ant-card-body">
                    <div class="ant-statistic">
                        <div class="ant-statistic-title">Ongoing Promos</div>
                        <div class="ant-statistic-content" style="color: #faad14;">
                            <span class="ant-statistic-content-value">2</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ant-col ant-col-6" style="padding: 8px;">
            <div class="ant-card ant-card-bordered">
                <div class="ant-card-body">
                    <div class="ant-statistic">
                        <div class="ant-statistic-title">Daily Bookings</div>
                        <div class="ant-statistic-content" style="color: #f5222d;">
                            <span class="ant-statistic-content-value">128</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ant-card ant-card-bordered" style="margin-top: 24px;">
    <div class="ant-card-head"><div class="ant-card-head-title">System Status</div></div>
    <div class="ant-card-body">
        <div class="ant-alert ant-alert-success ant-alert-with-description">
            <span class="ant-alert-icon"><i class="fas fa-check-circle"></i></span>
            <div class="ant-alert-content">
                <div class="ant-alert-message">API Connectivity: OK</div>
                <div class="ant-alert-description">Connection to MySQL and Mobile API is stable.</div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
