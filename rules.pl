disease(flu,
    [fever, cough, sore_throat, runny_nose, headache, body_ache, fatigue],
    general_physician).

disease(common_cold,
    [sneezing, runny_nose, sore_throat, mild_fever, congestion],
    general_physician).

disease(migraine,
    [headache, nausea, vomiting, sensitivity_to_light, sensitivity_to_sound],
    neurologist).

disease(asthma,
    [shortness_of_breath, wheezing, chest_tightness, coughing],
    pulmonologist).

disease(diabetes,
    [frequent_urination, excessive_thirst, unexplained_weight_loss, fatigue, blurred_vision],
    endocrinologist).

disease(hypertension,
    [headache, dizziness, chest_pain, blurred_vision, shortness_of_breath],
    cardiologist).

disease(heart_disease,
    [chest_pain, shortness_of_breath, nausea, sweating, fatigue],
    cardiologist).

disease(anemia,
    [fatigue, weakness, pale_skin, dizziness, shortness_of_breath],
    hematologist).

disease(food_poisoning,
    [nausea, vomiting, diarrhea, stomach_cramps, fever],
    gastroenterologist).

disease(gastritis,
    [stomach_pain, bloating, nausea, indigestion, loss_of_appetite],
    gastroenterologist).

disease(ulcer,
    [burning_stomach_pain, bloating, heartburn, nausea],
    gastroenterologist).

disease(depression,
    [persistent_sadness, fatigue, loss_of_interest, sleep_disorders, difficulty_concentrating],
    psychiatrist).

disease(anxiety_disorder,
    [excessive_worry, restlessness, rapid_heartbeat, sweating, trembling],
    psychiatrist).

disease(arthritis,
    [joint_pain, joint_stiffness, swelling, reduced_mobility],
    orthopedist).

disease(back_pain,
    [lower_back_pain, stiffness, muscle_spasms, difficulty_moving],
    orthopedist).

disease(urinary_tract_infection,
    [burning_urination, frequent_urination, pelvic_pain, cloudy_urine],
    urologist).

disease(kidney_stone,
    [severe_back_pain, blood_in_urine, nausea, vomiting, frequent_urination],
    urologist).

disease(skin_allergy,
    [itching, redness, rash, swelling],
    dermatologist).

disease(acne,
    [pimples, oily_skin, blackheads, whiteheads],
    dermatologist).

disease(conjunctivitis,
    [red_eyes, eye_discharge, itching, watery_eyes],
    ophthalmologist).

disease(ear_infection,
    [ear_pain, hearing_loss, fever, ear_discharge],
    ent_specialist).

disease(tonsillitis,
    [sore_throat, difficulty_swallowing, fever, swollen_tonsils],
    ent_specialist).

disease(pneumonia,
    [high_fever, chest_pain, cough_with_phlegm, breathing_difficulty],
    pulmonologist).

disease(tuberculosis,
    [persistent_cough, weight_loss, night_sweats, blood_in_sputum],
    pulmonologist).

/* -----------------------------------------
   Utility Predicates
   ----------------------------------------- */

% count how many symptoms match
count_matches([], _, 0).
count_matches([H|T], Selected, Count) :-
    member(H, Selected),
    count_matches(T, Selected, Rest),
    Count is Rest + 1.
count_matches([H|T], Selected, Count) :-
    \+ member(H, Selected),
    count_matches(T, Selected, Count).

% calculate confidence percentage
confidence(Matched, Total, Confidence) :-
    Total > 0,
    Confidence is (Matched / Total) * 100.

/* -----------------------------------------
   Main Diagnosis Rule
   diagnose(SelectedSymptoms, Disease, Confidence, DoctorType)
   ----------------------------------------- */

diagnose(SelectedSymptoms, Disease, Confidence, DoctorType) :-
    disease(Disease, DiseaseSymptoms, DoctorType),
    count_matches(DiseaseSymptoms, SelectedSymptoms, Matched),
    length(DiseaseSymptoms, Total),
    Matched > 0,
    confidence(Matched, Total, Confidence).

/* -----------------------------------------
   Get Best Diagnosis (Highest Confidence)
   ----------------------------------------- */

top_diagnoses(SelectedSymptoms, Threshold, Results) :-
    findall(
        Confidence-Disease-DoctorType,
        diagnose(SelectedSymptoms, Disease, Confidence, DoctorType),
        AllResults
    ),
    include([C-_-_]>>(C >= Threshold), AllResults, ResultsSorted),
    sort(ResultsSorted, Results).