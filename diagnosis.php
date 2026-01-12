<?php
include 'db.php';

// List of symptoms (same as before)
$all_symptoms = [
    'fever', 'cough', 'sore_throat', 'runny_nose', 'headache', 'body_ache', 'fatigue',
    'sneezing', 'mild_fever', 'congestion', 'dry_cough', 'loss_of_taste', 'loss_of_smell',
    'breathing_difficulty', 'nausea', 'vomiting', 'sensitivity_to_light', 'sensitivity_to_sound',
    'shortness_of_breath', 'wheezing', 'chest_tightness', 'coughing', 'frequent_urination',
    'excessive_thirst', 'unexplained_weight_loss', 'blurred_vision', 'dizziness', 'chest_pain',
    'sweating', 'weakness', 'pale_skin', 'diarrhea', 'stomach_cramps', 'stomach_pain', 'bloating',
    'indigestion', 'loss_of_appetite', 'burning_stomach_pain', 'heartburn', 'persistent_sadness',
    'loss_of_interest', 'sleep_disorders', 'difficulty_concentrating', 'excessive_worry',
    'restlessness', 'rapid_heartbeat', 'trembling', 'joint_pain', 'joint_stiffness', 'swelling',
    'reduced_mobility', 'lower_back_pain', 'stiffness', 'muscle_spasms', 'difficulty_moving',
    'burning_urination', 'pelvic_pain', 'cloudy_urine', 'severe_back_pain', 'blood_in_urine',
    'itching', 'redness', 'rash', 'pimples', 'oily_skin', 'blackheads', 'whiteheads', 'red_eyes',
    'eye_discharge', 'watery_eyes', 'ear_pain', 'hearing_loss', 'ear_discharge', 'difficulty_swallowing',
    'swollen_tonsils', 'high_fever', 'cough_with_phlegm', 'persistent_cough', 'weight_loss',
    'night_sweats', 'blood_in_sputum'
];
sort($all_symptoms);

$diagnosis_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['symptoms'])) {
    $selected = $_POST['symptoms'];
    $prolog_list = "[" . implode(",", $selected) . "]";
    
    // Adjust path if needed for your server
    $cmd = '"C:\Program Files\swipl\bin\swipl.exe" -s rules.pl -g "top_diagnoses(' . $prolog_list . ', 10, Results), write(Results), halt."';
    $output = shell_exec($cmd);

    if ($output && $output != '[]') {
        $clean = trim($output, "[] ");
        $entries = explode(',', $clean);
        
        foreach ($entries as $entry) {
            $parts = explode('-', trim($entry));
            if (count($parts) == 3) {
                $diagnosis_results[] = [
                    'confidence' => round($parts[0], 1),
                    'disease'    => str_replace('_', ' ', $parts[1]),
                    'doctor'     => $parts[2]
                ];
            }
        }
        usort($diagnosis_results, function($a, $b) { return $b['confidence'] <=> $a['confidence']; });
        $diagnosis_results = array_slice($diagnosis_results, 0, 3);
        
        if (isset($_SESSION['user_id']) && !empty($diagnosis_results)) {
            $top_result = $diagnosis_results[0];
            $save_stmt = $pdo->prepare("INSERT INTO diagnosis_history (user_id, symptoms, disease, confidence, doctor_type) VALUES (?, ?, ?, ?, ?)");
            $save_stmt->execute([$_SESSION['user_id'], implode(', ', $selected), $top_result['disease'], $top_result['confidence'], $top_result['doctor']]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Diagnosis - CareClinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="fas fa-robot"></i> CareClinic</a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php" class="nav-btn">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container-center" style="align-items: flex-start; padding-top: 50px;">
        <div class="card" style="width: 100%; max-width: 800px;">
            <h2 class="card-title text-center"><i class="fas fa-stethoscope"></i> Symptom Checker</h2>
            <p class="text-center" style="color: var(--text-light); margin-bottom: 30px;">
                Select your symptoms below, and our AI will suggest potential conditions and specialists.
            </p>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">What are you feeling?</label>
                    <select name="symptoms[]" id="symptoms_dropdown" multiple="multiple" style="width: 100%;" required>
                        <?php foreach($all_symptoms as $s): ?>
                            <option value="<?= $s ?>"><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-search-plus"></i> Run AI Diagnosis
                </button>
            </form>

            <?php if (!empty($diagnosis_results)): ?>
                <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">
                
                <h3 style="margin-bottom: 20px;">AI Analysis Results:</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
                    <?php foreach($diagnosis_results as $res): ?>
                        <div style="background: #fff; border: 1px solid #e1e4e8; border-radius: 12px; padding: 20px; text-align: center; transition: transform 0.2s; box-shadow: 0 5px 15px rgba(0,0,0,0.03);">
                            <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 10px;">
                                <?= $res['confidence'] ?><small style="font-size: 1rem;">%</small>
                            </div>
                            <h4 style="margin-bottom: 5px; color: var(--text-dark);"><?= ucwords($res['disease']) ?></h4>
                            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 15px;">
                                Consult: <strong><?= ucwords(str_replace('_', ' ', $res['doctor'])) ?></strong>
                            </p>
                            <a href="book_appointment.php?type=<?= $res['doctor'] ?>" class="btn btn-sm btn-success" style="width: 100%;">
                                Book Specialist
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <div class="alert alert-error mt-3">
                    <i class="fas fa-exclamation-circle"></i> No specific match found. Please consult a General Physician.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#symptoms_dropdown').select2({
                placeholder: "Search symptoms (e.g. fever, headache)...",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
</body>
</html>