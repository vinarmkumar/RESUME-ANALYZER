<?php
require_once 'vendor/autoload.php';
require_once 'lib/SkillMatcher.php';

use Smalot\PdfParser\Parser;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type
header('Content-Type: application/json');

// Configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Log file for debugging
$log_file = 'debug.log';
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

try {
    // Ensure upload directory exists
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
        log_message("Created upload directory");
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('No resume file uploaded');
    }

    $file = $_FILES['resume'];
    log_message("File received: " . json_encode($file));

    // Validate file
    if ($file['type'] !== 'application/pdf') {
        throw new Exception('Only PDF files are supported');
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds 5MB limit');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) { // Fixed from UPLOAD_OKAY to UPLOAD_ERR_OK
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }

    // Move file
    $filename = uniqid('resume_') . '.pdf';
    $filepath = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }
    log_message("File moved to: " . $filepath);

    // Parse PDF
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($filepath);
        $text = $pdf->getText();
        log_message("PDF parsed successfully, text length: " . strlen($text));
    } catch (Exception $e) {
        throw new Exception('PDF parsing failed: ' . $e->getMessage());
    } finally {
        // Clean up
        if (file_exists($filepath)) {
            unlink($filepath);
            log_message("Temporary file deleted");
        }
    }

    if (empty($text)) {
        throw new Exception('No readable text found in PDF');
    }

    // Initialize SkillMatcher
    $matcher = new SkillMatcher();
    $extracted_skills = $matcher->extractSkills($text);
    log_message("Skills extracted: " . json_encode($extracted_skills));

    $response = [
        'success' => true,
        'extracted_skills' => $extracted_skills
    ];

    // Eligibility check
    if (isset($_POST['required_skills']) && !empty($_POST['required_skills'])) {
        $required_skills = array_filter(array_map('trim', explode(',', strtolower($_POST['required_skills']))));
        $eligibility = $matcher->checkEligibility($extracted_skills, $required_skills);
        $response['eligibility'] = $eligibility;
        log_message("Eligibility checked: " . json_encode($eligibility));
    }

    // Job matching
    $job_matches = $matcher->matchJobs($extracted_skills);
    $response['job_matches'] = $job_matches;
    log_message("Job matches found: " . count($job_matches));

    echo json_encode($response);

} catch (Exception $e) {
    log_message("Error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => 'Check debug.log for more details'
    ]);
}