<?php
class SkillMatcher {
    private $skill_database = [
        'html', 'css', 'javascript', 'php', 'python', 'java', 'mysql', 'react',
        'laravel', 'node.js', 'angular', 'vue.js', 'django', 'git', 'docker',
        'aws', 'typescript', 'sql', 'mongodb', 'redux', 'graphql'
    ];

    private $job_database = [
        [
            'title' => 'Frontend Developer',
            'skills' => ['html', 'css', 'javascript', 'react', 'typescript'],
            'description' => 'Building responsive web interfaces'
        ],
        [
            'title' => 'Backend Developer',
            'skills' => ['php', 'mysql', 'laravel', 'python', 'node.js'],
            'description' => 'Server-side development and APIs'
        ],
        [
            'title' => 'Full Stack Developer',
            'skills' => ['html', 'css', 'javascript', 'php', 'mysql', 'react'],
            'description' => 'End-to-end web application development'
        ]
    ];

    public function extractSkills($text) {
        $text_lower = strtolower($text);
        $skills_found = [];

        foreach ($this->skill_database as $skill) {
            $pattern = '/\b' . preg_quote($skill, '/') . '\b/i';
            $count = preg_match_all($pattern, $text, $matches);
            
            if ($count > 0) {
                $confidence = min(1.0, $count * 0.2 + 0.6); // Confidence scoring
                $skills_found[$skill] = [
                    'name' => $skill,
                    'confidence' => round($confidence, 2),
                    'count' => $count
                ];
            }
        }

        return $skills_found;
    }

    public function checkEligibility($extracted_skills, $required_skills) {
        $skill_names = array_keys($extracted_skills);
        $matching_skills = array_intersect($required_skills, $skill_names);
        $missing_skills = array_diff($required_skills, $skill_names);
        
        $match_percentage = count($required_skills) > 0 
            ? (count($matching_skills) / count($required_skills)) * 100 
            : 0;

        return [
            'match_percentage' => round($match_percentage, 2),
            'matching_skills' => array_values($matching_skills),
            'missing_skills' => array_values($missing_skills),
            'is_eligible' => $match_percentage >= 70
        ];
    }

    public function matchJobs($extracted_skills) {
        $skill_names = array_keys($extracted_skills);
        $matches = [];

        foreach ($this->job_database as $job) {
            $matched_skills = array_intersect($job['skills'], $skill_names);
            $match_percentage = count($job['skills']) > 0 
                ? (count($matched_skills) / count($job['skills'])) * 100 
                : 0;

            if ($match_percentage >= 50) {
                $matches[] = [
                    'title' => $job['title'],
                    'description' => $job['description'],
                    'match_percentage' => round($match_percentage, 2),
                    'required_skills' => $job['skills'],
                    'matched_skills' => array_values($matched_skills)
                ];
            }
        }

        usort($matches, function($a, $b) {
            return $b['match_percentage'] <=> $a['match_percentage'];
        });

        return $matches;
    }
}