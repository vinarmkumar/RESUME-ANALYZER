document.addEventListener('DOMContentLoaded', () => {
  const navButtons = document.querySelectorAll('.nav-btn');
  const tabContents = document.querySelectorAll('.tab-content');
  const forms = document.querySelectorAll('form');
  const resultsSection = document.getElementById('results');

  // Tab switching
  navButtons.forEach(button => {
      button.addEventListener('click', () => {
          navButtons.forEach(btn => btn.classList.remove('active'));
          tabContents.forEach(content => content.classList.remove('active'));
          
          button.classList.add('active');
          document.getElementById(button.dataset.tab).classList.add('active');
          resultsSection.classList.add('hidden');
      });
  });

  // Form submission
  forms.forEach(form => {
      form.addEventListener('submit', async (e) => {
          e.preventDefault();
          resultsSection.innerHTML = '<div class="loader">Analyzing your resume...</div>';
          resultsSection.classList.remove('hidden');

          const formData = new FormData(form);
          
          try {
              const response = await fetch('process.php', {
                  method: 'POST',
                  body: formData
              });

              const text = await response.text(); // Get raw response first
              console.log('Raw response:', text); // Log for debugging

              let data;
              try {
                  data = JSON.parse(text);
              } catch (jsonError) {
                  throw new Error('Invalid JSON response: ' + jsonError.message + '\nRaw response: ' + text);
              }

              if (!data.success) {
                  throw new Error(data.error || 'Unknown error occurred');
              }

              let html = '<h2>Analysis Results</h2>';

              // Skills
              html += '<h3>Extracted Skills</h3>';
              html += '<div class="skill-grid">';
              for (const [skill, info] of Object.entries(data.extracted_skills)) {
                  html += `
                      <div class="skill-card">
                          <strong>${skill}</strong>
                          <span>Confidence: ${info.confidence * 100}%</span>
                          <span>Mentions: ${info.count}</span>
                      </div>`;
              }
              html += '</div>';

              // Eligibility
              if (data.eligibility) {
                  html += '<h3>Eligibility Analysis</h3>';
                  html += `<div class="eligibility ${data.eligibility.is_eligible ? 'eligible' : 'not-eligible'}">`;
                  html += `<p>${data.eligibility.is_eligible ? '✅ You\'re Eligible!' : '❌ Not Quite There'}</p>`;
                  html += `<p>Match Percentage: ${data.eligibility.match_percentage}%</p>`;
                  html += `<div class="match-bar"><div class="match-fill" style="width: ${data.eligibility.match_percentage}%"></div></div>`;
                  
                  if (data.eligibility.matching_skills.length) {
                      html += '<p>Matching Skills:</p><div class="skill-grid">';
                      data.eligibility.matching_skills.forEach(skill => {
                          html += `<div class="skill-card">${skill}</div>`;
                      });
                      html += '</div>';
                  }
                  
                  if (data.eligibility.missing_skills.length) {
                      html += '<p>Missing Skills:</p><div class="skill-grid">';
                      data.eligibility.missing_skills.forEach(skill => {
                          html += `<div class="skill-card">${skill}</div>`;
                      });
                      html += '</div>';
                  }
                  html += '</div>';
              }

              // Job Matches
              if (data.job_matches.length) {
                  html += '<h3>Recommended Jobs</h3>';
                  html += '<div class="job-grid">';
                  data.job_matches.forEach(job => {
                      html += `
                          <div class="job-card">
                              <h4>${job.title}</h4>
                              <p>${job.description}</p>
                              <p>Match: ${job.match_percentage}%</p>
                              <div class="match-bar">
                                  <div class="match-fill" style="width: ${job.match_percentage}%"></div>
                              </div>
                              <p>Matched Skills: ${job.matched_skills.join(', ')}</p>
                          </div>`;
                  });
                  html += '</div>';
              }

              resultsSection.innerHTML = html;

          } catch (error) {
              resultsSection.innerHTML = `
                  <h2>Error</h2>
                  <p class="error">Failed to process resume: ${error.message}</p>
                  <p>Please check the following:</p>
                  <ul>
                      <li>Ensure your PDF contains selectable text (not just images)</li>
                      <li>File size is under 5MB</li>
                      <li>Server is running correctly</li>
                      <li>Check debug.log in the project folder for more details</li>
                  </ul>`;
              console.error('Error details:', error);
          }
      });
  });
});