<?php
session_start();

// If already logged in skip landing page
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Evaluator — AI Powered Resume Analysis</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="landing-page">

    <!-- ============================================
         NAVBAR
         Simple top bar with logo + login/register
         ============================================ -->
    <nav class="landing-nav">
        <div class="landing-nav-inner">
            <div class="nav-brand">
                <i class="fas fa-file-alt"></i> Resume Evaluator
            </div>
            <div class="landing-nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How it Works</a>
                <a href="login.php" class="btn-nav-login">Login</a>
                <a href="register.php" class="btn-nav-register">Get Started Free</a>
            </div>
        </div>
    </nav>

    <!-- ============================================
         HERO SECTION
         First thing visitor sees — must be impactful
         ============================================ -->
    <section class="hero">
        <div class="hero-inner">

            <div class="hero-badge">
                <i class="fas fa-bolt"></i> Powered by Google Gemini AI
            </div>

            <h1 class="hero-title">
                Get Your Resume<br>
                <span class="hero-highlight">AI Evaluated</span><br>
                in Seconds
            </h1>

            <p class="hero-subtitle">
                Upload your resume, paste a job description and get instant 
                AI-powered feedback — scores, skill gaps, strengths and 
                actionable suggestions to land your dream job.
            </p>

            <div class="hero-actions">
                <a href="register.php" class="btn-hero-primary">
                    <i class="fas fa-rocket"></i> Start for Free
                </a>
                <a href="login.php" class="btn-hero-secondary">
                    <i class="fas fa-arrow-right-to-bracket"></i> Login
                </a>
            </div>

            <!-- Social proof -->
            <div class="hero-proof">
                <div class="proof-item">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <span>AI Powered Analysis</span>
                </div>
                <div class="proof-divider">•</div>
                <div class="proof-item">
                    <i class="fas fa-shield-halved"></i>
                    <span>Secure & Private</span>
                </div>
                <div class="proof-divider">•</div>
                <div class="proof-item">
                    <i class="fas fa-bolt"></i>
                    <span>Results in Seconds</span>
                </div>
            </div>

        </div>
    </section>

    <!-- ============================================
         HOW IT WORKS SECTION
         3 simple steps
         ============================================ -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-container">

            <div class="section-label">Simple Process</div>
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">
                Get detailed resume feedback in three simple steps
            </p>

            <div class="steps-grid">

                <div class="step-card">
                    <div class="step-number">01</div>
                    <div class="step-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>Upload Resume</h3>
                    <p>Upload your resume as a PDF file. Our system securely processes and extracts all content from it.</p>
                </div>

                <div class="step-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div class="step-card">
                    <div class="step-number">02</div>
                    <div class="step-icon">
                        <i class="fas fa-paste"></i>
                    </div>
                    <h3>Paste Job Description</h3>
                    <p>Copy and paste the job description from LinkedIn, Naukri, or any job portal directly into the tool.</p>
                </div>

                <div class="step-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div class="step-card">
                    <div class="step-number">03</div>
                    <div class="step-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Get AI Feedback</h3>
                    <p>Receive detailed scores, matched skills, missing skills, strengths and improvement suggestions instantly.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================
         FEATURES SECTION
         ============================================ -->
    <section class="features" id="features">
        <div class="section-container">

            <div class="section-label">What You Get</div>
            <h2 class="section-title">Everything You Need</h2>
            <p class="section-subtitle">
                Comprehensive resume analysis to help you stand out
            </p>

            <div class="features-grid">

                <div class="feature-card">
                    <div class="feature-icon feature-icon-blue">
                        <i class="fas fa-percent"></i>
                    </div>
                    <h3>Match Score</h3>
                    <p>Get an overall match percentage showing how well your resume fits the job description.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-green">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <h3>Skill Analysis</h3>
                    <p>See exactly which skills you have that match the JD and which ones you're missing.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-purple">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Strengths</h3>
                    <p>Understand what makes your resume strong for this specific role and company.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-orange">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Suggestions</h3>
                    <p>Get specific, actionable tips to improve your resume and increase your chances.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-red">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <h3>History</h3>
                    <p>Track all your past evaluations and compare how different resumes perform.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-teal">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h3>Secure & Private</h3>
                    <p>Your resume data is private to your account and never shared with anyone.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================
         CTA SECTION
         Final push to register
         ============================================ -->
    <section class="cta-section">
        <div class="section-container">
            <h2>Ready to Evaluate Your Resume?</h2>
            <p>Join and get AI-powered feedback on your resume today — completely free.</p>
            <a href="register.php" class="btn-hero-primary">
                <i class="fas fa-rocket"></i> Get Started Free
            </a>
        </div>
    </section>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="landing-footer">
        <div class="landing-footer-inner">
            <div class="footer-brand">
                <i class="fas fa-file-alt"></i> Resume Evaluator
            </div>
            <p class="footer-tagline">AI-powered resume analysis using Google Gemini</p>
            <div class="footer-links">
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="#features">Features</a>
                <a href="#how-it-works">How it Works</a>
            </div>
            <p class="footer-copy">© <?php echo date('Y'); ?> Resume Evaluator. Built with PHP + Google Gemini AI.</p>
        </div>
    </footer>

</body>
</html>