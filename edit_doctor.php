<?php
// filename: edit_doctor.php
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT d.*, u.name, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $type = $_POST['doctor_type'];
    $spec = $_POST['specialization'];
    $active = isset($_POST['is_active']) ? 1 : 0;

    $upd1 = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $upd1->execute([$name, $email, $doctor['user_id']]);

    $upd2 = $pdo->prepare("UPDATE doctors SET doctor_type = ?, specialization = ?, is_active = ? WHERE id = ?");
    $upd2->execute([$type, $spec, $active, $id]);

    header("Location: admin_dashboard.php?msg=Doctor Updated");
}

$types = $pdo->query("SELECT * FROM doctor_types")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Doctor - MediSmart AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-center">
        <div class="card" style="width: 100%; max-width: 500px;">
            <h2 class="card-title">Edit Doctor Profile</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($doctor['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Doctor Type</label>
                    <select name="doctor_type" class="form-control">
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['type_name'] ?>" <?= ($doctor['doctor_type'] == $t['type_name']) ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', $t['type_name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Specialization Detail</label>
                    <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($doctor['specialization']) ?>">
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" id="active" <?= $doctor['is_active'] ? 'checked' : '' ?> style="width: 20px; height: 20px;">
                    <label for="active" class="form-label" style="margin: 0; cursor: pointer;">Account Active</label>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">Save Changes</button>
                    <a href="admin_dashboard.php" class="btn btn-outline" style="flex: 1;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>