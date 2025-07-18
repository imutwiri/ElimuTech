<?php
session_start();
require_once 'db_config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_student = false;
$verification_status = 'not_submitted';

// Check if user is a student
$stmt = $conn->prepare("SELECT is_student FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $is_student = $row['is_student'];
}

// Check verification status
$stmt = $conn->prepare("SELECT status FROM verifications WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $verification_status = $row['status'];
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $institution = sanitize_input($_POST['institution']);
    $student_id = sanitize_input($_POST['student_id']);
    $program = sanitize_input($_POST['program']);
    
    // Validate inputs
    if (empty($institution)) {
        $errors[] = "Institution is required";
    }
    
    if (empty($student_id)) {
        $errors[] = "Student ID is required";
    }
    
    if (empty($program)) {
        $errors[] = "Program of study is required";
    }
    
    // Handle file uploads
    $front_uploaded = false;
    $back_uploaded = false;
    $front_file = '';
    $back_file = '';
    
    if (isset($_FILES['id_front']) && $_FILES['id_front']['error'] == UPLOAD_ERR_OK) {
        $front_file = upload_file($_FILES['id_front'], 'front');
        $front_uploaded = ($front_file !== false);
    } else {
        $errors[] = "Front ID image is required";
    }
    
    if (isset($_FILES['id_back']) && $_FILES['id_back']['error'] == UPLOAD_ERR_OK) {
        $back_file = upload_file($_FILES['id_back'], 'back');
        $back_uploaded = ($back_file !== false);
    } else {
        $errors[] = "Back ID image is required";
    }
    
    // Save to database if no errors
    if (empty($errors) && $front_uploaded && $back_uploaded) {
        $stmt = $conn->prepare("INSERT INTO verifications (user_id, institution, student_id, program, id_front, id_back) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $institution, $student_id, $program, $front_file, $back_file);
        
        if ($stmt->execute()) {
            $success = true;
            $verification_status = 'pending';
        } else {
            $errors[] = "Error submitting verification: " . $stmt->error;
        }
    }
}

function sanitize_input($data) {
    global $conn;
    return htmlspecialchars(stripslashes(trim($data)));
}

function upload_file($file, $type) {
    $target_dir = "uploads/";
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_filename = "id_" . $type . "_" . time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Check file size (max 2MB)
    if ($file["size"] > 2000000) {
        return false;
    }
    
    // Allow certain file formats
    $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Verification - ElimuTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .kenyan-flag-bg {
            background: linear-gradient(to right, 
                #006600 33%,  /* Green */
                #000000 33%,  /* Black */
                #000000 66%,  /* Black */
                #BB0000 66%,  /* Red */
                #BB0000 100%  /* Red */
            );
            height: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #059669;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .preview-container {
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            background-color: #f9fafb;
            min-height: 150px;
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <!-- Kenyan Flag Top Bar -->
    <div class="kenyan-flag-bg"></div>

    <!-- Header/Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <img src="Images/ELIMU Tech.png" alt="ElimuTech Logo" class="h-10 mr-3">
                <h1 class="text-xl font-bold text-gray-800">Elimu<span class="text-green-700">Tech</span></h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-green-700 font-medium">
                    <i class="fas fa-user mr-1"></i> My Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Verification Section -->
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-green-700 to-green-800 text-white p-6">
                    <h2 class="text-2xl font-bold">Student Status Verification</h2>
                    <p class="mt-2">Verify your student status to unlock free access to all courses</p>
                    
                    <div class="mt-4">
                        <span class="status-badge status-<?php echo $verification_status; ?>">
                            <?php 
                            if ($verification_status == 'pending') {
                                echo 'Verification Pending';
                            } elseif ($verification_status == 'approved') {
                                echo 'Verified Student';
                            } elseif ($verification_status == 'rejected') {
                                echo 'Verification Rejected';
                            } else {
                                echo 'Verification Not Submitted';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                            <strong><i class="fas fa-check-circle mr-2"></i> Success!</strong>
                            <span>Your verification request has been submitted. We'll review it within 1-2 business days.</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                            <strong><i class="fas fa-exclamation-circle mr-2"></i> Errors:</strong>
                            <ul class="mt-1 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($verification_status == 'approved'): ?>
                        <div class="text-center py-8">
                            <div class="inline-block bg-green-100 rounded-full p-4 mb-4">
                                <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Verification Complete!</h3>
                            <p class="text-gray-600 mb-6">Your student status has been verified. You now have full access to all courses.</p>
                            <a href="courses.html" class="inline-block bg-green-700 hover:bg-green-800 text-white font-medium py-2 px-6 rounded-md">
                                Browse Courses
                            </a>
                        </div>
                    <?php elseif ($verification_status == 'rejected'): ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Your verification was rejected. Please review your documents and submit again.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php include 'verification_form.php'; ?>
                    <?php elseif ($verification_status == 'pending'): ?>
                        <div class="text-center py-8">
                            <div class="inline-block bg-blue-100 rounded-full p-4 mb-4">
                                <i class="fas fa-clock text-blue-600 text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Verification in Progress</h3>
                            <p class="text-gray-600 mb-6">We've received your documents and are verifying your student status. This usually takes 1-2 business days.</p>
                            <p class="text-sm text-gray-500">You can still access free courses while we verify your status.</p>
                        </div>
                    <?php else: ?>
                        <?php if ($is_student): ?>
                            <?php include 'verification_form.php'; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="inline-block bg-purple-100 rounded-full p-4 mb-4">
                                    <i class="fas fa-graduation-cap text-purple-600 text-4xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Student Verification Required</h3>
                                <p class="text-gray-600 mb-6">
                                    You indicated that you're a Kenyan university student during registration. 
                                    Please verify your student status to unlock free access to all courses.
                                </p>
                                <a href="dashboard.php?action=verify" class="inline-block bg-green-700 hover:bg-green-800 text-white font-medium py-2 px-6 rounded-md">
                                    Verify Student Status
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Why Verify Your Student Status?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-book text-green-700 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">Free Course Access</h4>
                        <p class="text-gray-600 text-sm mt-1">Access all premium courses at no cost as a verified Kenyan student.</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-certificate text-green-700 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">University Certificates</h4>
                        <p class="text-gray-600 text-sm mt-1">Earn certificates recognized by Kenyan universities.</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-briefcase text-green-700 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">Job Opportunities</h4>
                        <p class="text-gray-600 text-sm mt-1">Get access to exclusive job postings from Kenyan tech companies.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p class="text-gray-400">© 2025 ElimuTech Kenya. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Preview image function
        function previewImage(event, previewId) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    
                    // Show preview container
                    const container = document.getElementById(previewId + '-container');
                    container.classList.remove('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Add event listeners
        document.getElementById('id_front').addEventListener('change', function(e) {
            previewImage(e, 'frontPreview');
        });
        
        document.getElementById('id_back').addEventListener('change', function(e) {
            previewImage(e, 'backPreview');
        });
    </script>
</body>
</html>