<?php include 'includes/header.php'; ?>

<style>
/* About Page Specific Styles */
.about-hero {
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
    padding: 80px 0 50px;
    text-align: center;
}

.about-hero h1 {
    font-size: 2.6em;
    color: #2e7d32;
    margin-bottom: 15px;
    font-weight: 700;
}

.about-hero p {
    font-size: 1.15em;
    color: #555;
    max-width: 650px;
    margin: 0 auto;
}

.about-section {
    padding: 60px 0;
}

.about-section.alt-bg {
    background-color: #f8f9fa;
}

.about-section-title {
    font-size: 2em;
    color: #2e7d32;
    margin-bottom: 25px;
    font-weight: 700;
}

.about-section p {
    font-size: 1.05em;
    line-height: 1.9;
    color: #555;
    margin-bottom: 15px;
}

/* Founder Section */
.founder-section {
    display: flex;
    flex-wrap: wrap;
    gap: 50px;
    align-items: center;
    background: white;
    padding: 50px;
    border-radius: 25px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
}

.founder-image {
    flex: 1;
    min-width: 280px;
    text-align: center;
}

.founder-image img {
    width: 100%;
    max-width: 350px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.founder-image p {
    margin-top: 15px;
    font-style: italic;
    color: #777;
    font-size: 0.95em;
}

.founder-content {
    flex: 1.5;
    min-width: 300px;
}

/* What We Do */
.what-we-do-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

/* Values Grid */
.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    margin-top: 40px;
}

.value-card {
    background: white;
    padding: 35px 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: #2e7d32;
}

.value-card i {
    font-size: 2.5em;
    color: #2e7d32;
    margin-bottom: 15px;
}

.value-card h4 {
    font-size: 1.2em;
    color: #333;
    margin-bottom: 10px;
    font-weight: 600;
}

.value-card p {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 0;
}

/* Team Cards Grid for About Page */
.about-team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 25px;
    max-width: 1000px;
    margin: 0 auto;
}

.about-team-card {
    background: white;
    border-radius: 20px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.about-team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
    border-color: #2e7d32;
}

.about-team-card img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 15px;
    border: 4px solid #e8f5e9;
    box-shadow: 0 5px 15px rgba(46, 125, 50, 0.15);
}

.about-team-card h4 {
    font-size: 1.15em;
    color: #333;
    margin-bottom: 5px;
    font-weight: 700;
}

.about-team-card .role {
    font-size: 0.9em;
    color: #2e7d32;
    font-weight: 600;
    margin: 0;
}

@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 2em;
    }
    
    .founder-section {
        padding: 30px 20px;
    }
    
    .about-section-title {
        font-size: 1.6em;
    }
    
    .about-team-grid {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .about-team-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <h1>About Dajot Poultry Supply</h1>
        <p>A family-led poultry business serving individuals and bulk buyers across Nigeria.</p>
    </div>
</section>

<!-- Our Story / Founder Section -->
<section class="about-section">
    <div class="container" style="max-width: 1000px;">
        <div class="founder-section">
            <div class="founder-image">
                <img src="assets/images/shuaib.png" alt="Late Shuaibu Dajot Mangu - Founder">
                <p>Late Alhaji Shuaibu Dajot Mangu (Founder)</p>
            </div>
            <div class="founder-content">
                <h2 class="about-section-title">Our Story</h2>
                <p>
                    Dajot Poultry Supply was founded by <strong>Late Shuaibu Dajot of Mangu, Jos</strong>, a respected poultry farmer with over 50 years of experience in the poultry business.
                </p>
                <p>
                    He built the business on trust, quality, and strong relationships with farmers and buyers. Over the years, he guided and supported many farmers, helping them grow and succeed in poultry production.
                </p>
                <p>
                    Today, his family continues this legacy, maintaining the same values while embracing modern systems and technology.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- What We Do -->
<section class="about-section alt-bg">
    <div class="container">
        <div class="what-we-do-content">
            <h2 class="about-section-title">What We Do</h2>
            <p>
                We sell and supply poultry products in both small and bulk quantities, serving homes, hotels, restaurants, schools, and businesses.
            </p>
            <p>
                We also buy poultry products in bulk directly from farmers and provide a marketplace where poultry farmers can showcase and sell their products.
            </p>
        </div>
    </div>
</section>

<!-- Our Marketplace -->
<section class="about-section">
    <div class="container">
        <div class="what-we-do-content">
            <h2 class="about-section-title">Our Poultry Marketplace</h2>
            <p>
                Our marketplace allows farmers and vendors to list poultry-related products such as live birds, eggs, feed, and processed poultry.
            </p>
            <p>
                Buyers can connect directly with sellers through WhatsApp or phone calls, making trade simple, transparent, and fast.
            </p>
            <div style="margin-top: 30px;">
                <a href="marketplace.php" class="btn btn-secondary" style="margin-right: 10px;">Browse Marketplace</a>
                <a href="vendor-register.php" class="btn btn-outline">Become a Vendor</a>
            </div>
        </div>
    </div>
</section>

<!-- Meet Our Team - FULL SECTION -->
<section class="about-section alt-bg">
    <div class="container">
        <h2 class="about-section-title text-center">Meet Our Team</h2>
        <p class="text-center" style="color: #666; margin-bottom: 40px;">A family-led poultry business built on trust, experience, and quality.</p>
        
        <div class="about-team-grid">
            <!-- Buhari Dajot -->
            <div class="about-team-card">
                <img src="assets/images/buhari.jpeg" alt="Buhari Dajot">
                <h4>Buhari Dajot</h4>
                <p class="role">Business Lead</p>
            </div>

            <!-- Hamza Dajot -->
            <div class="about-team-card">
                <img src="assets/images/hamza.jpeg" alt="Hamza Dajot">
                <h4>Hamza Dajot</h4>
                <p class="role">Operations Lead</p>
            </div>

            <!-- Abdul Karim Dajot -->
            <div class="about-team-card">
                <img src="assets/images/Duwa.png" alt="Abdul Karim Dajot">
                <h4>Abdul Karim Dajot</h4>
                <p class="role">Quality Lead</p>
            </div>

            <!-- Ismail Dajot -->
            <div class="about-team-card">
                <img src="assets/images/ismail.jpeg" alt="Ismail Dajot">
                <h4>Ismail Dajot</h4>
                <p class="role">Transport Lead</p>
            </div>

            <!-- Habeeba Shuaibu Dajot -->
            <div class="about-team-card">
                <img src="assets/images/Beeeba.png" alt="Habeeba Shuaibu Dajot">
                <h4>Habeeba Shuaibu Dajot</h4>
                <p class="role">Digital & Systems Lead</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="about-section">
    <div class="container" style="max-width: 1000px;">
        <h2 class="about-section-title text-center">Our Values</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-handshake"></i>
                <h4>Trust</h4>
                <p>We build lasting relationships based on honesty and reliability with every customer and farmer.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-certificate"></i>
                <h4>Quality</h4>
                <p>We maintain the highest standards in every product we supply, from farm to table.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-balance-scale"></i>
                <h4>Fair Trade</h4>
                <p>We ensure fair pricing for both farmers and buyers, creating value for everyone.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-users"></i>
                <h4>Community Growth</h4>
                <p>We empower local farmers and contribute to the growth of Nigeria's poultry industry.</p>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="about-section alt-bg">
    <div class="container" style="max-width: 900px; text-align: center;">
        <div style="background: #2e7d32; color: white; padding: 50px 40px; border-radius: 20px;">
            <h3 style="font-size: 1.8em; margin-bottom: 15px;">Be Part of the Dajot Family</h3>
            <p style="margin-bottom: 25px; font-size: 1.05em; opacity: 0.95;">Whether you're buying for your home or selling from your farm, we're here to grow together.</p>
            <a href="shop.php" class="btn" style="background: white; color: #2e7d32; margin: 5px;">Browse Products</a>
            <a href="vendor-register.php" class="btn" style="background: transparent; color: white; border: 2px solid white; margin: 5px;">Apply as Vendor</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
