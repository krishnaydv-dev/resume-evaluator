<?php
session_start();

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// This page should only be accessed via POST
// from upload.php — never directly via browser
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload.php');
    exit();
}

require_once 'config/db.php';
require_once 'vendor/autoload.php';
// autoload.php loads all composer packages
// including smalot/pdfparser automatically

use Smalot\PdfParser\Parser;

$userId = $_SESSION['user_id'];

// STEP 1 — Validate uploaded file
// $_FILES holds all uploaded file data
// $_FILES['resume']['error'] = 0 means no error
// ============================================

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== 0) {
    die("Error: No file uploaded or upload failed.");
}

$file = $_FILES['resume'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);

if ($mimeType !== 'application/pdf') {
    die("Error: Only PDF files are allowed.");
}

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    die("Error: File size must be less than 5MB.");
}

// STEP 2 — Save PDF to /uploads folder

$uniqueName = uniqid('resume_', true) . '.pdf';
// uniqid() generates unique ID like: resume_6634ab12345.pdf

$uploadPath = __DIR__ . '/uploads/' . $uniqueName;
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    die("Error: Failed to save uploaded file.");
}

// STEP 3 — Extract text from PDF

try {
    $parser = new Parser();
    $pdf = $parser->parseFile($uploadPath);
    $resumeText = $pdf->getText();

    // Clean up the text
    $resumeText = trim($resumeText);

    if (empty($resumeText)) {
        die("Error: Could not extract text from PDF. 
             Make sure your PDF is not a scanned image.");
    }

} catch (Exception $e) {
    die("Error parsing PDF: " . $e->getMessage());
}

// STEP 4 — Get Job Description

$savedJdId = isset($_POST['saved_jd']) ? (int)$_POST['saved_jd'] : 0;

$jdTitle   = trim($_POST['jd_title'] ?? '');
$jdContent = trim($_POST['jd_content'] ?? '');

if ($savedJdId > 0) {
    // User selected a saved JD — fetch from database
    $jdQuery = $conn->prepare(
        "SELECT id, title, content FROM job_descriptions 
         WHERE id = ? AND user_id = ?"
    );
    $jdQuery->bind_param("ii", $savedJdId, $userId);
    $jdQuery->execute();
    $jdData = $jdQuery->get_result()->fetch_assoc();

    if (!$jdData) {
        die("Error: Job description not found.");
    }

    $jdId      = $jdData['id'];
    $jdTitle   = $jdData['title'];
    $jdContent = $jdData['content'];

} else {
    // User typed a new JD — validate and save it
    if (empty($jdTitle) || empty($jdContent)) {
        die("Error: Please provide a job title and description.");
    }

    // Save new JD to database for future reuse
    $jdInsert = $conn->prepare(
        "INSERT INTO job_descriptions (user_id, title, content) 
         VALUES (?, ?, ?)"
    );
    $jdInsert->bind_param("iss", $userId, $jdTitle, $jdContent);
    $jdInsert->execute();
    $jdId = $conn->insert_id;
}

// STEP 5 — Save resume record to database

$resumeInsert = $conn->prepare(
    "INSERT INTO resumes (user_id, filename, filepath) 
     VALUES (?, ?, ?)"
);
$originalName = htmlspecialchars($file['name']);
$resumeInsert->bind_param("iss", $userId, $originalName, $uniqueName);
$resumeInsert->execute();
$resumeId = $conn->insert_id;

// STEP 6 — Build Gemini API Prompt

$prompt = "
You are an expert HR recruiter and resume evaluator with 10+ years of experience.

Analyze the following resume against the job description and return a JSON object.

RESUME:
$resumeText

JOB DESCRIPTION:
$jdContent

Return ONLY a valid JSON object with exactly this structure, no extra text:
{
    \"overall_score\": <integer 0-100>,
    \"skills_score\": <integer 0-100>,
    \"experience_score\": <integer 0-100>,
    \"matched_skills\": [<list of skills present in both resume and JD>],
    \"missing_skills\": [<list of skills required in JD but missing from resume>],
    \"strengths\": [<list of 3-5 strong points of this resume for this role>],
    \"suggestions\": [<list of 3-5 specific improvements the candidate should make>]
}

Scoring guide:
- overall_score: holistic fit for the role
- skills_score: technical skills match percentage  
- experience_score: years and relevance of experience

Be specific and actionable in strengths and suggestions.
";

// STEP 7 — Call Gemini API
// We use PHP's cURL to make HTTP POST request
// to Google's Gemini API endpoint

$apiKey = GEMINI_API_KEY;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$requestBody = json_encode([
    "contents" => [[
        "parts" => [["text" => $prompt]]
    ]],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "temperature" => 0.3
        
    ]
]);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);


$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if ($response === false) {
    die("Error: Failed to connect to Gemini API.");
}

// Check for API errors
if ($httpCode !== 200) {
    die("Error: Gemini API returned status $httpCode. Response: $response");
}


// STEP 8 — Parse Gemini Response


$responseData = json_decode($response, true);

// Extract the text content from Gemini's response
$evaluationJson = $responseData['candidates'][0]['content']['parts'][0]['text'];
$evaluation = json_decode($evaluationJson, true);

if (!$evaluation) {
    die("Error: Could not parse AI response. Please try again.");
}


// STEP 9 — Save Evaluation to Database

$matchedSkills  = json_encode($evaluation['matched_skills'] ?? []);
$missingSkills  = json_encode($evaluation['missing_skills'] ?? []);
$strengths      = json_encode($evaluation['strengths'] ?? []);
$suggestions    = json_encode($evaluation['suggestions'] ?? []);
$overallScore   = (int)($evaluation['overall_score'] ?? 0);
$skillsScore    = (int)($evaluation['skills_score'] ?? 0);
$experienceScore = (int)($evaluation['experience_score'] ?? 0);

$evalInsert = $conn->prepare("
    INSERT INTO evaluations 
    (user_id, resume_id, jd_id, overall_score, skills_score, 
     experience_score, matched_skills, missing_skills, strengths, suggestions)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$evalInsert->bind_param(
    "iiiiiissss",
    $userId,
    $resumeId,
    $jdId,
    $overallScore,
    $skillsScore,
    $experienceScore,
    $matchedSkills,
    $missingSkills,
    $strengths,
    $suggestions
);

$evalInsert->execute();
$evaluationId = $conn->insert_id;

// STEP 10 — Redirect to result page

header("Location: result.php?id=" . $evaluationId);
exit();
?>