<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "elimutech_db"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$full_name = $email = $phone = $password = $confirm_password = "";
$is_student = 0;
$errors = [];
$success = false;

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $full_name = sanitize_input($_POST["full-name"]);
    $email = sanitize_input($_POST["email"]);
    $phone = sanitize_input($_POST["phone"]);
    $password = sanitize_input($_POST["password"]);
    $confirm_password = sanitize_input($_POST["confirm-password"]);
    $is_student = isset($_POST["student-check"]) ? 1 : 0;

    // Validation rules
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $errors[] = "Email is already registered";
    }
    $stmt->close();

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $sql = "INSERT INTO users (full_name, email, phone, password, is_student, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $hashed_password, $is_student);
        
        if ($stmt->execute()) {
            $success = true;
            // Reset form values
            $full_name = $email = $phone = $password = $confirm_password = "";
            $is_student = 0;
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ElimuTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .success-message {
            background-color: #48bb78;
            color: white;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <!-- Kenyan Flag Top Bar -->
    <div class="kenyan-flag-bg"></div>

    <!-- Registration Section -->
    <section class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <img src="Images/ELIMU Tech.png" alt="ElimuTech Logo" class="mx-auto h-12 w-auto">
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Already have an account? <a href="login.html" class="font-medium text-green-700 hover:text-green-800">Sign in here</a>
                </p>
            </div>
            
            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle mr-2"></i> Registration successful! You can now login.
                </div>
            <?php endif; ?>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md" action="register.php" method="POST">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="full-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="full-name" name="full-name" type="text" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500" 
                                placeholder="Ian Mutwiri"
                                value="<?php echo htmlspecialchars($full_name); ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500" 
                                placeholder="your@gmail.com"
                                value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input id="phone" name="phone" type="tel" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500" 
                                placeholder="+254 700 000 000"
                                value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="new-password" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500" 
                                placeholder="••••••••">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                    </div>
                    
                    <div>
                        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="confirm-password" name="confirm-password" type="password" autocomplete="new-password" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500" 
                                placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input id="student-check" name="student-check" type="checkbox" 
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            <?php echo $is_student ? 'checked' : ''; ?>>
                        <label for="student-check" class="ml-2 block text-sm text-gray-700">
                            I am a Kenyan university/college student
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-green-300 group-hover:text-green-200"></i>
                        </span>
                        Create Account
                    </button>
                </div>
                
                <div class="relative mt-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Or register with
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mt-6">
                    <button type="button" 
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-google text-red-500 mr-2"></i> Google
                    </button>
                    
                    <button type="button" 
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-microsoft text-blue-500 mr-2"></i> Microsoft
                    </button>
                </div>
            </form>
            
            <div class="text-center text-sm text-gray-500 mt-6">
                <p>By registering, you agree to our <a href="#" class="text-green-700 hover:text-green-800">Terms of Service</a> and <a href="#" class="text-green-700 hover:text-green-800">Privacy Policy</a>.</p>
            </div>
        </div>
    </section>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters');
                return;
            }
            
            const isStudent = document.getElementById('student-check').checked;
            if (isStudent) {
                alert('After registration, please verify your student status to get free access to all courses.');
            }
        });
    </script>
</body>
</html>