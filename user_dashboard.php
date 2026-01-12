<?php
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- HANDLE CANCELLATION ---
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->execute([$cancel_id, $user_id]);
    header("Location: user_dashboard.php?msg=Appointment Cancelled");
    exit;
}

// Fetch Appointments
$app_stmt = $pdo->prepare("SELECT a.*, u.name as doc_name 
                           FROM appointments a 
                           JOIN doctors d ON a.doctor_id = d.id 
                           JOIN users u ON d.user_id = u.id 
                           WHERE a.user_id = ? 
                           ORDER BY a.appointment_date DESC");
$app_stmt->execute([$user_id]);
$appointments = $app_stmt->fetchAll();

// Fetch Diagnosis History
$diag_stmt = $pdo->prepare("SELECT * FROM diagnosis_history WHERE user_id = ? ORDER BY created_at DESC");
$diag_stmt->execute([$user_id]);
$history = $diag_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - CareClinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="fas fa-heartbeat"></i> CareClinic</a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="diagnosis.php" class="nav-link">New Diagnosis</a>
            <span style="color: var(--text-light);">|</span>
            <span style="font-weight: 600;"><?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <h1>My Health Dashboard</h1>
            <a href="book_appointment.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book Appointment</a>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Appointments Section -->
            <div class="card">
                <h3 class="card-title"><i class="far fa-calendar-check" style="color: var(--primary-color);"></i> My Appointments</h3>
                
                <?php if(empty($appointments)): ?>
                    <p class="text-center" style="color: var(--text-light); padding: 20px;">No appointments found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($appointments as $a): ?>
                                <tr>
                                    <td><strong>Dr. <?= htmlspecialchars($a['doc_name']) ?></strong></td>
                                    <td><?= $a['appointment_date'] ?></td>
                                    <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($a['status']) == 'pending' ? 'pending' : (strtolower($a['status']) == 'cancelled' ? 'cancelled' : 'confirmed') ?>">
                                            <?= strtoupper($a['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($a['status'] == 'pending'): ?>
                                            <a href="user_dashboard.php?cancel_id=<?= $a['id'] ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure?')">
                                               <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #ccc;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Diagnosis History Section -->
            <div class="card" style="height: fit-content;">
                <h3 class="card-title"><i class="fas fa-file-medical-alt" style="color: var(--secondary-color);"></i> AI Diagnosis History</h3>
                
                <?php if(empty($history)): ?>
                    <p class="text-center" style="color: var(--text-light); padding: 20px;">No diagnosis history yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach($history as $h): ?>
                            <div style="padding: 15px; border-radius: 8px; background: #f8f9fa; border-left: 4px solid var(--secondary-color);">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h4 style="color: var(--text-dark); margin-bottom: 5px;"><?= ucwords($h['disease']) ?></h4>
                                        <p style="font-size: 0.85rem; color: var(--text-light);">
                                            <i class="far fa-clock"></i> <?= date('M d, Y', strtotime($h['created_at'])) ?>
                                        </p>
                                    </div>
                                    <span style="font-weight: 700; color: var(--primary-color);"><?= $h['confidence'] ?>%</span>
                                </div>
                                <div style="margin-top: 10px; font-size: 0.9rem;">
                                    <strong>Symptoms:</strong> <span style="color: var(--text-light);"><?= htmlspecialchars($h['symptoms']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>