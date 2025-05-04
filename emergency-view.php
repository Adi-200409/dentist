<?php
require_once 'conn.php';
session_start();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$emergency_id = intval($_GET['id']);

// Get emergency details
try {
    $stmt = $conn->prepare("SELECT * FROM emergency_requests WHERE id = ?");
    $stmt->bind_param("i", $emergency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Emergency not found
        header('Location: admin.php');
        exit;
    }
    
    $emergency = $result->fetch_assoc();
} catch (Exception $e) {
    // Log error
    error_log("Error fetching emergency: " . $e->getMessage());
    // Redirect to admin
    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Request #<?php echo $emergency['id']; ?> - JUSTSmile Dental Clinic</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .emergency-card {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background-color: white;
        }
        
        .emergency-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .emergency-icon {
            background-color: #f8d7da;
            color: #721c24;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        
        .emergency-title {
            display: flex;
            align-items: center;
        }
        
        .emergency-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .patient-info, .emergency-info {
            margin-bottom: 2rem;
        }
        
        .info-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #555;
        }
        
        .info-value {
            font-size: 1.1rem;
        }
        
        .emergency-description {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .emergency-description h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-accept {
            background-color: #4c52e0;
            color: white;
        }
        
        .btn-complete {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-cancel {
            background-color: #F44336;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn:disabled:hover {
            transform: none;
        }
        
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            animation: slideIn 0.3s ease-out forwards;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <header>
        <!-- Add your site header here -->
    </header>
    
    <main>
        <div class="emergency-card" data-emergency-id="<?php echo $emergency['id']; ?>">
            <div class="emergency-header">
                <div class="emergency-title">
                    <div class="emergency-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1>Emergency #<?php echo $emergency['id']; ?></h1>
                </div>
                <span class="emergency-status status-<?php echo $emergency['status']; ?>">
                    <?php echo str_replace('_', ' ', ucfirst($emergency['status'])); ?>
                </span>
            </div>
            
            <div class="patient-info">
                <h2>Patient Information</h2>
                <div class="info-group">
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($emergency['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo htmlspecialchars($emergency['phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Location</span>
                        <span class="info-value"><?php echo htmlspecialchars($emergency['location']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Urgency</span>
                        <span class="info-value"><?php echo htmlspecialchars($emergency['urgency']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="emergency-description">
                <h3>Emergency Description</h3>
                <p><?php echo nl2br(htmlspecialchars($emergency['issue'])); ?></p>
            </div>
            
            <div class="emergency-info">
                <h2>Additional Details</h2>
                <div class="info-group">
                    <div class="info-item">
                        <span class="info-label">Requested On</span>
                        <span class="info-value"><?php echo date('M d, Y h:i A', strtotime($emergency['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-accept Accept" <?php echo ($emergency['status'] !== 'pending') ? 'disabled' : ''; ?> data-action="accept">
                    <i class="fas fa-check"></i> Accept
                </button>
                <button class="btn btn-complete Complete" <?php echo ($emergency['status'] !== 'in_progress') ? 'disabled' : ''; ?> data-action="complete">
                    <i class="fas fa-check-double"></i> Complete
                </button>
                <button class="btn btn-cancel Cancel" <?php echo ($emergency['status'] === 'completed' || $emergency['status'] === 'cancelled') ? 'disabled' : ''; ?> data-action="cancel">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </main>
    
    <footer>
        <!-- Add your site footer here -->
    </footer>
    
    <script src="emergency-view.js"></script>
</body>
</html> 