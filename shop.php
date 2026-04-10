<?php include 'includes/header.php'; ?>
<?php include 'includes/db.php'; ?>

<div class="page-header text-center">
    <div class="container">
        <h1>Shop Dajot Products</h1>
        <p>Premium quality poultry straight from our farms</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="product-grid">
            <?php
            $sql = "SELECT * FROM products ORDER BY name ASC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $image = !empty($row['image']) ? $row['image'] : 'assets/images/logo.png';
                    ?>
                    <div class="product-card">
                         <div class="product-img-placeholder" style="height:200px; padding: 0; background: #fff; border-bottom: 1px solid #eee;">
                              <img src="<?php echo $image; ?>" style="width:100%; height:100%; object-fit:cover; border-radius: 10px 10px 0 0;" alt="<?php echo $row['name']; ?>">
                         </div>
                        <div class="product-info">
                            <h3><?php echo e($row['name']); ?></h3>
                            <p class="desc"><?php echo e($row['description']); ?></p>
                            <p class="price">₦<?php echo number_format($row['price']); ?> / <?php echo e($row['price_unit']); ?></p>
                            <a href="https://wa.me/2349034670525?text=I%20want%20to%20order%20<?php echo urlencode($row['name']); ?>" class="btn btn-primary btn-block">Order on WhatsApp</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No products available at the moment.</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
