<?php
$pageTitle = "New Evaluation";
require_once 'includes/header.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user_id'];
$error = '';

// Fetch user's previously saved JDs
$jdQuery = $conn->prepare(
    "SELECT id, title FROM job_descriptions 
     WHERE user_id = ? 
     ORDER BY created_at DESC"
);
$jdQuery->bind_param("i", $userId);
$jdQuery->execute();
$savedJDs = $jdQuery->get_result();
?>

<div class="container">

    <div class="page-header">
        <h2>📤 New Evaluation</h2>
        <p>Upload your resume and provide a job description to get AI-powered feedback</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="evaluate.php" method="POST" enctype="multipart/form-data" id="uploadForm">

        <!-- SECTION 1: Resume Upload -->
        <div class="card">
            <h3>Step 1 — Upload Resume</h3>
            <p>PDF format only, maximum 5MB</p>

            <div class="upload-area" id="uploadArea">
                <input
                    type="file"
                    name="resume"
                    id="resumeFile"
                    accept=".pdf"
                    required
                    style="display:none"
                >
                <label for="resumeFile" class="upload-label">
                    <div class="upload-icon">📄</div>
                    <div id="uploadText">
                        <strong>Click to upload</strong> or drag and drop
                        <br>PDF files only
                    </div>
                </label>
            </div>

            <!-- Shows selected filename after user picks file -->
            <div id="fileSelected" style="display:none" class="file-selected">
                ✅ Selected: <span id="fileName"></span>
            </div>
        </div>

        <!-- SECTION 2: Job Description -->
        <div class="card">
            <h3>Step 2 — Job Description</h3>

            <div class="form-group">
                <label for="jd_title">Job Title</label>
                <input
                    type="text"
                    name="jd_title"
                    id="jd_title"
                    placeholder="e.g. Software Engineer at Google"
                    required
                >
            </div>

            <!-- ============================================
                 Toggle between saved JDs and new JD
                 ============================================ -->
            <?php if ($savedJDs->num_rows > 0): ?>
            <div class="form-group">
                <label for="saved_jd">Use a Saved Job Description (optional)</label>
                <select name="saved_jd" id="saved_jd">
                    <option value="">-- Type a new one below --</option>
                    <?php while ($jd = $savedJDs->fetch_assoc()): ?>
                        <option value="<?php echo $jd['id']; ?>">
                            <?php echo htmlspecialchars($jd['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <small>Selecting a saved JD will use that instead of what you type below</small>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="jd_content">Job Description</label>
                <textarea
                    name="jd_content"
                    id="jd_content"
                    rows="10"
                    placeholder="Paste the full job description here...
                    
Example:
We are looking for a Software Engineer with:
- 3+ years of experience in Python
- Strong knowledge of REST APIs
- Experience with MySQL or PostgreSQL
..."
                ></textarea>
                <small>
                    Required if you're not selecting a saved JD above
                </small>
            </div>
        </div>

        <!-- SECTION 3: Submit -->
        <div class="card">
            <button type="submit" class="btn-primary btn-large" id="submitBtn">
                🚀 Evaluate Resume
            </button>
            <p class="hint">
                This may take 10-15 seconds while AI analyzes your resume
            </p>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>