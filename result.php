<?php
$pageTitle = "Evaluation Result";
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user_id'];
$evalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($evalId === 0) {
    header('Location: dashboard.php');
    exit();
}

$query = $conn->prepare("
    SELECT 
        e.*,
        r.filename,
        j.title as jd_title,
        j.content as jd_content
    FROM evaluations e
    JOIN resumes r ON e.resume_id = r.id
    JOIN job_descriptions j ON e.jd_id = j.id
    WHERE e.id = ? AND e.user_id = ?
");
$query->bind_param("ii", $evalId, $userId);
$query->execute();
$eval = $query->get_result()->fetch_assoc();

if (!$eval) {
    header('Location: dashboard.php');
    exit();
}

$matchedSkills = json_decode($eval['matched_skills'], true) ?? [];
$missingSkills = json_decode($eval['missing_skills'], true) ?? [];
$strengths     = json_decode($eval['strengths'], true) ?? [];
$suggestions   = json_decode($eval['suggestions'], true) ?? [];

function getScoreClass($score) {
    if ($score >= 70) return 'score-good';
    if ($score >= 40) return 'score-medium';
    return 'score-low';
}
?>

<div class="container">

    <div class="page-header">
        <div>
            <h2><i class="fas fa-chart-bar"></i> Evaluation Result</h2>
            <div class="result-meta">
                <span><i class="fas fa-file-pdf"></i> <?php echo htmlspecialchars($eval['filename']); ?></span>
                <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($eval['jd_title']); ?></span>
                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y, h:i A', strtotime($eval['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- Score Cards -->
    <div class="scores-grid">

        <div class="score-card score-card-main">
            <div class="score-label">Overall Match</div>
            <div class="score-circle <?php echo getScoreClass($eval['overall_score']); ?>">
                <?php echo $eval['overall_score']; ?>%
            </div>
            <div class="score-desc">Overall fit for this role</div>
        </div>

        <div class="score-card">
            <div class="score-label">Skills Match</div>
            <div class="score-circle <?php echo getScoreClass($eval['skills_score']); ?>">
                <?php echo $eval['skills_score']; ?>%
            </div>
            <div class="score-desc">Technical skills alignment</div>
        </div>

        <div class="score-card">
            <div class="score-label">Experience Match</div>
            <div class="score-circle <?php echo getScoreClass($eval['experience_score']); ?>">
                <?php echo $eval['experience_score']; ?>%
            </div>
            <div class="score-desc">Relevance of past experience</div>
        </div>

    </div>

    <!-- Skills Analysis -->
    <div class="two-col">

        <div class="card">
            <h3><i class="fas fa-circle-check"></i> Matched Skills</h3>
            <p class="card-subtitle">Skills found in both your resume and the JD</p>
            <?php if (!empty($matchedSkills)): ?>
                <div class="skills-list">
                    <?php foreach ($matchedSkills as $skill): ?>
                        <span class="skill-tag skill-matched">
                            <?php echo htmlspecialchars($skill); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No matching skills found.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-circle-xmark"></i> Missing Skills</h3>
            <p class="card-subtitle">Skills required by JD but not found in resume</p>
            <?php if (!empty($missingSkills)): ?>
                <div class="skills-list">
                    <?php foreach ($missingSkills as $skill): ?>
                        <span class="skill-tag skill-missing">
                            <?php echo htmlspecialchars($skill); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No missing skills — great match!</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Strengths -->
    <div class="card">
        <h3><i class="fas fa-dumbbell"></i> Strengths</h3>
        <p class="card-subtitle">What works well in your resume for this role</p>
        <?php if (!empty($strengths)): ?>
            <ul class="feedback-list">
                <?php foreach ($strengths as $strength): ?>
                    <li class="feedback-item feedback-positive">
                        <i class="fas fa-check"></i>
                        <?php echo htmlspecialchars($strength); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty-text">No strengths identified.</p>
        <?php endif; ?>
    </div>

    <!-- Suggestions -->
    <div class="card">
        <h3><i class="fas fa-bullseye"></i> Suggestions for Improvement</h3>
        <p class="card-subtitle">Specific actions to improve your resume for this role</p>
        <?php if (!empty($suggestions)): ?>
            <ul class="feedback-list">
                <?php foreach ($suggestions as $suggestion): ?>
                    <li class="feedback-item feedback-suggestion">
                        <i class="fas fa-lightbulb"></i>
                        <?php echo htmlspecialchars($suggestion); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty-text">No suggestions — excellent resume!</p>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="result-actions">
        <a href="upload.php" class="btn-primary">
            <i class="fas fa-rotate-right"></i> Evaluate Another Resume
        </a>
        <a href="history.php" class="btn-secondary">
            <i class="fas fa-clock-rotate-left"></i> View All Evaluations
        </a>
        <a href="dashboard.php" class="btn-secondary">
            <i class="fas fa-house"></i> Back to Dashboard
        </a>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>