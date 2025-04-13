<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Analyzer for Job Matching</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Resume Analyzer Pro</h1>
            <nav>
                <button class="nav-btn active" data-tab="eligibility">Eligibility Check</button>
                <button class="nav-btn" data-tab="upload">Resume Analysis</button>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="eligibility" class="tab-content active">
            <h2>Job Eligibility Checker</h2>
            <form id="eligibilityForm" action="process.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Required Skills:</label>
                    <textarea name="required_skills" placeholder="Enter skills (comma-separated) e.g., html, css, javascript" required></textarea>
                </div>
                <div class="form-group">
                    <label>Upload Resume (PDF):</label>
                    <input type="file" name="resume" accept=".pdf" required>
                    <small>PDF files only (max 5MB)</small>
                </div>
                <button type="submit" class="submit-btn">Analyze Eligibility</button>
            </form>
        </section>

        <section id="upload" class="tab-content">
            <h2>Comprehensive Resume Analysis</h2>
            <form id="uploadForm" action="process.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Upload Resume (PDF):</label>
                    <input type="file" name="resume" accept=".pdf" required>
                    <small>PDF files only (max 5MB)</small>
                </div>
                <button type="submit" class="submit-btn">Analyze & Match Jobs</button>
            </form>
            <div class="feature-box">
                <h3>Advanced Features</h3>
                <ul>
                    <li>Accurate skill detection with confidence scores</li>
                    <li>Job matching with detailed analysis</li>
                    <li>Skill gap identification</li>
                    <li>Interactive results visualization</li>
                </ul>
            </div>
        </section>

        <section id="results" class="results hidden">
            <div class="loader">Analyzing your resume...</div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Resume Analyzer Pro. All rights reserved.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>