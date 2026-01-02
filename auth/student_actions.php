<?php
session_start();
include("../connection.php"); 

// --- 1. Authentication and Validation ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : null;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Handle the certificate action
if ($action === 'certificate') {

    if (!$course_id || !is_numeric($course_id)) {
        $_SESSION['error'] = "Invalid request.";
        header("Location: ../dashboard.php"); 
        exit;
    }

    // --- 2. Fetch Data ---
    $total_modules_query = "SELECT COUNT(id) AS total_modules FROM modules WHERE course_id = '$course_id'";
    $total_modules_result = mysqli_query($conn, $total_modules_query);
    $total_modules = mysqli_fetch_assoc($total_modules_result)['total_modules'];

    $completed_modules_query = "SELECT COUNT(p.id) AS completed_modules FROM progress p JOIN modules m ON p.module_id = m.id WHERE m.course_id = '$course_id' AND p.user_id = '$user_id' AND p.status = 'completed'";
    $completed_modules_result = mysqli_query($conn, $completed_modules_query);
    $completed_modules = mysqli_fetch_assoc($completed_modules_result)['completed_modules'];

    $is_complete = ($total_modules > 0 && $completed_modules == $total_modules);

    $course_data_query = "SELECT c.title AS course_title, u.name AS lecturer_name FROM courses c JOIN users u ON c.lecturer_id = u.id WHERE c.id = '$course_id'";
    $course_data_result = mysqli_query($conn, $course_data_query);
    $course_data = mysqli_fetch_assoc($course_data_result);

    $course_title = htmlspecialchars($course_data['course_title']);
    $lecturer_name = htmlspecialchars($course_data['lecturer_name']);
    $completion_date = date('F j, Y'); 
    
    // --- 🔧 FEATURE 1: Generate Unique Certificate ID ---
    $unique_string = $user_id . '-' . $course_id . '-cert-salt';
    $certificate_id = strtoupper(substr(md5($unique_string), 0, 4) . '-' . substr(md5($unique_string), 4, 4) . '-' . substr(md5($unique_string), 8, 4));

    // --- 🔧 FEATURE 2: QR Code Generation ---
    $verification_url = "https://yourwebsite.com/verify.php?id=" . $certificate_id;
    $qr_code_url = "https://quickchart.io/qr?text=" . urlencode($verification_url) . "&size=120&margin=1&dark=1a1a1a&light=ffffff";

    mysqli_close($conn);
    
    // --- 3. HTML Output ---
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <link href="../css/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gold-rich: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
            --gold-darker: linear-gradient(135deg, #8c6225, #d4b058, #e8d68e, #d4b058, #8c6225);
            --black-accent: #1a1a1a;
            --off-white-bg: #fdfcf8;
        }

        body {
            background-color: #d4d4d4; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 10px; /* Small padding for mobile edges */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* --- 🔧 FEATURE 5: Mobile Optimization Container --- */
        .certificate-container {
            position: relative;
            /* Responsive Width */
            width: 100%; 
            max-width: 1122px; /* Max width approx A4 landscape */
            /* Maintain Aspect Ratio (Landscape) automatically */
            aspect-ratio: 1.414 / 1; 
            
            background: var(--off-white-bg);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            overflow: hidden;
            color: #333;
            margin: auto;
            border: 1px solid #d1c7b4;
        }

        /* --- DECORATIVE GEOMETRY --- */
        .shape-bl-black { position: absolute; bottom: -14%; left: -14%; width: 28%; height: 38%; background: var(--black-accent); transform: rotate(45deg); z-index: 1; }
        .shape-bl-gold { position: absolute; bottom: -13%; left: -13%; width: 27%; height: 37%; background: var(--gold-rich); transform: rotate(45deg); z-index: 2; box-shadow: 2px 2px 10px rgba(0,0,0,0.4); }
        
        .shape-tr-black { position: absolute; top: -16%; right: -16%; width: 32%; height: 44%; background: var(--black-accent); transform: rotate(45deg); z-index: 1; }
        .shape-tr-gold { position: absolute; top: -15%; right: -15%; width: 31%; height: 43%; background: var(--gold-rich); transform: rotate(45deg); z-index: 2; box-shadow: -2px 2px 10px rgba(0,0,0,0.4); }

        /* --- GOLD BORDER --- */
        .inner-border-container {
            position: absolute;
            top: 3%; bottom: 3%; left: 3%; right: 3%;
            border: 4px double #cfa44a; 
            z-index: 5;
            pointer-events: none;
        }

        /* --- 🔧 FEATURE 3: Interesting Watermark Pattern --- */
        .watermark-pattern {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0.05; /* Adjust for desired faintness */
            
            /* REPLACE THE BASE64 STRING BELOW WITH YOUR IMAGE'S DATA.
               You can use an online tool to convert image_2.png to base64.
            */
            background-image: url('data:image/png;base64,YOUR_BASE64_IMAGE_DATA_HERE');
            background-repeat: repeat;
            background-size: 150px 150px; /* Adjust scale of the pattern */
            background-position: center;
            
            /* Optional: Rotate for a more dynamic effect */
            transform: rotate(-15deg) scale(1.5);
            transform-origin: center;
        }

        /* Inner Content Wrapper */
        .content-wrapper {
            position: relative;
            z-index: 10;
            text-align: center;
            /* Responsive Padding using % */
            padding: 5% 7%; 
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* --- 🔧 FEATURE 4: Responsive Fonts (clamp) --- */
        
        .company-name {
            font-family: 'Cinzel', serif;
            /* Minimum 10px, Preferred 1.5vw, Max 16px */
            font-size: clamp(10px, 1.5vw, 16px);
            font-weight: 700;
            letter-spacing: 3px;
            color: #8c6225;
            margin-bottom: 2%;
            text-transform: uppercase;
        }

        .cert-title {
            font-family: 'Cinzel', serif;
            /* Scales dramatically with screen size */
            font-size: clamp(32px, 6.5vw, 72px); 
            font-weight: 900;
            text-transform: uppercase;
            color: #222;
            margin: 0;
            line-height: 0.9;
            letter-spacing: 4px;
            text-shadow: 1px 1px 0px rgba(255,255,255,0.5);
        }

        .cert-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(10px, 1.8vw, 18px);
            letter-spacing: 6px;
            color: #555;
            text-transform: uppercase;
            margin-top: 10px;
            font-weight: 500;
        }

        /* Decorative Divider */
        .divider-line {
            height: 2px;
            width: 50%;
            margin: 2% auto;
            background: var(--gold-rich);
            position: relative;
        }
        .divider-line::after {
            content: '◆';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: var(--off-white-bg);
            padding: 0 10px;
            color: #cfa44a;
            font-size: 14px;
        }

        /* Ribbon */
        .ribbon-banner {
            background: var(--gold-darker);
            display: inline-block;
            padding: 1.2% 6%;
            margin: 1.5% 0 2.5% 0;
            position: relative;
            clip-path: polygon(0 0, 100% 0, 97% 50%, 100% 100%, 0 100%, 3% 50%);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .ribbon-text {
            color: #1a1a1a;
            font-family: 'Cinzel', serif;
            font-weight: 700;
            text-transform: uppercase;
            font-size: clamp(8px, 1.2vw, 13px);
            letter-spacing: 2px;
        }

        /* Student Name */
        .student-name {
            font-family: 'Great Vibes', cursive;
            /* Massive responsive scaling */
            font-size: clamp(40px, 8vw, 90px);
            color: #1a1a1a;
            margin: 0px 0 1.5% 0;
            line-height: 1.1;
            text-shadow: 2px 2px 2px rgba(0,0,0,0.1);
        }

        .description-text {
            max-width: 70%;
            margin: 0 auto 3% auto;
            color: #444;
            font-size: clamp(10px, 1.5vw, 16px);
            line-height: 1.6;
        }
        .course-highlight {
             font-family: 'Cinzel', serif;
             color: #000; 
             font-size: clamp(12px, 2vw, 22px); 
             font-weight: 700;
             display: block;
             margin-top: 5px;
        }

        /* Footer */
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            width: 85%;
            margin: 0 auto;
        }

        .signature-block { text-align: center; width: 25%; }
        .line { border-bottom: 1px solid #cfa44a; margin-bottom: 8px; padding-bottom: 5px; }
        .sign-text {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            color: #8c6225;
            font-size: clamp(8px, 1.1vw, 12px);
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .generated-val {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: clamp(10px, 1.4vw, 16px);
            color: #333;
        }

        /* Badge */
        .badge {
            width: clamp(60px, 9vw, 100px);
            height: clamp(60px, 9vw, 100px);
            border-radius: 50%;
            background: var(--gold-rich);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border: 4px solid #fff;
            outline: 2px solid #cfa44a;
        }
        .badge-text {
            font-family: 'Cinzel', serif;
            font-size: clamp(6px, 0.9vw, 10px);
            font-weight: bold;
            text-align: center; line-height: 1.3;
            color: #3d2e10; letter-spacing: 1px;
        }

        /* --- 🔧 FEATURE 1 & 2: ID and QR Styling --- */
        .cert-id-display {
            position: absolute;
            bottom: 35px;
            left: 50px;
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 1vw, 10px);
            color: #666;
            text-align: left;
            z-index: 20;
        }
        .qr-code-container {
            position: absolute;
            bottom: 35px;
            right: 50px;
            z-index: 20;
            background: white;
            padding: 5px;
            border: 1px solid #ddd;
        }
        .qr-code-img {
            width: clamp(50px, 8vw, 80px);
            height: clamp(50px, 8vw, 80px);
        }

        /* Print Button */
        .print-btn { 
            position: fixed; bottom: 30px; right: 30px; 
            background: var(--gold-darker); color: #1a1a1a; 
            padding: 15px 30px; border-radius: 4px; 
            cursor: pointer; font-family: 'Cinzel', serif; 
            font-weight: bold; letter-spacing: 1px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.2); 
            border: none; transition: all 0.3s; z-index: 100; 
        }
        .print-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(0,0,0,0.3); }

        /* Error Box */
        .error-box { background: #fee2e2; border: 2px solid #ef4444; color: #b91c1c; padding: 2rem; border-radius: 0.5rem; text-align: center; z-index: 50; }

        /* --- PDF / PRINT SETTINGS --- */
        @media print {
            @page { size: landscape; margin: 0; }
            body { background: white; margin: 0; padding: 0; display: block; }
            .certificate-container { 
                margin: 0; box-shadow: none; width: 100% !important; max-width: none !important; 
                height: 100vh !important; border: none; page-break-after: always;
            }
            .content-wrapper { padding: 40px 60px; }
            .no-print, .print-btn { display: none; }
            /* Ensure background images (watermark) print */
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>

<body>

    <?php if (!$is_complete): ?>
        <div class="certificate-container" style="display:flex; justify-content:center; align-items:center;">
            <div class="error-box">
                <h2 class="text-3xl font-bold mb-4">Course Not Yet Completed!</h2>
                <p class="text-lg mb-4">You must complete all <b><?php echo $total_modules; ?></b> modules.<br>You currently have <b><?php echo $completed_modules; ?></b> complete.</p>
                <a href="./dashboard.php?id=<?php echo $course_id; ?>" class="inline-block bg-red-700 text-white font-bold py-2 px-4 rounded hover:bg-red-800 no-print" style="text-decoration:none;">Return to Course</a>
            </div>
        </div>
    <?php else: ?>

        <div class="certificate-container">
            <div class="shape-bl-black"></div>
            <div class="shape-bl-gold"></div>
            <div class="shape-tr-black"></div>
            <div class="shape-tr-gold"></div>

            <div class="watermark-pattern"></div>

            <div class="inner-border-container"></div>

            <div class="content-wrapper">
                
                <div class="company-name">Academic Excellence Award</div>
                
                <h1 class="cert-title">Certificate</h1>
                <div class="cert-subtitle">of Completion</div>

                <div class="divider-line"></div>

                <div class="ribbon-banner">
                    <span class="ribbon-text">Is hereby proudly presented to</span>
                </div>

                <div class="student-name"><?php echo htmlspecialchars($user_name); ?></div>

                <p class="description-text">
                    For having successfully completed the required course of study and meeting all the necessary standards set forth by the institution for the program titled:
                    <span class="course-highlight">"<?php echo $course_title; ?>"</span>
                </p>

                <div class="footer-row">
                    <div class="signature-block">
                        <div class="line generated-val"><?php echo $completion_date; ?></div>
                        <div class="sign-text">Date Issued</div>
                    </div>

                    <div class="badge">
                        <div class="badge-text">OFFICIAL<br>SEAL<br>2025</div>
                    </div>

                    <div class="signature-block">
                        <div class="line generated-val" style="font-family:'Great Vibes'; font-size: clamp(20px, 3vw, 32px);">
                            <?php echo $lecturer_name; ?>
                        </div>
                        <div class="sign-text">Instructor Signature</div>
                    </div>
                </div>

                <div class="cert-id-display">
                    <strong>Certificate ID:</strong><br>
                    <?php echo $certificate_id; ?>
                </div>

                <div class="qr-code-container">
                    <img src="<?php echo $qr_code_url; ?>" class="qr-code-img" alt="Verification QR">
                </div>

            </div>
        </div>

        <button onclick="window.print()" class="print-btn">PRINT CERTIFICATE</button>

    <?php endif; ?>

</body>
</html>
<?php
} else {
    header("Location: ../auth/dashboard.php");
    exit;
}
?>