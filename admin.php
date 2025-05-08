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

// Check if email field exists in users table
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
$hasEmailField = ($result->num_rows > 0);

if ($hasEmailField) {
    $stmt = $conn->prepare("SELECT name, phone, email, created_at FROM users WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT name, phone, created_at FROM users WHERE id = ?");
}

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
        (SELECT COUNT(*) FROM appointments WHERE status = 'cancelled') as cancelled_appointments,
        (SELECT COUNT(*) FROM consultations) as total_consultations,
        (SELECT COUNT(*) FROM consultations WHERE status = 'pending') as pending_consultations
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
            margin: 10px;
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
            background: linear-gradient(135deg, #1e1f21 0%, #2d3436 100%);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .emergency-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgdmlld0JveD0iMCAwIDUwIDUwIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDMiIGQ9Ik0xMiAxM2gxdjFoLTF6TTEzIDEzaDJ2MWgtMnpNMTUgMTNoMXYxaC0xek0xNiAxM2gxdjFoLTF6Ii8+PC9zdmc+');
            opacity: 0.3;
            z-index: 1;
        }

        .emergency-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 2;
        }

        .emergency-title {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            font-size: 1.75rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .emergency-title i {
            color: #ff3b30;
            font-size: 2.25rem;
            background: rgba(255, 59, 48, 0.15);
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(255, 59, 48, 0.25);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(255, 59, 48, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 59, 48, 0);
            }
        }

        .emergency-filters {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .emergency-filter {
            padding: 0.75rem 1.5rem;
            border-radius: 4rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .emergency-filter i {
            font-size: 1rem;
        }

        .emergency-filter.active {
            background: #ff3b30;
            color: white;
            border-color: #ff3b30;
            box-shadow: 0 8px 15px rgba(255, 59, 48, 0.25);
        }

        .emergency-filter:hover:not(.active) {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.9);
        }

        .emergency-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 2;
        }

        .emergency-stat {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .emergency-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: -1;
        }

        .emergency-stat.pending {
            border-left: 4px solid #ff9500;
        }

        .emergency-stat.in-progress {
            border-left: 4px solid #007aff;
        }

        .emergency-stat.completed {
            border-left: 4px solid #34c759;
        }

        .emergency-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .stat-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-icon.pending {
            background: rgba(255, 149, 0, 0.15);
            color: #ff9500;
        }

        .stat-icon.in-progress {
            background: rgba(0, 122, 255, 0.15);
            color: #007aff;
        }

        .stat-icon.completed {
            background: rgba(52, 199, 89, 0.15);
            color: #34c759;
        }

        .stat-data {
            flex: 1;
        }

        .stat-data h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
        }

        .stat-data p {
            font-size: 2.25rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin: 0;
        }

        .emergency-list {
            display: grid;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .emergency-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .emergency-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            opacity: 1;
        }

        .emergency-card.pending::before {
            background: #ff9500;
        }

        .emergency-card.in_progress::before {
            background: #007aff;
        }

        .emergency-card.completed::before {
            background: #34c759;
        }

        .emergency-card.cancelled::before {
            background: #ff3b30;
        }

        .emergency-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 4rem 4rem 0;
            opacity: 0.1;
        }

        .emergency-card.pending::after {
            border-color: transparent #ff9500 transparent transparent;
        }

        .emergency-card.in_progress::after {
            border-color: transparent #007aff transparent transparent;
        }

        .emergency-card.completed::after {
            border-color: transparent #34c759 transparent transparent;
        }

        .emergency-card.cancelled::after {
            border-color: transparent #ff3b30 transparent transparent;
        }

        .emergency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .emergency-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .patient-avatar {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: rgba(255, 59, 48, 0.15);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.5rem;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .patient-details h3 {
            font-weight: 700;
            color: #fff;
            font-size: 1.25rem;
            margin: 0 0 0.35rem 0;
        }

        .patient-details p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: rgba(255, 149, 0, 0.15);
            color: #ff9500;
            border: 1px solid rgba(255, 149, 0, 0.3);
        }

        .status-badge.in_progress {
            background: rgba(0, 122, 255, 0.15);
            color: #007aff;
            border: 1px solid rgba(0, 122, 255, 0.3);
        }

        .status-badge.completed {
            background: rgba(52, 199, 89, 0.15);
            color: #34c759;
            border: 1px solid rgba(52, 199, 89, 0.3);
        }

        .status-badge.cancelled {
            background: rgba(255, 59, 48, 0.15);
            color: #ff3b30;
            border: 1px solid rgba(255, 59, 48, 0.3);
        }

        .emergency-body {
            display: grid;
            gap: 1.25rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .detail-item i {
            width: 2.5rem;
            min-width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
        }

        .detail-item span {
            font-weight: 500;
            line-height: 1.4;
        }

        .emergency-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
            justify-content: center;
        }

        .action-btn {
            padding: 0.85rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            min-width: 150px;
            justify-content: center;
        }

        .action-btn i {
            font-size: 1.1rem;
        }

        .action-btn.accept {
            background: linear-gradient(135deg, #007aff, #5856d6);
            box-shadow: 0 8px 15px rgba(0, 122, 255, 0.25);
        }

        .action-btn.complete {
            background: linear-gradient(135deg, #34c759, #32d74b);
            box-shadow: 0 8px 15px rgba(52, 199, 89, 0.25);
        }

        .action-btn.cancel {
            background: linear-gradient(135deg, #ff3b30, #ff453a);
            box-shadow: 0 8px 15px rgba(255, 59, 48, 0.25);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
        }

        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 992px) {
            .emergency-card {
                grid-template-columns: 1fr;
            }

            .emergency-actions {
                flex-direction: row;
                justify-content: flex-start;
                margin-top: 1.5rem;
                padding-top: 1.5rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
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
        }

        /* Custom Modal Styles */
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

        /* Add these styles to your existing CSS */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .status-approved {
            background-color: var(--success-color);
            color: white;
        }

        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            margin-right: 0.25rem;
            transition: all 0.2s;
        }

        .view-btn {
            background-color: var(--info-color);
            color: white;
        }

        .approve-btn {
            background-color: var(--success-color);
            color: white;
        }

        .reject-btn {
            background-color: var(--danger-color);
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 2.5rem;
            border-radius: 1.5rem;
            width: 90%;
            max-width: 900px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            color: var(--primary-color);
            font-size: 1.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.75rem;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-radius: 50%;
            background: var(--light-color);
        }

        .close:hover {
            background: var(--danger-color);
            color: white;
            transform: rotate(90deg);
        }
        .close-icon{
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            right: 10px;
            top: -10px;
            
        }
        .close-icon:hover{
            right: 10px;
            top: -15px;
            background: var(--danger-color);
            color: white;
            transform: rotate(90deg);
        }

        #consultationDetails {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .detail-group {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .detail-group:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-group h4 {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .detail-group h4 i {
            font-size: 1.25rem;
        }

        .detail-group p {
            color: var(--text-color);
            margin-bottom: 0.75rem;
            line-height: 1.6;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .detail-group p strong {
            min-width: 150px;
            color: var(--dark-color);
        }

        .consultation-image {
            max-width: 100%;
            border-radius: 1rem;
            margin-top: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .consultation-image:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 5% auto;
                padding: 1.5rem;
                width: 95%;
            }

            #consultationDetails {
                grid-template-columns: 1fr;
            }

            .detail-group p {
                flex-direction: column;
                gap: 0.25rem;
            }

            .detail-group p strong {
                min-width: auto;
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
            <a href="#" class="nav-item active" data-section="dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item" data-section="appointments">
                <i class="fas fa-calendar-alt"></i>
                <span>Appointments</span>
            </a>
            <a href="#" class="nav-item" data-section="emergencies">
                <i class="fas fa-ambulance"></i>
                <span>Emergencies</span>
            </a>
            <a href="#" class="nav-item" data-section="consultations">
                <i class="fas fa-comment-medical"></i>
                <span>Consultations</span>
            </a>
            <a href="#" class="nav-item" data-section="users">
                <i class="fas fa-users"></i>
                <span>Users</span>
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
            <div class="card">
                <h3><i class="fas fa-comment-medical"></i> Online Consultations</h3>
                <div class="number"><?php echo $counts['total_consultations']; ?></div>
                <p>Pending: <?php echo $counts['pending_consultations']; ?></p>
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
                        <div class="stat-data">
                            <h3>Pending</h3>
                            <p id="pendingEmergencyCount">0</p>
                        </div>
                    </div>
                    <div class="emergency-stat in-progress">
                        <div class="stat-icon in-progress">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="stat-data">
                            <h3>In Progress</h3>
                            <p id="inProgressEmergencyCount">0</p>
                        </div>
                    </div>
                    <div class="emergency-stat completed">
                        <div class="stat-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-data">
                            <h3>Completed</h3>
                            <p id="completedEmergencyCount">0</p>
                        </div>
                    </div>
                </div>
                
                <div class="emergency-list" id="emergenciesTableBody">
                    <!-- Emergency cards will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <div class="content-section" id="consultations" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">Online Consultation Requests</h3>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterConsultations('all')">All</button>
                    <button class="filter-btn" onclick="filterConsultations('pending')">Pending</button>
                    <button class="filter-btn" onclick="filterConsultations('approved')">Approved</button>
                    <button class="filter-btn" onclick="filterConsultations('rejected')">Rejected</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Contact</th>
                            <th>Preferred Date</th>
                            <th>Preferred Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="consultationsTableBody">
                        <?php
                        $consultations_query = "SELECT * FROM consultations ORDER BY submission_date DESC";
                        $consultations_result = $conn->query($consultations_query);
                        
                        while ($consultation = $consultations_result->fetch_assoc()) {
                            $status_class = '';
                            switch ($consultation['status']) {
                                case 'pending':
                                    $status_class = 'status-pending';
                                    break;
                                case 'approved':
                                    $status_class = 'status-approved';
                                    break;
                                case 'rejected':
                                    $status_class = 'status-rejected';
                                    break;
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($consultation['name']); ?></td>
                                <td><?php echo htmlspecialchars($consultation['age']); ?></td>
                                <td>
                                    Email: <?php echo htmlspecialchars($consultation['email']); ?><br>
                                    Phone: <?php echo htmlspecialchars($consultation['phone']); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($consultation['preferred_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($consultation['preferred_time'])); ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($consultation['status']); ?></span></td>
                                <td>
                                    <button class="action-btn view-btn" onclick="viewConsultation(<?php echo $consultation['id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn approve-btn" onclick="updateConsultationStatus(<?php echo $consultation['id']; ?>, 'approved')" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn reject-btn" onclick="updateConsultationStatus(<?php echo $consultation['id']; ?>, 'rejected')" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
    </div>

    <!-- Add Modal for Viewing Consultation Details -->
    <div id="consultationModal" class="modal">
        <div class="modal-content">
            <span class="close"><span class="close-icon">&times;</span></span>
            <h2>Consultation Details</h2>
            <div id="consultationDetails"></div>
        </div>
    </div>

    <script>
        let appointmentsData = [];
        let emergenciesData = [];
        let usersData = [];
        let confirmCallback = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Load initial data
            loadAppointments();
            loadEmergencies();
            loadUsers();
            
            // Set up section switching
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    
                    const section = this.getAttribute('data-section');
                    showSection(section);
                });
            });
        });
        
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(s => {
                s.style.display = 'none';
            });
            
            // Show selected section
            const selectedSection = document.getElementById(section);
            if (selectedSection) {
                selectedSection.style.display = 'block';
            }
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-section') === section) {
                    item.classList.add('active');
                }
            });
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
                        emergenciesData = data.emergencies;
                        
                        // Update stats
                        document.getElementById('pendingEmergencyCount').textContent = emergenciesData.filter(e => e.status === 'pending').length;
                        document.getElementById('inProgressEmergencyCount').textContent = emergenciesData.filter(e => e.status === 'in_progress').length;
                        document.getElementById('completedEmergencyCount').textContent = emergenciesData.filter(e => e.status === 'completed').length;
                        
                        // Render emergency cards
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
                                    ${getActionButtons(emergency)}
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
                                        <button class="action-btn" onclick="deleteUser(${user.id}, ${user.role === 'admin'})" title="Delete User">
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

        function deleteUser(id, isAdmin = false) {
            const confirmMessage = isAdmin 
                ? 'You are about to delete an ADMIN account. This action cannot be undone. Are you absolutely sure?' 
                : 'Are you sure you want to delete this user? This action cannot be undone.';
            
            showCustomConfirm(confirmMessage, (confirmed) => {
                if (confirmed) {
                    fetch('delete_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            id: id,
                            force_delete: isAdmin
                        })
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

        function filterEmergencies(status) {
            // Update filter buttons
            document.querySelectorAll('.emergency-filter').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const emergencyList = document.querySelector('.emergency-list');
            emergencyList.innerHTML = '';
            
            const filteredData = status === 'all' 
                ? emergenciesData 
                : emergenciesData.filter(emergency => emergency.status.toLowerCase() === status);
            
            filteredData.forEach(emergency => {
                const statusClass = `status-${emergency.status.toLowerCase()}`;
                const cardClass = `emergency-card ${emergency.status.toLowerCase()}`;
                
                const card = document.createElement('div');
                card.className = cardClass;
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
                        ${getActionButtons(emergency)}
                    </div>
                `;
                emergencyList.appendChild(card);
            });
        }
        
        function getActionButtons(emergency) {
            let buttons = '';
            
            if (emergency.status === 'pending') {
                buttons += `
                    <button class="action-btn accept" onclick="updateEmergencyStatus(${emergency.id}, 'in_progress')">
                        <i class="fas fa-check"></i> Accept
                    </button>
                `;
            } else if (emergency.status === 'in_progress') {
                buttons += `
                    <button class="action-btn complete" onclick="updateEmergencyStatus(${emergency.id}, 'completed')">
                        <i class="fas fa-check-double"></i> Complete
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

        // Custom Alert and Confirm Functions
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

        // Replace only window.alert with showCustomAlert to avoid recursion issues
        window.alert = showCustomAlert;
        // Do NOT replace window.confirm as this causes recursion issues

        // Initialize functionality
        document.addEventListener('DOMContentLoaded', function() {
            // initSettingsTabs - Removed
            // setupSettingsForms - Removed
            // setupBlockedDatesFunctionality - Removed
        });

        function viewConsultation(id) {
            const modal = document.getElementById('consultationModal');
            const detailsDiv = document.getElementById('consultationDetails');
            
            // Fetch consultation details
            fetch(`get_consultation_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    detailsDiv.innerHTML = `
                        <div class="detail-group">
                            <h4><i class="fas fa-user"></i> Personal Information</h4>
                            <p><strong>Name:</strong> ${data.name}</p>
                            <p><strong>Age:</strong> ${data.age}</p>
                            <p><strong>Email:</strong> ${data.email}</p>
                            <p><strong>Phone:</strong> ${data.phone}</p>
                        </div>
                        <div class="detail-group">
                            <h4><i class="fas fa-calendar-alt"></i> Preferred Schedule</h4>
                            <p><strong>Date:</strong> ${new Date(data.preferred_date).toLocaleDateString()}</p>
                            <p><strong>Time:</strong> ${new Date('1970-01-01T' + data.preferred_time).toLocaleTimeString()}</p>
                        </div>
                        <div class="detail-group">
                            <h4><i class="fas fa-notes-medical"></i> Medical Information</h4>
                            <p><strong>Dental History:</strong> ${data.dental_history || 'None provided'}</p>
                            <p><strong>Current Symptoms:</strong> ${data.current_symptoms}</p>
                            <p><strong>Medical Conditions:</strong> ${data.medical_conditions || 'None provided'}</p>
                            <p><strong>Medications:</strong> ${data.medications || 'None provided'}</p>
                        </div>
                        <div class="detail-group">
                            <h4><i class="fas fa-question-circle"></i> Additional Information</h4>
                            <p><strong>Questions:</strong> ${data.questions || 'None provided'}</p>
                        </div>
                        ${data.image_path ? `
                        <div class="detail-group">
                            <h4><i class="fas fa-camera"></i> Dental Image</h4>
                            <img src="${data.image_path}" alt="Dental Image" class="consultation-image">
                        </div>
                        ` : ''}
                    `;
                    modal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error loading consultation details', 'error');
                });
        }

        function updateConsultationStatus(id, status) {
            if (!confirm(`Are you sure you want to ${status} this consultation request?`)) {
                return;
            }
            
            fetch('update_consultation_status.php', {
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
                    location.reload();
                } else {
                    alert('Error updating consultation status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating consultation status');
            });
        }

        // Close modal when clicking the X or outside the modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('consultationModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('consultationModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function filterConsultations(status) {
            // Update filter buttons
            document.querySelectorAll('#consultations .filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const tbody = document.getElementById('consultationsTableBody');
            const rows = tbody.getElementsByTagName('tr');
            
            for (let row of rows) {
                const statusCell = row.querySelector('.status-badge');
                if (status === 'all' || statusCell.textContent.toLowerCase() === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>