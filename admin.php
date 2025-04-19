<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, phone, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get counts for dashboard
$stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM appointments) as total_appointments,
        (SELECT COUNT(*) FROM emergency_requests) as total_emergencies,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM appointments WHERE status = 'scheduled') as pending_appointments,
        (SELECT COUNT(*) FROM appointments WHERE status = 'completed') as completed_appointments,
        (SELECT COUNT(*) FROM appointments WHERE status = 'cancelled') as cancelled_appointments
");
$stmt->execute();
$result = $stmt->get_result();
$counts = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JUSTSmile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #0ea5e9;
            --success-color: #10b981;
            --danger-color: #f43f5e;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f1f5f9;
            --dark-color: #0f172a;
            --text-color: #334155;
            --border-color: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: var(--text-color);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid var(--border-color);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h1 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: var(--text-color);
            opacity: 0.7;
            font-size: 0.875rem;
        }

        .nav-menu {
            flex: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: var(--light-color);
            color: var(--primary-color);
        }

        .nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-item i {
            font-size: 1.25rem;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .header {
            background: white;
            padding: 1rem 2rem;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info span {
            color: var(--text-color);
            font-weight: 500;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .card h3 {
            color: var(--text-color);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card h3 i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .card .number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }

        .content-section {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background: white;
            color: var(--text-color);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background: var(--light-color);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: var(--light-color);
            font-weight: 600;
            color: var(--text-color);
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background: var(--light-color);
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-right: 0.5rem;
            color: white;
            background: var(--primary-color);
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-scheduled {
            background: #e0e7ff;
            color: #4338ca;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Settings Styles */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .settings-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .settings-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .settings-card h4 {
            color: var(--text-color);
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .settings-card h4 i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .checkbox-group {
            flex-direction: row;
            align-items: center;
            gap: 0.75rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 0.25rem;
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                padding: 1rem;
            }

            .sidebar-header h1,
            .sidebar-header p,
            .nav-item span {
                display: none;
            }

            .nav-item {
                justify-content: center;
                padding: 0.75rem;
            }

            .nav-item i {
                margin: 0;
            }

            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .dashboard {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
            }

            .settings-container {
                grid-template-columns: 1fr;
            }
        }

        /* Add notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem;
            border-radius: 0.5rem;
            background: white;
            box-shadow: var(--card-shadow);
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            border-left: 4px solid var(--success-color);
        }

        .notification.error {
            border-left: 4px solid var(--danger-color);
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .notification i {
            font-size: 1.25rem;
        }

        .notification.success i {
            color: var(--success-color);
        }

        .notification.error i {
            color: var(--danger-color);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Emergency Section Styles - Updated */
        .emergency-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .emergency-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .emergency-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #343a40;
        }

        .emergency-title i {
            color: #e74c3c;
            font-size: 2rem;
            background: rgba(231, 76, 60, 0.1);
            padding: 0.75rem;
            border-radius: 1rem;
        }

        .emergency-filters {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .emergency-filter {
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .emergency-filter i {
            font-size: 1rem;
        }

        .emergency-filter.active {
            background: #e74c3c;
            color: white;
        }

        .emergency-filter:hover:not(.active) {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .emergency-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .emergency-stat {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .emergency-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .emergency-stat.pending::before {
            background: linear-gradient(90deg, #f39c12, #f1c40f);
        }

        .emergency-stat.in-progress::before {
            background: linear-gradient(90deg, #3498db, #2980b9);
        }

        .emergency-stat.completed::before {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .emergency-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.pending {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }

        .stat-icon.in-progress {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .stat-icon.completed {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        .emergency-list {
            display: grid;
            gap: 1.5rem;
        }

        .emergency-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .emergency-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 3rem 3rem 0;
            opacity: 0.1;
        }

        .emergency-card.pending::after {
            border-color: transparent #f39c12 transparent transparent;
        }

        .emergency-card.in-progress::after {
            border-color: transparent #3498db transparent transparent;
        }

        .emergency-card.completed::after {
            border-color: transparent #2ecc71 transparent transparent;
        }

        .emergency-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .emergency-content {
            display: grid;
            gap: 1rem;
        }

        .emergency-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .emergency-patient {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .patient-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: #e74c3c;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .patient-info {
            display: grid;
            gap: 0.25rem;
        }

        .patient-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .patient-contact {
            color: #7f8c8d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .emergency-status {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }

        .status-in_progress {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .status-completed {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .status-cancelled {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .emergency-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .detail-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .detail-text {
            display: grid;
            gap: 0.25rem;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .detail-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .emergency-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-end;
            justify-content: center;
        }

        .action-btn {
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            min-width: 140px;
            justify-content: center;
        }

        .action-btn i {
            font-size: 1rem;
        }

        .action-btn.progress {
            background: #3498db;
        }

        .action-btn.complete {
            background: #2ecc71;
        }

        .action-btn.cancel {
            background: #e74c3c;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 992px) {
            .emergency-card {
                grid-template-columns: 1fr;
            }

            .emergency-actions {
                flex-direction: row;
                justify-content: flex-start;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
            }
        }

        @media (max-width: 768px) {
            .emergency-filters {
                flex-direction: column;
                width: 100%;
            }

            .emergency-filter {
                width: 100%;
                justify-content: center;
            }

            .emergency-stats {
                grid-template-columns: 1fr;
            }

            .emergency-details {
                grid-template-columns: 1fr;
            }
        }

        /* Custom Alert and Confirm Panel Styles */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .custom-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            width: 90%;
            max-width: 400px;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .custom-modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .modal-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .modal-icon.alert {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .modal-icon.confirm {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .modal-message {
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-btn.cancel {
            background: #f8f9fa;
            color: #7f8c8d;
        }

        .modal-btn.confirm {
            background: #3498db;
            color: white;
        }

        .modal-btn.danger {
            background: #e74c3c;
            color: white;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                padding: 1.5rem;
            }

            .modal-actions {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Custom Alert Modal -->
    <div id="customAlert" class="custom-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon alert">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="modal-title">Alert</div>
            </div>
            <div class="modal-message" id="alertMessage"></div>
            <div class="modal-actions">
                <button class="modal-btn confirm" onclick="closeCustomAlert()">
                    <i class="fas fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Confirm Modal -->
    <div id="customConfirm" class="custom-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon confirm">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="modal-title">Confirm</div>
            </div>
            <div class="modal-message" id="confirmMessage"></div>
            <div class="modal-actions">
                <button class="modal-btn cancel" onclick="closeCustomConfirm(false)">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="modal-btn confirm" onclick="closeCustomConfirm(true)">
                    <i class="fas fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-header">
            <h1>JUSTSmile</h1>
            <p>Admin Dashboard</p>
        </div>
        <nav class="nav-menu">
            <a href="#" class="nav-item active" data-section="appointments">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item" data-section="appointments">
                <i class="fas fa-calendar-check"></i>
                <span>Appointments</span>
            </a>
            <a href="#" class="nav-item" data-section="emergencies">
                <i class="fas fa-ambulance"></i>
                <span>Emergencies</span>
            </a>
            <a href="#" class="nav-item" data-section="users">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="#" class="nav-item" data-section="settings">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?></h2>
            <div class="user-info">
                <span>Administrator</span>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>

        <div class="dashboard">
            <div class="card">
                <h3><i class="fas fa-calendar-check"></i> Total Appointments</h3>
                <div class="number"><?php echo $counts['total_appointments']; ?></div>
            </div>
            <div class="card">
                <h3><i class="fas fa-ambulance"></i> Emergency Requests</h3>
                <div class="number"><?php echo $counts['total_emergencies']; ?></div>
            </div>
            <div class="card">
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <div class="number"><?php echo $counts['total_users']; ?></div>
            </div>
        </div>

        <div class="content-section" id="appointments">
            <div class="section-header">
                <h3 class="section-title">Recent Appointments</h3>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterAppointments('all')">All</button>
                    <button class="filter-btn" onclick="filterAppointments('scheduled')">Scheduled</button>
                    <button class="filter-btn" onclick="filterAppointments('completed')">Completed</button>
                    <button class="filter-btn" onclick="filterAppointments('cancelled')">Cancelled</button>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="appointmentsTableBody"></tbody>
            </table>
        </div>

        <div class="content-section" id="emergencies" style="display: none;">
            <div class="emergency-section">
                <div class="emergency-header">
                    <div class="emergency-title">
                        <i class="fas fa-ambulance"></i>
                        <span>Emergency Requests</span>
                    </div>
                    <div class="emergency-filters">
                        <button class="emergency-filter active" onclick="filterEmergencies('all')">
                            <i class="fas fa-list"></i> All
                        </button>
                        <button class="emergency-filter" onclick="filterEmergencies('pending')">
                            <i class="fas fa-clock"></i> Pending
                        </button>
                        <button class="emergency-filter" onclick="filterEmergencies('in_progress')">
                            <i class="fas fa-spinner fa-spin"></i> In Progress
                        </button>
                        <button class="emergency-filter" onclick="filterEmergencies('completed')">
                            <i class="fas fa-check-circle"></i> Completed
                        </button>
                        <button class="emergency-filter" onclick="filterEmergencies('cancelled')">
                            <i class="fas fa-times-circle"></i> Cancelled
                        </button>
                    </div>
                </div>
                
                <div class="emergency-stats">
                    <div class="emergency-stat pending">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="pendingCount">0</div>
                            <div class="stat-label">Pending Requests</div>
                        </div>
                    </div>
                    <div class="emergency-stat in-progress">
                        <div class="stat-icon in-progress">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="inProgressCount">0</div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                    <div class="emergency-stat completed">
                        <div class="stat-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="completedCount">0</div>
                            <div class="stat-label">Completed Today</div>
                        </div>
                    </div>
                </div>
                
                <div class="emergency-list" id="emergenciesTableBody">
                    <!-- Emergency cards will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <div class="content-section" id="users" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">User Management</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody"></tbody>
            </table>
        </div>

        <div class="content-section" id="settings" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">System Settings</h3>
            </div>
            
            <div class="settings-container">
                <div class="settings-card">
                    <h4><i class="fas fa-user-shield"></i> Admin Profile</h4>
                    <form id="adminProfileForm" class="settings-form">
                        <div class="form-group">
                            <label for="adminName">Name</label>
                            <input type="text" id="adminName" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="adminPhone">Phone</label>
                            <input type="text" id="adminPhone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="adminPassword">New Password (leave blank to keep current)</label>
                            <input type="password" id="adminPassword" name="password">
                        </div>
                        <div class="form-group">
                            <label for="adminConfirmPassword">Confirm New Password</label>
                            <input type="password" id="adminConfirmPassword" name="confirm_password">
                        </div>
                        <button type="submit" class="btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h4><i class="fas fa-clock"></i> Appointment Settings</h4>
                    <form id="appointmentSettingsForm" class="settings-form">
                        <div class="form-group">
                            <label for="workingHoursStart">Working Hours Start</label>
                            <input type="time" id="workingHoursStart" name="working_hours_start" value="09:00" required>
                        </div>
                        <div class="form-group">
                            <label for="workingHoursEnd">Working Hours End</label>
                            <input type="time" id="workingHoursEnd" name="working_hours_end" value="21:00" required>
                        </div>
                        <div class="form-group">
                            <label for="appointmentDuration">Appointment Duration (minutes)</label>
                            <select id="appointmentDuration" name="appointment_duration">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">1 hour</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="maxAppointmentsPerDay">Maximum Appointments Per Day</label>
                            <input type="number" id="maxAppointmentsPerDay" name="max_appointments_per_day" value="20" min="1" max="100" required>
                        </div>
                        <button type="submit" class="btn-primary">Save Appointment Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let appointmentsData = [];
        let emergenciesData = [];
        let usersData = [];

        // Load all data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadAppointments();
            loadEmergencies();
            loadUsers();
            loadSettings();
        });

        // Navigation handling
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                // Remove active class from all items
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
                
                // Handle navigation
                const section = this.getAttribute('data-section');
                if (section) {
                    showSection(section);
                }
            });
        });

        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            const selectedSection = document.getElementById(section);
            if (selectedSection) {
                selectedSection.style.display = 'block';
                // Load data for the section
                switch(section) {
                    case 'appointments':
                        loadAppointments();
                        break;
                    case 'emergencies':
                        loadEmergencies();
                        break;
                    case 'users':
                        loadUsers();
                        break;
                    case 'settings':
                        loadSettings();
                        break;
                }
            }
        }

        function loadAppointments() {
            fetch('get_appointments.php')
                .then(response => response.json())
                .then(data => {
                    appointmentsData = data;
                    const tbody = document.getElementById('appointmentsTableBody');
                    tbody.innerHTML = '';
                    data.forEach(appointment => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${appointment.id}</td>
                                <td>${appointment.name}</td>
                                <td>${appointment.phone}</td>
                                <td>${appointment.appointment_date}</td>
                                <td>${appointment.appointment_time}</td>
                                <td>${appointment.address}</td>
                                <td><span class="status-badge status-${appointment.status.toLowerCase()}">${appointment.status}</span></td>
                                <td>
                                    <button class="action-btn" onclick="updateAppointmentStatus(${appointment.id}, 'completed')" title="Mark as Completed">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn" onclick="updateAppointmentStatus(${appointment.id}, 'cancelled')" title="Cancel Appointment">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to load appointments', 'error');
                });
        }

        function loadEmergencies() {
            fetch('get_emergencies.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const emergencyList = document.querySelector('.emergency-list');
                        emergencyList.innerHTML = '';
                        
                        data.emergencies.forEach(emergency => {
                            const card = document.createElement('div');
                            card.className = 'emergency-card';
                            card.innerHTML = `
                                <div class="emergency-header">
                                    <div class="patient-info">
                                        <div class="patient-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="patient-details">
                                            <h3>${emergency.name}</h3>
                                            <p class="phone"><i class="fas fa-phone"></i> ${emergency.phone}</p>
                                        </div>
                                    </div>
                                    <div class="status-badge ${emergency.status}">${emergency.status.replace('_', ' ')}</div>
                                </div>
                                <div class="emergency-body">
                                    <div class="detail-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${emergency.location}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>${emergency.issue}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span>${new Date(emergency.created_at).toLocaleString()}</span>
                                    </div>
                                </div>
                                <div class="emergency-actions">
                                    <button class="action-btn accept" onclick="updateEmergencyStatus(${emergency.id}, 'in_progress')" ${emergency.status !== 'pending' ? 'disabled' : ''}>
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                    <button class="action-btn complete" onclick="updateEmergencyStatus(${emergency.id}, 'completed')" ${emergency.status !== 'in_progress' ? 'disabled' : ''}>
                                        <i class="fas fa-check-double"></i> Complete
                                    </button>
                                    <button class="action-btn cancel" onclick="updateEmergencyStatus(${emergency.id}, 'cancelled')" ${emergency.status === 'completed' || emergency.status === 'cancelled' ? 'disabled' : ''}>
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            `;
                            emergencyList.appendChild(card);
                        });
                    } else {
                        showNotification('Error loading emergency data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error loading emergency data', 'error');
                });
        }

        function loadUsers() {
            fetch('get_users.php')
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        usersData = response.data;
                        const tbody = document.getElementById('usersTableBody');
                        tbody.innerHTML = '';
                        response.data.forEach(user => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.name}</td>
                                    <td>${user.phone}</td>
                                    <td>${user.role || 'User'}</td>
                                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                                    <td>
                                        <button class="action-btn" onclick="deleteUser(${user.id})" title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        throw new Error(response.message || 'Failed to load users');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification(error.message, 'error');
                });
        }

        function loadSettings() {
            // Load admin profile settings
            fetch('get_admin_settings.php')
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        const settings = response.data;
                        
                        // Populate appointment settings
                        if (settings.appointment_settings) {
                            document.getElementById('workingHoursStart').value = settings.appointment_settings.working_hours_start || '09:00';
                            document.getElementById('workingHoursEnd').value = settings.appointment_settings.working_hours_end || '21:00';
                            document.getElementById('appointmentDuration').value = settings.appointment_settings.appointment_duration || '30';
                            document.getElementById('maxAppointmentsPerDay').value = settings.appointment_settings.max_appointments_per_day || '20';
                        }
                    } else {
                        throw new Error(response.message || 'Failed to load settings');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification(error.message, 'error');
                });
        }

        function filterAppointments(status) {
            // Update filter buttons
            document.querySelectorAll('#appointments .filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const tbody = document.getElementById('appointmentsTableBody');
            tbody.innerHTML = '';
            
            const filteredData = status === 'all' 
                ? appointmentsData 
                : appointmentsData.filter(appointment => appointment.status.toLowerCase() === status);
            
            filteredData.forEach(appointment => {
                tbody.innerHTML += `
                    <tr>
                        <td>${appointment.id}</td>
                        <td>${appointment.name}</td>
                        <td>${appointment.phone}</td>
                        <td>${appointment.appointment_date}</td>
                        <td>${appointment.appointment_time}</td>
                        <td>${appointment.address}</td>
                        <td><span class="status-badge status-${appointment.status.toLowerCase()}">${appointment.status}</span></td>
                        <td>
                            <button class="action-btn" onclick="updateAppointmentStatus(${appointment.id}, 'completed')" title="Mark as Completed">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="action-btn" onclick="updateAppointmentStatus(${appointment.id}, 'cancelled')" title="Cancel Appointment">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        function getStatusIcon(status) {
            switch(status) {
                case 'pending': return 'clock';
                case 'in_progress': return 'spinner fa-spin';
                case 'completed': return 'check-circle';
                case 'cancelled': return 'times-circle';
                default: return 'question-circle';
            }
        }

        function formatStatus(status) {
            switch(status) {
                case 'pending': return 'Pending';
                case 'in_progress': return 'In Progress';
                case 'completed': return 'Completed';
                case 'cancelled': return 'Cancelled';
                default: return status;
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }

        function getActionButtons(emergency) {
            let buttons = '';
            
            if (emergency.status === 'pending') {
                buttons += `
                    <button class="action-btn progress" onclick="updateEmergencyStatus(${emergency.id}, 'in_progress')">
                        <i class="fas fa-play"></i> Start
                    </button>
                `;
            } else if (emergency.status === 'in_progress') {
                buttons += `
                    <button class="action-btn complete" onclick="updateEmergencyStatus(${emergency.id}, 'completed')">
                        <i class="fas fa-check"></i> Complete
                    </button>
                `;
            }
            
            if (emergency.status !== 'completed' && emergency.status !== 'cancelled') {
                buttons += `
                    <button class="action-btn cancel" onclick="updateEmergencyStatus(${emergency.id}, 'cancelled')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                `;
            }
            
            return buttons;
        }

        function filterEmergencies(status) {
            // Update filter buttons
            document.querySelectorAll('.emergency-filter').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const tbody = document.getElementById('emergenciesTableBody');
            tbody.innerHTML = '';
            
            const filteredData = status === 'all' 
                ? emergenciesData 
                : emergenciesData.filter(emergency => emergency.status.toLowerCase() === status);
            
            // Re-render the filtered emergencies
            filteredData.forEach(emergency => {
                const statusClass = `status-${emergency.status.toLowerCase()}`;
                const cardClass = `emergency-card ${emergency.status.toLowerCase()}`;
                const patientInitial = emergency.name.charAt(0).toUpperCase();
                
                tbody.innerHTML += `
                    <div class="${cardClass}">
                        <div class="emergency-content">
                            <div class="emergency-top">
                                <div class="emergency-patient">
                                    <div class="patient-avatar">${patientInitial}</div>
                                    <div class="patient-info">
                                        <div class="patient-name">${emergency.name}</div>
                                        <div class="patient-contact">
                                            <i class="fas fa-phone"></i> ${emergency.phone}
                                        </div>
                                    </div>
                                </div>
                                <div class="emergency-status ${statusClass}">
                                    <i class="fas fa-${getStatusIcon(emergency.status)}"></i>
                                    ${formatStatus(emergency.status)}
                                </div>
                            </div>
                            <div class="emergency-details">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="detail-text">
                                        <div class="detail-label">Location</div>
                                        <div class="detail-value">${emergency.location}</div>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="detail-text">
                                        <div class="detail-label">Date</div>
                                        <div class="detail-value">${formatDate(emergency.created_at)}</div>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="detail-text">
                                        <div class="detail-label">Time</div>
                                        <div class="detail-value">${formatTime(emergency.created_at)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="emergency-actions">
                            ${getActionButtons(emergency)}
                        </div>
                    </div>
                `;
            });
        }

        // Custom Alert and Confirm Functions
        let confirmCallback = null;

        function showCustomAlert(message) {
            const modal = document.getElementById('customAlert');
            const messageEl = document.getElementById('alertMessage');
            messageEl.textContent = message;
            modal.classList.add('show');
        }

        function closeCustomAlert() {
            const modal = document.getElementById('customAlert');
            modal.classList.remove('show');
        }

        function showCustomConfirm(message, callback) {
            const modal = document.getElementById('customConfirm');
            const messageEl = document.getElementById('confirmMessage');
            messageEl.textContent = message;
            confirmCallback = callback;
            modal.classList.add('show');
        }

        function closeCustomConfirm(result) {
            const modal = document.getElementById('customConfirm');
            modal.classList.remove('show');
            if (confirmCallback) {
                confirmCallback(result);
                confirmCallback = null;
            }
        }

        // Replace default alert and confirm
        window.alert = showCustomAlert;
        window.confirm = showCustomConfirm;

        // Update existing functions to use custom alerts
        function updateAppointmentStatus(id, status) {
            showCustomConfirm(`Are you sure you want to mark this appointment as ${status}?`, (confirmed) => {
                if (confirmed) {
                    fetch('update_appointment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: id,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Appointment status updated successfully', 'success');
                            loadAppointments();
                        } else {
                            throw new Error(data.message || 'Failed to update appointment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification(error.message, 'error');
                    });
                }
            });
        }

        function updateEmergencyStatus(id, status) {
            showCustomConfirm(`Are you sure you want to mark this emergency as ${status}?`, (confirmed) => {
                if (confirmed) {
                    fetch('update_emergency_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id, status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Emergency status updated successfully', 'success');
                            loadEmergencies();
                        } else {
                            throw new Error(data.message || 'Failed to update emergency');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification(error.message, 'error');
                    });
                }
            });
        }

        function deleteUser(id) {
            showCustomConfirm('Are you sure you want to delete this user? This action cannot be undone.', (confirmed) => {
                if (confirmed) {
                    fetch('delete_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('User deleted successfully', 'success');
                            loadUsers();
                        } else {
                            throw new Error(data.message || 'Failed to delete user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification(error.message, 'error');
                    });
                }
            });
        }

        function logout() {
            showCustomConfirm('Are you sure you want to logout?', (confirmed) => {
                if (confirmed) {
                    fetch('logout.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = 'login.html';
                            } else {
                                throw new Error('Failed to logout');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to logout', 'error');
                        });
                }
            });
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Admin Profile Form Submission
        document.getElementById('adminProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('adminPassword').value;
            const confirmPassword = document.getElementById('adminConfirmPassword').value;
            
            if (password && password !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            
            const formData = {
                name: document.getElementById('adminName').value,
                phone: document.getElementById('adminPhone').value
            };
            
            if (password) {
                formData.password = password;
            }
            
            fetch('update_admin_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Profile updated successfully', 'success');
                    // Clear password fields
                    document.getElementById('adminPassword').value = '';
                    document.getElementById('adminConfirmPassword').value = '';
                } else {
                    throw new Error(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            });
        });

        // Appointment Settings Form Submission
        document.getElementById('appointmentSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                working_hours_start: document.getElementById('workingHoursStart').value,
                working_hours_end: document.getElementById('workingHoursEnd').value,
                appointment_duration: document.getElementById('appointmentDuration').value,
                max_appointments_per_day: document.getElementById('maxAppointmentsPerDay').value
            };
            
            fetch('update_appointment_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Appointment settings updated successfully', 'success');
                } else {
                    throw new Error(data.message || 'Failed to update appointment settings');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            });
        });
    </script>
</body>
</html>