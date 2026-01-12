<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to book an appointment");
    exit;
}

$message = "";
$pre_selected_type = $_GET['type'] ?? '';

$type_stmt = $pdo->query("SELECT * FROM doctor_types");
$all_types = $type_stmt->fetchAll();

$doctors = [];
if ($pre_selected_type) {
    $doc_stmt = $pdo->prepare("SELECT d.id, u.name, d.specialization 
                               FROM doctors d 
                               JOIN users u ON d.user_id = u.id 
                               WHERE d.doctor_type = ? AND d.is_active = 1");
    $doc_stmt->execute([$pre_selected_type]);
    $doctors = $doc_stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $doc_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $user_id = $_SESSION['user_id'];

    if (!empty($doc_id) && !empty($date) && !empty($time)) {
        $insert = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'pending')");
        $insert->execute([$user_id, $doc_id, $date, $time]);
        $message = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Success! Your appointment is booked. <a href='user_dashboard.php'>View Dashboard</a></div>";
    } else {
        $message = "<div class='alert alert-error'>Please fill all fields.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - CareClinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="fas fa-heartbeat"></i> CareClinic</a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="user_dashboard.php" class="nav-btn">Dashboard</a>
        </div>
    </nav>

    <div class="container-center" style="align-items: flex-start; padding-top: 50px;">
        <div class="card" style="width: 100%; max-width: 600px;">
            <h2 class="card-title text-center"><i class="fas fa-calendar-plus"></i> Book Appointment</h2>
            <?= $message ?>

            <!-- Step 1: Filter -->
            <form method="GET" action="book_appointment.php" style="background: #f8f9fa; padding: 20px; border-radius: var(--radius-md); margin-bottom: 25px;">
                <label class="form-label"><i class="fas fa-filter"></i> Filter by Specialist</label>
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Select Category --</option>
                    <?php foreach($all_types as $t): ?>
                        <option value="<?= $t['type_name'] ?>" <?= ($pre_selected_type == $t['type_name']) ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_', ' ', $t['type_name'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Step 2: Booking Form -->
            <?php if ($pre_selected_type): ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Select Doctor</label>
                        <select name="doctor_id" class="form-control" required>
                            <?php if (empty($doctors)): ?>
                                <option value="">No doctors available</option>
                            <?php else: ?>
                                <?php foreach($doctors as $d): ?>
                                    <option value="<?= $d['id'] ?>">Dr. <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['specialization']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time</label>
                            <input type="time" name="time" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" name="confirm_booking" class="btn btn-success" style="width: 100%; margin-top: 10px;" <?= empty($doctors) ? 'disabled' : '' ?>>
                        Confirm Appointment
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center" style="padding: 20px; color: var(--text-light);">
                    <i class="fas fa-arrow-up" style="margin-bottom: 10px;"></i><br>
                    Please select a specialist category above to proceed.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>