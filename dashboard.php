<?php
// Auth Guard — must be at top of every
$pageTitle = "Dashboard";
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Query 1 — Total resumes uploaded by user
$resumeQuery = $conn->prepare("SELECT COUNT(*) as total FROM resumes WHERE user_id = ?");
$resumeQuery->bind_param("i", $userId);

$resumeQuery->execute();
$resumeResult = $resumeQuery->get_result()->fetch_assoc();
$totalResumes = $resumeResult['total'];

// Query 2 — Total evaluations done by user
$evalQuery = $conn->prepare("SELECT COUNT(*) as total FROM evaluations WHERE user_id = ?");
$evalQuery->bind_param("i", $userId);
$evalQuery->execute();
$evalResult = $evalQuery->get_result()->fetch_assoc();
$totalEvals = $evalResult['total'];

// Query 3 — Average score across all evaluations
$avgQuery = $conn->prepare("SELECT AVG(overall_score) as avg_score FROM evaluations WHERE user_id = ?");
$avgQuery->bind_param("i", $userId);
$avgQuery->execute();
$avgResult = $avgQuery->get_result()->fetch_assoc();

$avgScore = round($avgResult['avg_score'] ?? 0);

// Query 4 — Last 5 evaluations with
// resume filename and JD title
$recentQuery = $conn->prepare("
    SELECT 
        e.id,
        e.overall_score,
        e.created_at,
        r.filename,
        j.title as jd_title
    FROM evaluations e
    JOIN resumes r ON e.resume_id = r.id
    JOIN job_descriptions j ON e.jd_id = j.id
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
    LIMIT 5
");
// ORDER BY created_at DESC = newest first
// LIMIT 5 = only last 5 records
$recentQuery->bind_param("i", $userId);
$recentQuery->execute();
$recentEvals = $recentQuery->get_result();
?>


<div class="container">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h2>Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋</h2>
        <p>Here's your resume evaluation overview</p>
        <a href="upload.php" class="btn-primary">+ New Evaluation</a>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-pdf"></i></div>
            <div class="stat-number"><?php echo $totalResumes; ?></div>
            <div class="stat-label">Resumes Uploaded</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-magnifying-glass"></i></div>
            <div class="stat-number"><?php echo $totalEvals; ?></div>
            <div class="stat-label">Evaluations Done</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-number"><?php echo $avgScore; ?>%</div>
            <div class="stat-label">Average Score</div>
        </div>

    </div>

    <!-- Recent Evaluations Table -->
    <div class="section">
        <div class="section-header">
            <h3>Recent Evaluations</h3>
            <a href="history.php">View All →</a>
        </div>

        <?php if ($recentEvals->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Resume</th>
                    <th>Job Description</th>
                    <th>Score</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($eval = $recentEvals->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($eval['filename']); ?></td>
                    <td><?php echo htmlspecialchars($eval['jd_title']); ?></td>
                    <td>
                        <!-- Color code the score -->
                        <span class="score-badge <?php
                            if ($eval['overall_score'] >= 70) echo 'score-good';
                            elseif ($eval['overall_score'] >= 40) echo 'score-medium';
                            else echo 'score-low';
                        ?>">
                            <?php echo $eval['overall_score']; ?>%
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($eval['created_at'])); ?></td>
                    <td>
                        <a href="result.php?id=<?php echo $eval['id']; ?>" class="btn-small">
                            View
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div class="empty-state">
            <p>🎯 No evaluations yet!</p>
            <p>Upload your resume and a job description to get started.</p>
            <a href="upload.php" class="btn-primary">Start Your First Evaluation</a>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>