<?php
$pageTitle = "Evaluation Result";
require_once 'includes/header.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user_id'];

// Get evaluation ID from URL

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

// Decode JSON strings back to PHP arrays

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

    <!-- Page Header -->
    <div class="page-header">
        <h2>📊 Evaluation Result</h2>
        <div class="result-meta">
            <span>📄 <?php echo htmlspecialchars($eval['filename']); ?></span>
            <span>💼 <?php echo htmlspecialchars($eval['jd_title']); ?></span>
            <span>📅 <?php echo date('d M Y, h:i A', strtotime($eval['created_at'])); ?></span>
        </div>
    </div>

    <!-- SCORE CARDS-->
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

    <!--SKILLS ANALYSIS-->
    <div class="two-col">

        <div class="card">
            <h3>✅ Matched Skills</h3>
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
            <h3>❌ Missing Skills</h3>
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

    <!--STRENGTHS -->
    <div class="card">
        <h3>💪 Strengths</h3>
        <p class="card-subtitle">What works well in your resume for this role</p>
        <?php if (!empty($strengths)): ?>
            <ul class="feedback-list">
                <?php foreach ($strengths as $strength): ?>
                    <li class="feedback-item feedback-positive">
                        <?php echo htmlspecialchars($strength); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty-text">No strengths identified.</p>
        <?php endif; ?>
    </div>

    <!--SUGGESTIONS-->
    <div class="card">
        <h3>🎯 Suggestions for Improvement</h3>
        <p class="card-subtitle">Specific actions to improve your resume for this role</p>
        <?php if (!empty($suggestions)): ?>
            <ul class="feedback-list">
                <?php foreach ($suggestions as $suggestion): ?>
                    <li class="feedback-item feedback-suggestion">
                        <?php echo htmlspecialchars($suggestion); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty-text">No suggestions — excellent resume!</p>
        <?php endif; ?>
    </div>

    <!--ACTION BUTTONS-->
    <div class="result-actions">
        <a href="upload.php" class="btn-primary">🔄 Evaluate Another Resume</a>
        <a href="history.php" class="btn-secondary">📋 View All Evaluations</a>
        <a href="dashboard.php" class="btn-secondary">🏠 Back to Dashboard</a>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>