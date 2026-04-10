<?php include 'includes/header.php'; ?>

<style>
/* Team Page Specific Styles */
.team-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 60px 0;
}

.team-header {
    text-align: center;
    margin-bottom: 50px;
}

.team-header h1 {
    font-size: 2.5em;
    color: #2e7d32;
    margin-bottom: 15px;
    font-weight: 700;
}

.team-header p {
    font-size: 1.1em;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.team-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}

.team-member-card {
    background: white;
    border-radius: 20px;
    padding: 30px 25px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.team-member-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    border-color: #2e7d32;
}

.team-member-photo {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 20px;
    border: 4px solid #e8f5e9;
    box-shadow: 0 5px 20px rgba(46, 125, 50, 0.15);
}

.team-member-card h3 {
    font-size: 1.3em;
    color: #333;
    margin-bottom: 8px;
    font-weight: 700;
}

.team-member-role {
    font-size: 0.95em;
    color: #2e7d32;
    font-weight: 600;
    margin-bottom: 0;
}

/* Mobile - 1 card per row */
@media (max-width: 640px) {
    .team-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .team-header h1 {
        font-size: 2em;
    }
    
    .team-member-photo {
        width: 120px;
        height: 120px;
    }
}

/* Tablet - 2 cards per row */
@media (min-width: 641px) and (max-width: 900px) {
    .team-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop - 3 cards per row */
@media (min-width: 901px) {
    .team-cards-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>

<section class="team-section">
    <div class="container">
        <div class="team-header">
            <h1>Meet Our Team</h1>
            <p>A family-led poultry business built on trust, experience, and quality.</p>
        </div>

        <div class="team-cards-grid">
            <!-- Buhari Dajot -->
            <div class="team-member-card">
                <img src="assets/images/buhari.jpeg" alt="Buhari Dajot" class="team-member-photo">
                <h3>Buhari Dajot</h3>
                <p class="team-member-role">Business Lead</p>
            </div>

            <!-- Hamza Dajot -->
            <div class="team-member-card">
                <img src="assets/images/hamza.jpeg" alt="Hamza Dajot" class="team-member-photo">
                <h3>Hamza Dajot</h3>
                <p class="team-member-role">Operations Lead</p>
            </div>

            <!-- Abdul Karim Dajot -->
            <div class="team-member-card">
                <img src="assets/images/Duwa.png" alt="Abdul Karim Dajot" class="team-member-photo">
                <h3>Abdul Karim Dajot</h3>
                <p class="team-member-role">Quality Lead</p>
            </div>

            <!-- Ismail Dajot -->
            <div class="team-member-card">
                <img src="assets/images/ismail.jpeg" alt="Ismail Dajot" class="team-member-photo">
                <h3>Ismail Dajot</h3>
                <p class="team-member-role">Transport Lead</p>
            </div>

            <!-- Habeeba Shuaibu Dajot -->
            <div class="team-member-card">
                <img src="assets/images/Beeeba.png" alt="Habeeba Shuaibu Dajot" class="team-member-photo">
                <h3>Habeeba Shuaibu Dajot</h3>
                <p class="team-member-role">Digital & Systems Lead</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
