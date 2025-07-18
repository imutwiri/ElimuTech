<?php
session_start();
require_once 'db_config.php';

// Redirect to login if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// Handle verification actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $verification_id = intval($_GET['id']);
    
    if ($_GET['action'] === 'approve') {
        $status = 'approved';
    } elseif ($_GET['action'] === 'reject') {
        $status = 'rejected';
    }
    
    $stmt = $conn->prepare("UPDATE verifications SET status = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $verification_id);
    $stmt->execute();
    
    // Redirect to prevent form resubmission
    header("Location: admin_dashboard.php");
    exit();
}

// Get all pending verifications
$verifications = [];
$stmt = $conn->prepare("
    SELECT v.*, u.full_name, u.email 
    FROM verifications v
    JOIN users u ON v.user_id = u.id
    WHERE v.status = 'pending'
    ORDER BY v.submitted_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $verifications[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ElimuTech</title>
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
        .verification-card {
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <!-- Kenyan Flag Top Bar -->
    <div class="kenyan-flag-bg"></div>

    <!-- Admin Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <img src="Images/ELIMU Tech.png" alt="ElimuTech Logo" class="h-10 mr-3">
                <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="logout.php" class="text-gray-700 hover:text-green-700 font-medium">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Admin Content -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Student Verification Requests</h2>
            
            <?php if (empty($verifications)): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="inline-block bg-green-100 rounded-full p-4 mb-4">
                        <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No Pending Verifications</h3>
                    <p class="text-gray-600">All student verification requests have been processed.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($verifications as $verification): ?>
                        <div class="bg-white rounded-lg shadow-md verification-card p-6">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($verification['full_name']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($verification['email']); ?></p>
                                    
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-sm text-gray-500">Institution</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($verification['institution']); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-500">Student ID</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($verification['student_id']); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-500">Program</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($verification['program']); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-500">Submitted</p>
                                            <p class="font-medium"><?php echo date('M d, Y H:i', strtotime($verification['submitted_at'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col space-y-2 md:items-end">
                                    <div class="flex space-x-2">
                                        <a href="uploads/<?php echo htmlspecialchars($verification['id_front']); ?>" target="_blank" class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-id-card mr-1"></i> Front ID
                                        </a>
                                        <a href="uploads/<?php echo htmlspecialchars($verification['id_back']); ?>" target="_blank" class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-id-card mr-1"></i> Back ID
                                        </a>
                                    </div>
                                    
                                    <div class="flex space-x-2 mt-2">
                                        <a href="admin_dashboard.php?action=approve&id=<?php echo $verification['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-700 hover:bg-green-800">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </a>
                                        <a href="admin_dashboard.php?action=reject&id=<?php echo $verification['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800">
                                            <i class="fas fa-times mr-1"></i> Reject
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>