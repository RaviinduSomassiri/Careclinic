<?php
// filename: doctor_dashboard.php
session_start();

include 'db.php';
require_once 'email_service.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$user_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

/* =========================
   HANDLE STATUS UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {

    $appointment_id = (int)$_POST['appointment_id'];
    $new_status = $_POST['status'];

    $stmt = $pdo->prepare("
        SELECT a.appointment_date, a.appointment_time, u.name, u.email
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointment_id, $doctor_id]);
    $appointment = $stmt->fetch();

    if ($appointment) {

        if (updateAppointmentStatus($pdo, $appointment_id, $doctor_id, $new_status)) {

            sendStatusChangeEmail(
                $appointment['email'],
                $appointment['name'],
                $_SESSION['name'],
                date('M d, Y', strtotime($appointment['appointment_date'])),
                date('g:i A', strtotime($appointment['appointment_time'])),
                $new_status
            );

            $statusText = strtoupper(str_replace('_', ' ', $new_status));
            $_SESSION['alert_message'] = "Appointment marked as {$statusText}. Email sent to patient.";
        }
    }

    header("Location: doctor_dashboard.php");
    exit;
}

/* =========================
   FETCH APPOINTMENTS
========================= */
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS patient_name, u.email AS patient_email
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();

/* =========================
   FUNCTIONS
========================= */
function updateAppointmentStatus(PDO $pdo, int $appointment_id, int $doctor_id, string $new_status): bool
{
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
    if (!in_array($new_status, $allowedStatuses)) return false;

    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET status = ? 
        WHERE id = ? AND doctor_id = ?
    ");
    return $stmt->execute([$new_status, $appointment_id, $doctor_id]);
}

function sendStatusChangeEmail(
    string $patientEmail,
    string $patientName,
    string $doctorName,
    string $appointmentDate,
    string $appointmentTime,
    string $status
) {
    $subject = "Appointment Status Update";

    $body = "
        <h2>Hello {$patientName},</h2>
        <p>Your appointment with <strong>Dr. {$doctorName}</strong> has been updated.</p>
        <p><strong>Date:</strong> {$appointmentDate}</p>
        <p><strong>Time:</strong> {$appointmentTime}</p>
        <p><strong>Status:</strong> " . strtoupper($status) . "</p>
        <br>
        <p>Thank you,<br>CareClinic AI Team</p>
    ";

    return sendEmail($patientEmail, $patientName, $subject, $body);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - CareClinic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        .ui-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            background: #e7f8ef;
            color: #1e7e34;
            border-left: 5px solid #28a745;
        }
        .ui-alert i {
            font-size: 18px;
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <i class="fas fa-user-md"></i> Doctor Portal
    </a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <strong>Dr. <?= htmlspecialchars($_SESSION['name']) ?></strong>
        <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
    </div>
</nav>

<div class="main-content">
    <div class="card">

        <!-- UI ALERT (TOP OF APPOINTMENTS) -->
        <?php if (isset($_SESSION['alert_message'])): ?>
            <div class="ui-alert">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['alert_message']) ?>
            </div>
            <?php unset($_SESSION['alert_message']); ?>
        <?php endif; ?>

        <h2 class="card-title">
            <i class="far fa-calendar-alt"></i> Upcoming Appointments
        </h2>

        <?php if (empty($appointments)): ?>
            <p style="text-align:center;">No appointments scheduled.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['patient_name']) ?></td>
                            <td><?= htmlspecialchars($a['patient_email']) ?></td>
                            <td><?= date('M d, Y', strtotime($a['appointment_date'])) ?></td>
                            <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $a['status'] ?>">
                                    <?= strtoupper($a['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:flex; gap:6px;">
                                    <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                                    <select name="status">
                                        <option value="pending" <?= $a['status']=='pending'?'selected':'' ?>>Pending</option>
                                        <option value="confirmed" <?= $a['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                                        <option value="completed" <?= $a['status']=='completed'?'selected':'' ?>>Completed</option>
                                        <option value="cancelled" <?= $a['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                        <option value="no_show" <?= $a['status']=='no_show'?'selected':'' ?>>No Show</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-spinner fa-spin spinner-icon" style="display:none; margin-right:6px;"></i>
                                        <span class="btn-text">Update</span>
                                    </button>
                                    <script>
                                        (function(){
                                            var script = document.currentScript || (function(){
                                                var s = document.getElementsByTagName('script');
                                                return s[s.length-1];
                                            })();
                                            var form = script.parentNode;
                                            var btn = form.querySelector('button[type="submit"]');
                                            var icon = btn.querySelector('.spinner-icon');
                                            var txt = btn.querySelector('.btn-text');
                                            form.addEventListener('submit', function(){
                                                if(btn.disabled) return;
                                                btn.disabled = true;
                                                if(txt) txt.style.display = 'none';
                                                if(icon) icon.style.display = 'inline-block';
                                            }, {once:true});
                                        })();
                                    </script>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    setTimeout(() => {
        const alertBox = document.querySelector('.ui-alert');
        if (alertBox) alertBox.style.display = 'none';
    }, 4000);
</script>

</body>
</html>
