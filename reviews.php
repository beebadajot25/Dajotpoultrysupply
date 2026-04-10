<?php 
include 'includes/header.php';
include 'includes/db.php';
?>


<section class="section">
    <div class="container">
        <h2 class="section-title">Community Reviews</h2>
        <p class="section-subtitle">Real feedback from verified purchases across our marketplace.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-top: 30px;">
            <?php
            // Fetch All Reviews with 3+ Stars
            $sql = "SELECT r.*, u.username as farmer_name, u.farm_name, u.id as seller_id
                    FROM reviews r 
                    JOIN users u ON r.seller_id = u.id 
                    WHERE r.rating >= 3 
                    ORDER BY r.created_at DESC 
                    LIMIT 50";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($rev = $result->fetch_assoc()) {
                    $display_name = !empty($rev['farm_name']) ? $rev['farm_name'] : $rev['farmer_name'];
                    ?>
                    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="margin-bottom: 12px;">
                            <?php for($i=0; $i<5; $i++): ?>
                                <?php if($i < $rev['rating']): ?>
                                    <i class="fas fa-star" style="color:#ecc94b;"></i>
                                <?php else: ?>
                                    <i class="far fa-star" style="color:#ddd;"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <p style="font-style: italic; color: #555; margin-bottom: 20px; line-height: 1.6;">
                            "<?php echo htmlspecialchars($rev['comment']); ?>"
                        </p>
                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f0f0f0; padding-top: 15px;">
                            <div>
                                <h5 style="margin: 0 0 5px; font-size: 1em;"><?php echo htmlspecialchars($rev['reviewer_name']); ?></h5>
                                <small style="color: #888;">
                                    Reviewed 
                                    <a href="vendor-shop.php?id=<?php echo $rev['seller_id']; ?>" style="color: #2e7d32; text-decoration: none; font-weight: 600;">
                                        <?php echo htmlspecialchars($display_name); ?>
                                    </a>
                                </small>
                            </div>
                            <small style="color: #a0aec0;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 12px;">
                    <i class="far fa-comments" style="font-size: 4em; color: #ddd; margin-bottom: 20px;"></i>
                    <h3 style="color: #666; margin-bottom: 10px;">No Reviews Yet</h3>
                    <p style="color: #888; margin-bottom: 25px;">Be the first to share your experience with our marketplace vendors!</p>
                    <a href="marketplace.php" class="btn btn-primary">Browse Marketplace</a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
