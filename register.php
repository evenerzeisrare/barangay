<?php

$page_title = "Register";


require_once 'includes/bootstrap.php';


if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $role = sanitizeInput($_POST['role']);
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);

  
    if (!preg_match('/^[0-9]+$/', $phone)) {
        $error = 'Phone number must contain numbers only.';
    }

    elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    }
  
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/', $password)) {
        $error = 'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.';
    } else {
        $database = new Database();
        $db = $database->getConnection();


        $query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $query = "INSERT INTO users (username, password, email, role, full_name, address, phone) 
                      VALUES (:username, :password, :email, :role, :full_name, :address, :phone)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':phone', $phone);

            if ($stmt->execute()) {
                $user_id = $db->lastInsertId();

                // Log the registration action
                logAction($user_id, 'register', 'users', $user_id);

                $success = 'Account created successfully. You can now <a href="login.php">login</a>.';
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}

// Include the header
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Create Your Account</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div id="passwordWarning" class="text-danger mt-1" style="display:none;">
                                            Password must be at least 8 characters, include uppercase, lowercase, number, and special character.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">I want to</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="buyer">Buy Services</option>
                                    <option value="seller">Sell Services</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                                <div id="phoneWarning" class="text-danger mt-1" style="display:none;">
                                    Phone number must contain numbers only.
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const phoneInput = document.getElementById('phone');
    const passwordWarning = document.getElementById('passwordWarning');
    const phoneWarning = document.getElementById('phoneWarning');

    passwordInput.addEventListener('input', () => {
        const strongPassword = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/;
        passwordWarning.style.display = strongPassword.test(passwordInput.value) ? 'none' : 'block';
    });

    phoneInput.addEventListener('input', () => {
        const numbersOnly = /^[0-9]*$/;
        phoneWarning.style.display = numbersOnly.test(phoneInput.value) ? 'none' : 'block';
    });
</script>

<?php require_once 'includes/footer.php'; ?>
