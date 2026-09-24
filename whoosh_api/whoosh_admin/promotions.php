<?php
ob_start();
include '../config.php';

// Logic Handle Form
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $color = $_POST['bg_color'];
    $conn->query("INSERT INTO promotions (title, description, bg_color) VALUES ('$title', '$desc', '$color')");
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM promotions WHERE id=$id");
}

$promos = $conn->query("SELECT * FROM promotions");
?>

<div class="ant-page-header">
    <div class="ant-page-header-heading">
        <span class="ant-page-header-heading-title">Manage Promotions</span>
    </div>
</div>

<div class="ant-card ant-card-bordered" style="margin-top: 24px;">
    <div class="ant-card-head"><div class="ant-card-head-title">Add New Promotion</div></div>
    <div class="ant-card-body">
        <form action="" method="POST" class="ant-form">
            <div class="ant-row" style="margin-bottom: 16px;">
                <div class="ant-col ant-col-8" style="padding-right: 8px;">
                    <input type="text" name="title" placeholder="Promo Title" class="ant-input" required>
                </div>
                <div class="ant-col ant-col-12" style="padding-right: 8px;">
                    <input type="text" name="description" placeholder="Short Description" class="ant-input" required>
                </div>
                <div class="ant-col ant-col-4">
                    <select name="bg_color" class="ant-input">
                        <option value="#FFFFEBEE" style="background:#FFFFEBEE">Soft Red</option>
                        <option value="#E3F2FD" style="background:#E3F2FD">Soft Blue</option>
                        <option value="#E8F5E9" style="background:#E8F5E9">Soft Green</option>
                        <option value="#FFF3E0" style="background:#FFF3E0">Soft Orange</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="add" class="ant-btn ant-btn-primary">Add Promotion</button>
        </form>
    </div>
</div>

<div class="ant-table-wrapper" style="margin-top: 24px;">
    <div class="ant-table">
        <div class="ant-table-content">
            <table style="width: 100%; border-collapse: collapse;">
                <thead class="ant-table-thead">
                    <tr>
                        <th class="ant-table-cell">Title</th>
                        <th class="ant-table-cell">Description</th>
                        <th class="ant-table-cell">Color Tag</th>
                        <th class="ant-table-cell">Action</th>
                    </tr>
                </thead>
                <tbody class="ant-table-tbody">
                    <?php while($p = $promos->fetch_assoc()): ?>
                    <tr class="ant-table-row">
                        <td class="ant-table-cell"><strong><?php echo $p['title']; ?></strong></td>
                        <td class="ant-table-cell"><?php echo $p['description']; ?></td>
                        <td class="ant-table-cell"><span style="display:inline-block; width:20px; height:20px; background:<?php echo $p['bg_color']; ?>; border:1px solid #ddd;"></span> <?php echo $p['bg_color']; ?></td>
                        <td class="ant-table-cell">
                            <a href="?delete=<?php echo $p['id']; ?>" class="ant-btn ant-btn-danger ant-btn-sm" onclick="return confirm('Hapus promo ini?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
