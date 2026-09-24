<?php
ob_start();
include '../config.php';

// Logic Handle Form
if (isset($_POST['add'])) {
    $url = $_POST['image_url'];
    $title = $_POST['title'];
    $conn->query("INSERT INTO banners (image_url, title, is_active) VALUES ('$url', '$title', 1)");
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM banners WHERE id=$id");
}

$banners = $conn->query("SELECT * FROM banners");
?>

<div class="ant-page-header">
    <div class="ant-page-header-heading">
        <span class="ant-page-header-heading-title">Manage Dynamic Banners</span>
    </div>
</div>

<div class="ant-card ant-card-bordered" style="margin-top: 24px;">
    <div class="ant-card-head"><div class="ant-card-head-title">Add New Banner</div></div>
    <div class="ant-card-body">
        <form action="" method="POST" class="ant-form ant-form-inline">
            <div class="ant-form-item">
                <input type="text" name="title" placeholder="Banner Title" class="ant-input" required>
            </div>
            <div class="ant-form-item">
                <input type="text" name="image_url" placeholder="Image URL (http://...)" class="ant-input" style="width: 300px;" required>
            </div>
            <div class="ant-form-item">
                <button type="submit" name="add" class="ant-btn ant-btn-primary">Add Banner</button>
            </div>
        </form>
    </div>
</div>

<div class="ant-table-wrapper" style="margin-top: 24px;">
    <div class="ant-table">
        <div class="ant-table-content">
            <table style="width: 100%; border-collapse: collapse;">
                <thead class="ant-table-thead">
                    <tr>
                        <th class="ant-table-cell">Preview</th>
                        <th class="ant-table-cell">Title</th>
                        <th class="ant-table-cell">URL</th>
                        <th class="ant-table-cell">Action</th>
                    </tr>
                </thead>
                <tbody class="ant-table-tbody">
                    <?php while($b = $banners->fetch_assoc()): ?>
                    <tr class="ant-table-row">
                        <td class="ant-table-cell"><img src="<?php echo $b['image_url']; ?>" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px;"></td>
                        <td class="ant-table-cell"><?php echo $b['title']; ?></td>
                        <td class="ant-table-cell"><small><?php echo $b['image_url']; ?></small></td>
                        <td class="ant-table-cell">
                            <a href="?delete=<?php echo $b['id']; ?>" class="ant-btn ant-btn-danger ant-btn-sm" onclick="return confirm('Hapus banner ini?')">Delete</a>
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
