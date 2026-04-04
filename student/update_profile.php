<?php
// Include the auth logic
require_once '../includes/auth.php';

// Enforce that only 'student' role can be here
require_role('student');

// If the profile is already completed, redirect to the dashboard so they don't see this again
if (isset($_SESSION['profile_completed']) && $_SESSION['profile_completed'] == 1) {
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/header.php';
?>

<style>
    .auth-container { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        min-height: calc(100vh - 80px); 
        padding: 40px 20px;
    }
    .auth-card { 
        background: white; 
        padding: 40px; 
        border-radius: 12px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        width: 100%; 
        max-width: 500px; 
        border: 1px solid #e2e8f0;
    }
    .auth-card h2 { text-align: center; margin-bottom: 10px; color: #1a202c; font-size: 28px; font-weight: 700; }
    .auth-card p.subtitle { text-align: center; color: #718096; margin-bottom: 30px; }
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #4a5568; font-weight: 600; font-size: 14px; }
    .form-group select, .form-group input { 
        width: 100%; 
        padding: 12px 15px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        transition: border-color 0.3s, box-shadow 0.3s; 
        background-color: white;
    }
    .form-group select:focus, .form-group input:focus { 
        outline: none; 
        border-color: #0056b3; 
        box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1); 
    }
    
    .btn-submit { 
        width: 100%; 
        padding: 14px; 
        background: #10b981; 
        color: white; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-size: 16px; 
        font-weight: 600; 
        font-family: 'Outfit', sans-serif;
        transition: background 0.3s, transform 0.2s; 
        margin-top: 10px;
    }
    .btn-submit:hover { background: #059669; transform: translateY(-1px); }
</style>

<div class="auth-container">
    <div class="auth-card animate__animated animate__zoomIn animate__fast">
        <h2>Complete Your Profile</h2>
        <p class="subtitle">Please provide your academic details before continuing.</p>
        
        <?php 
        // Display any error alerts
        if(isset($_SESSION['error'])) {
            echo '<div class="alert alert-error animate__animated animate__shakeX">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']); 
        }
        if(isset($_SESSION['success'])) {
            echo '<div class="alert alert-success animate__animated animate__pulse">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']); 
        }
        ?>

        <form action="../actions/update_profile_action.php" method="POST">
            
            <div class="form-group">
                <label for="faculty">Faculty</label>
                <select name="faculty" id="faculty" required>
                    <option value="" disabled selected>Select Your Faculty</option>
                    <option value="Faculty of Arts & Social Sciences">Faculty of Arts & Social Sciences</option>
                    <option value="Faculty of Education">Faculty of Education</option>
                    <option value="Faculty of Science">Faculty of Science</option>
                    <option value="Faculty of Law">Faculty of Law</option>
                    <option value="Faculty of Pharmaceutical Sciences">Faculty of Pharmaceutical Sciences</option>
                    <option value="Faculty of Environmental Sciences">Faculty of Environmental Sciences</option>
                    <option value="Faculty of Agriculture">Faculty of Agriculture</option>
                    <option value="Faculty of Medical Sciences">Faculty of Medical Sciences</option>
                    <option value="Faculty of Basic Clinical Sciences">Faculty of Basic Clinical Sciences</option>
                    <option value="Faculty of Clinical Sciences">Faculty of Clinical Sciences</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" required placeholder="e.g. +234 812 345 6789">
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" name="department" id="department" required placeholder="e.g. Computer Science">
            </div>
            
            <button type="submit" class="btn-submit" name="update_profile_btn">Save & Proceed to Dashboard</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
