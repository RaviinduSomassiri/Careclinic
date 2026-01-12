<?php
// filename: admin_dashboard.php
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 1. Handle Adding New Doctors
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_doctor'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $type = $_POST['doctor_type'];
    $spec = $_POST['specialization'];

    try {
        $pdo->beginTransaction();
        $stmt1 = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
        $stmt1->execute([$name, $email, $password]);
        $user_id = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO doctors (user_id, doctor_type, specialization) VALUES (?, ?, ?)");
        $stmt2->execute([$user_id, $type, $spec]);
        
        $pdo->commit();
        $msg = "Doctor added successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg_err = "Error: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $doc_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $stmt->execute([$doc_id]);
    $doctor_user = $stmt->fetch();

    if ($doctor_user) {
        $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$doctor_user['user_id']]);
        $msg = "Doctor deleted successfully!";
    }
}

// Fetch Data
$docs = $pdo->query("SELECT d.id, u.name, u.email, d.doctor_type, d.is_active FROM doctors d JOIN users u ON d.user_id = u.id")->fetchAll();
$types = $pdo->query("SELECT * FROM doctor_types")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MediSmart AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
        <?php if(isset($msg_err)) echo "<div class='alert alert-error'>$msg_err</div>"; ?>

        <div class="grid-2">
            <!-- Add Doctor Form -->
            <div class="card">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Register Doctor</h3>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Specialist Type</label>
                        <select name="doctor_type" class="form-control">
                            <?php foreach($types as $t): ?>
                                <option value="<?= $t['type_name'] ?>"><?= ucwords(str_replace('_', ' ', $t['type_name'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Specialization Detail</label>
                        <input type="text" name="specialization" class="form-control" placeholder="e.g. Asthma Specialist">
                    </div>
                    <button type="submit" name="add_doctor" class="btn btn-primary" style="width:100%;">Create Account</button>
                </form>
            </div>

            <!-- Manage Doctors -->
            <div class="card">
                <h3 class="card-title"><i class="fas fa-user-md"></i> Manage Doctors</h3>
                <div class="table-container" style="max-height: 500px; overflow-y: auto;">
                    <table class="styled-table" style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Specialty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($docs as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['name']) ?></td>
                                <td><?= ucwords(str_replace('_', ' ', $d['doctor_type'])) ?></td>
                                <td>
                                    <a href="edit_doctor.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <a href="admin_dashboard.php?delete_id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete permanently?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- All System Appointments -->
        <div class="card mt-3">
            <h3 class="card-title"><i class="fas fa-calendar-alt"></i> System Appointments Log</h3>
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT a.id, u1.name as patient, u2.name as doctor, a.appointment_date, a.appointment_time, a.status 
                                             FROM appointments a
                                             JOIN users u1 ON a.user_id = u1.id
                                             JOIN doctors d ON a.doctor_id = d.id
                                             JOIN users u2 ON d.user_id = u2.id
                                             ORDER BY a.appointment_date DESC LIMIT 20");
                        while($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['patient']) ?></td>
                            <td>Dr. <?= htmlspecialchars($row['doctor']) ?></td>
                            <td><?= $row['appointment_date'] ?> at <?= date('H:i', strtotime($row['appointment_time'])) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($row['status']) == 'pending' ? 'pending' : (strtolower($row['status']) == 'cancelled' ? 'cancelled' : 'confirmed') ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>