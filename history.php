<?php
$pageTitle = "Evaluation History";
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/db.php';
$userId = $_SESSION['user_id'];

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $deleteId = (int)$_POST['eval_id'];
    $deleteStmt = $conn->prepare(
        "DELETE FROM evaluations WHERE id = ? AND user_id = ?"
    );
    $deleteStmt->bind_param("ii", $deleteId, $userId);
    $deleteStmt->execute();
    header('Location: history.php?deleted=1');
    exit();
}

// Fetch all evaluations
$query = $conn->prepare("
    SELECT
        e.id,
        e.overall_score,
        e.skills_score,
        e.experience_score,
        e.created_at,
        r.filename,
        j.title as jd_title
    FROM evaluations e
    JOIN resumes r ON e.resume_id = r.id
    JOIN job_descriptions j ON e.jd_id = j.id
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
");
$query->bind_param("i", $userId);
$query->execute();
$evaluations = $query->get_result();
?>

<div class="container">

    <div class="page-header">
        <div>
            <h2><i class="fas fa-clock-rotate-left"></i> Evaluation History</h2>
            <p>All your past resume evaluations</p>
        </div>
        <a href="upload.php" class="btn-primary">
            <i class="fas fa-plus"></i> New Evaluation
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-circle-check"></i> Evaluation deleted successfully.
        </div>
    <?php endif; ?>

    <?php if ($evaluations->num_rows > 0): ?>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><i class="fas fa-file-pdf"></i> Resume</th>
                    <th><i class="fas fa-briefcase"></i> Job Description</th>
                    <th><i class="fas fa-star"></i> Overall</th>
                    <th><i class="fas fa-code"></i> Skills</th>
                    <th><i class="fas fa-clock"></i> Experience</th>
                    <th><i class="fas fa-calendar"></i> Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                while ($eval = $evaluations->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($eval['filename']); ?></td>
                    <td><?php echo htmlspecialchars($eval['jd_title']); ?></td>

                    <td>
                        <span class="score-badge <?php
                            if ($eval['overall_score'] >= 70) echo 'score-good';
                            elseif ($eval['overall_score'] >= 40) echo 'score-medium';
                            else echo 'score-low';
                        ?>">
                            <?php echo $eval['overall_score']; ?>%
                        </span>
                    </td>

                    <td>
                        <span class="score-badge <?php
                            if ($eval['skills_score'] >= 70) echo 'score-good';
                            elseif ($eval['skills_score'] >= 40) echo 'score-medium';
                            else echo 'score-low';
                        ?>">
                            <?php echo $eval['skills_score']; ?>%
                        </span>
                    </td>

                    <td>
                        <span class="score-badge <?php
                            if ($eval['experience_score'] >= 70) echo 'score-good';
                            elseif ($eval['experience_score'] >= 40) echo 'score-medium';
                            else echo 'score-low';
                        ?>">
                            <?php echo $eval['experience_score']; ?>%
                        </span>
                    </td>

                    <td><?php echo date('d M Y', strtotime($eval['created_at'])); ?></td>

                    <td class="action-buttons">
                        <a href="result.php?id=<?php echo $eval['id']; ?>"
                           class="btn-small btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>

                        <form method="POST" action="history.php"
                              style="display:inline"
                              onsubmit="return confirm('Are you sure you want to delete this evaluation?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="eval_id" value="<?php echo $eval['id']; ?>">
                            <button type="submit" class="btn-small btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-folder-open" style="font-size:3rem;color:#d1d5db;display:block;margin-bottom:16px"></i>
        <p>No evaluations yet!</p>
        <p>Upload your resume and a job description to get started.</p>
        <a href="upload.php" class="btn-primary" style="width:auto;margin-top:20px">
            <i class="fas fa-plus"></i> Start Your First Evaluation
        </a>
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>