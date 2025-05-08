<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'emergency_details_error.log');

require_once 'conn.php';
session_start();

// Check if ID is provided
$emergency_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$emergency_id) {
    error_log("No emergency ID provided");
    header('Location: admin.php');
    exit;
}

// Get emergency details
try {
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }

    // Log the query we're about to run
    error_log("Fetching emergency data for ID: " . $emergency_id);
    
    $stmt = $conn->prepare("SELECT * FROM emergency_requests WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $emergency_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute statement failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Emergency not found for ID: " . $emergency_id);
        header('Location: admin.php');
        exit;
    }
    
    $emergency = $result->fetch_assoc();
    error_log("Emergency data retrieved successfully for ID: " . $emergency_id);
} catch (Exception $e) {
    error_log("Error fetching emergency: " . $e->getMessage());
    echo "<div style='color:red; padding:20px; background-color:#ffe0e0; border:1px solid #900; margin:20px;'>
            <h3>Error Loading Emergency Data</h3>
            <p>We encountered an error while loading the emergency request data.</p>
            <p>Error details: " . htmlspecialchars($e->getMessage()) . "</p>
            <p><a href='admin.php'>Return to Admin Panel</a></p>
          </div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Request Details</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .emergency-details {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: #fff;
        }
        
        .emergency-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .patient-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #f2f2f2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.8rem;
            color: #666;
        }
        
        .patient-info h2 {
            margin: 0;
            font-size: 1.6rem;
        }
        
        .patient-info p {
            margin: 0.5rem 0 0;
            color: #666;
        }
        
        .status-badge {
            margin-left: auto;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .emergency-description {
            padding: 1.5rem;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .action-button svg {
            margin-right: 0.5rem;
            width: 18px;
            height: 18px;
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
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .action-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .action-button:disabled:hover {
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="emergency-details">
        <div class="emergency-header">
            <div class="patient-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="patient-info">
                <h2><?php echo htmlspecialchars($emergency['name'] ?? 'Unknown'); ?></h2>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($emergency['phone'] ?? 'N/A'); ?></p>
            </div>
            <span class="status-badge status-<?php echo $emergency['status'] ?? 'pending'; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $emergency['status'] ?? 'pending')); ?>
            </span>
        </div>
        
        <div class="emergency-description">
            <h3>Issue Description</h3>
            <p><?php echo nl2br(htmlspecialchars($emergency['issue'] ?? 'No description provided')); ?></p>
        </div>
        
        <div class="action-buttons">
            <button class="action-button btn-accept" <?php echo (!isset($emergency['status']) || $emergency['status'] !== 'pending') ? 'disabled' : ''; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Accept
            </button>
            
            <button class="action-button btn-complete" <?php echo (!isset($emergency['status']) || $emergency['status'] !== 'in_progress') ? 'disabled' : ''; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                Complete
            </button>
            
            <button class="action-button btn-cancel" <?php echo (!isset($emergency['status']) || $emergency['status'] === 'completed' || $emergency['status'] === 'cancelled') ? 'disabled' : ''; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Cancel
            </button>
        </div>
    </div>
    
    <!-- Add data ID for JavaScript -->
    <div id="emergencyData" data-emergency-id="<?php echo htmlspecialchars($emergency_id); ?>" style="display:none;"></div>
    
    <script>
    // Debug information for console
    console.log('Emergency ID:', <?php echo json_encode($emergency_id); ?>);
    console.log('Emergency status:', <?php echo json_encode($emergency['status'] ?? null); ?>);
    </script>
    
    <script src="emergency-detail.js"></script>
</body>
</html> 