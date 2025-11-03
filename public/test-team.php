<?php
// Test script to debug team member submission
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>Team Members Array:</h2>";
    if (isset($_POST['team_members'])) {
        echo "<pre>";
        print_r($_POST['team_members']);
        echo "</pre>";
    } else {
        echo "No team_members array received!";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Team Member Test</title>
</head>
<body>
    <h1>Test Team Member Submission</h1>
    
    <form method="POST" action="">
        <h3>Test 1: Normal hidden inputs</h3>
        <input type="hidden" name="team_members[]" value="1">
        <input type="hidden" name="team_members[]" value="2">
        <input type="hidden" name="team_members[]" value="3">
        
        <h3>Test 2: Dynamic inputs</h3>
        <div id="dynamic-inputs"></div>
        
        <button type="submit">Submit Form</button>
    </form>
    
    <script>
        // Add dynamic inputs
        const container = document.getElementById('dynamic-inputs');
        [4, 5, 6].forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'team_members[]';
            input.value = id;
            container.appendChild(input);
            console.log('Added input for ID:', id);
        });
        
        // Log all inputs before submit
        document.querySelector('form').addEventListener('submit', (e) => {
            const inputs = document.querySelectorAll('input[name="team_members[]"]');
            console.log('Submitting inputs:', Array.from(inputs).map(i => i.value));
        });
    </script>
</body>
</html>