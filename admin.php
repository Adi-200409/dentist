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

        /* Settings Section Styling */
        .settings-tabs {
            display: flex;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            overflow-x: auto;
            padding-bottom: 0.25rem;
            gap: 0.5rem;
        }

        .settings-tab {
            background: transparent;
            border: none;
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            font-weight: 500;
            color: #6c757d;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
            flex-shrink: 0;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .settings-tab:hover {
            color: #007bff;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .settings-tab.active {
            color: #007bff;
            background-color: rgba(13, 110, 253, 0.1);
            border-bottom: 3px solid #007bff;
        }

        .settings-tab i {
            margin-right: 6px;
        }

        .settings-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .settings-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .settings-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            height: fit-content;
        }

        .settings-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .settings-card h4 {
            color: #333;
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            position: relative;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .settings-card h4 i {
            color: #007bff;
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .form-row .form-group {
            flex: 1 1 220px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus, 
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon label i {
            color: #007bff;
            margin-right: 0.25rem;
        }

        .help-text {
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            margin-bottom: 0;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-primary, .btn-secondary {
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0069d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .blocked-dates-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }

        .blocked-date-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .blocked-date-item .date-info {
            display: flex;
            flex-direction: column;
        }

        .blocked-date-item .date {
            font-weight: 500;
        }

        .blocked-date-item .reason {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .blocked-date-item .delete-date {
            color: #dc3545;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .blocked-date-item .delete-date:hover {
            background: rgba(220, 53, 69, 0.1);
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
                <p class="section-description">Configure your clinic settings and preferences</p>
            </div>
            
            <div class="settings-tabs">
                <button class="settings-tab active" data-tab="profile">
                    <i class="fas fa-user-shield"></i> Profile
                </button>
                <button class="settings-tab" data-tab="appointments">
                    <i class="fas fa-calendar-alt"></i> Appointments
                </button>
                <button class="settings-tab" data-tab="notifications">
                    <i class="fas fa-bell"></i> Notifications
                </button>
                <button class="settings-tab" data-tab="system">
                    <i class="fas fa-cogs"></i> System
                </button>
            </div>
            
            <div class="settings-container" id="profileSettings">
                <div class="settings-card">
                    <h4><i class="fas fa-user-shield"></i> Admin Profile</h4>
                    <form id="adminProfileForm" class="settings-form">
                        <div class="form-row">
                            <div class="form-group input-with-icon">
                                <label for="adminName"><i class="fas fa-user"></i> Name</label>
                                <input type="text" id="adminName" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="adminPhone"><i class="fas fa-phone"></i> Phone</label>
                                <input type="text" id="adminPhone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                        </div>
                        <?php if (isset($hasEmailField) && $hasEmailField): ?>
                        <div class="form-group input-with-icon">
                            <label for="adminEmail"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="adminEmail" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
                            <p class="help-text">This email will be used for notifications and password recovery</p>
                        </div>
                        <?php endif; ?>
                        <div class="form-group input-with-icon">
                            <label for="adminPassword"><i class="fas fa-lock"></i> New Password</label>
                            <input type="password" id="adminPassword" name="password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="form-group input-with-icon">
                            <label for="adminConfirmPassword"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                            <input type="password" id="adminConfirmPassword" name="confirm_password" placeholder="Confirm your new password">
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h4><i class="fas fa-clinic-medical"></i> Clinic Information</h4>
                    <form id="clinicInfoForm" class="settings-form">
                        <div class="form-group input-with-icon">
                            <label for="clinicName"><i class="fas fa-hospital"></i> Clinic Name</label>
                            <input type="text" id="clinicName" name="clinic_name" value="JUSTSmile Dental Clinic">
                        </div>
                        <div class="form-group input-with-icon">
                            <label for="clinicAddress"><i class="fas fa-map-marker-alt"></i> Address</label>
                            <input type="text" id="clinicAddress" name="clinic_address" placeholder="Enter clinic address">
                        </div>
                        <div class="form-row">
                            <div class="form-group input-with-icon">
                                <label for="clinicPhone"><i class="fas fa-phone"></i> Contact Phone</label>
                                <input type="text" id="clinicPhone" name="clinic_phone" placeholder="Enter contact phone">
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="clinicEmail"><i class="fas fa-envelope"></i> Contact Email</label>
                                <input type="email" id="clinicEmail" name="clinic_email" placeholder="Enter contact email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="clinicDescription"><i class="fas fa-info-circle"></i> Description</label>
                            <textarea id="clinicDescription" name="clinic_description" rows="3" placeholder="Short description about your clinic"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="settings-container" id="appointmentsSettings" style="display: none;">
                <div class="settings-card">
                    <h4><i class="fas fa-clock"></i> Appointment Settings</h4>
                    <form id="appointmentSettingsForm" class="settings-form">
                        <div class="form-row">
                            <div class="form-group input-with-icon">
                                <label for="workingHoursStart"><i class="fas fa-hourglass-start"></i> Working Hours Start</label>
                                <input type="time" id="workingHoursStart" name="working_hours_start" value="09:00" required>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="workingHoursEnd"><i class="fas fa-hourglass-end"></i> Working Hours End</label>
                                <input type="time" id="workingHoursEnd" name="working_hours_end" value="21:00" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group input-with-icon">
                                <label for="appointmentDuration"><i class="fas fa-stopwatch"></i> Appointment Duration</label>
                                <select id="appointmentDuration" name="appointment_duration">
                                    <option value="15">15 minutes</option>
                                    <option value="30" selected>30 minutes</option>
                                    <option value="45">45 minutes</option>
                                    <option value="60">1 hour</option>
                                </select>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="maxAppointmentsPerDay"><i class="fas fa-calendar-day"></i> Max Appointments Per Day</label>
                                <input type="number" id="maxAppointmentsPerDay" name="max_appointments_per_day" value="20" min="1" max="100" required>
                            </div>
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="allowWeekends" name="allow_weekends">
                            <label for="allowWeekends">Allow weekend appointments</label>
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="requireConfirmation" name="require_confirmation" checked>
                            <label for="requireConfirmation">Require admin confirmation for appointments</label>
                        </div>
                        <div class="form-group">
                            <label>Buffer Time Between Appointments</label>
                            <div class="form-row">
                                <div class="form-group input-with-icon">
                                    <input type="number" id="bufferTime" name="buffer_time" value="10" min="0" max="60">
                                    <p class="help-text">Minutes between appointments for preparation</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h4><i class="fas fa-ban"></i> Blocked Dates</h4>
                    <form id="blockedDatesForm" class="settings-form">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-times"></i> Add Blocked Date</label>
                            <div class="form-row">
                                <div class="form-group input-with-icon">
                                    <input type="date" id="blockedDate" name="blocked_date">
                                </div>
                                <div class="form-group input-with-icon">
                                    <input type="text" id="blockReason" name="block_reason" placeholder="Reason for blocking (optional)">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-list"></i> Current Blocked Dates</label>
                            <div id="blockedDatesList" class="blocked-dates-list">
                                <!-- Blocked dates will be dynamically inserted here -->
                                <p class="help-text">No blocked dates added yet</p>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" id="addBlockedDate" class="btn-primary">
                                <i class="fas fa-plus"></i> Add Blocked Date
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="settings-container" id="notificationsSettings" style="display: none;">
                <div class="settings-card">
                    <h4><i class="fas fa-bell"></i> Notification Preferences</h4>
                    <form id="notificationSettingsForm" class="settings-form">
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Notifications</label>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="emailNewAppointment" name="email_new_appointment" checked>
                                <label for="emailNewAppointment">New appointment notifications</label>
                            </div>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="emailCancelledAppointment" name="email_cancelled_appointment" checked>
                                <label for="emailCancelledAppointment">Cancelled appointment notifications</label>
                            </div>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="emailEmergencyRequest" name="email_emergency_request" checked>
                                <label for="emailEmergencyRequest">Emergency request notifications</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-desktop"></i> Browser Notifications</label>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="browserNotifications" name="browser_notifications" checked>
                                <label for="browserNotifications">Enable browser notifications</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-sms"></i> SMS Notifications</label>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="smsNewAppointment" name="sms_new_appointment">
                                <label for="smsNewAppointment">New appointment notifications</label>
                            </div>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="smsEmergencyRequest" name="sms_emergency_request" checked>
                                <label for="smsEmergencyRequest">Emergency request notifications</label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="settings-container" id="systemSettings" style="display: none;">
                <div class="settings-card">
                    <h4><i class="fas fa-cogs"></i> System Settings</h4>
                    <form id="systemSettingsForm" class="settings-form">
                        <div class="form-group">
                            <label><i class="fas fa-database"></i> Database Backup</label>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enableAutoBackup" name="enable_auto_backup">
                                <label for="enableAutoBackup">Enable automatic database backup</label>
                            </div>
                            <div class="form-group">
                                <label for="backupFrequency"><i class="fas fa-clock"></i> Backup Frequency</label>
                                <select id="backupFrequency" name="backup_frequency">
                                    <option value="daily">Daily</option>
                                    <option value="weekly" selected>Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="button" id="manualBackup" class="btn-secondary">
                                    <i class="fas fa-download"></i> Manual Backup Now
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-trash-alt"></i> Data Cleanup</label>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enableDataCleanup" name="enable_data_cleanup">
                                <label for="enableDataCleanup">Automatically remove old completed appointments</label>
                            </div>
                            <div class="form-group">
                                <label for="dataRetentionPeriod"><i class="fas fa-calendar"></i> Data Retention Period</label>
                                <select id="dataRetentionPeriod" name="data_retention_period">
                                    <option value="30">30 days</option>
                                    <option value="60">60 days</option>
                                    <option value="90" selected>90 days</option>
                                    <option value="180">6 months</option>
                                    <option value="365">1 year</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let appointmentsData = [];
        let emergenciesData = [];
        let usersData = [];

        document.addEventListener('DOMContentLoaded', function() {
            // Load initial data
            loadAppointments();
            loadEmergencies();
            loadUsers();
            loadSettings();
            
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
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            const selectedSection = document.getElementById(section);
            if (selectedSection) {
                selectedSection.style.display = 'block';
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
            
            // Only include email if the field exists in the form
            const emailField = document.getElementById('adminEmail');
            if (emailField) {
                formData.email = emailField.value;
            }
            
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

        // Settings Tab Functionality
        function initSettingsTabs() {
            const tabs = document.querySelectorAll('.settings-tab');
            const settingsForms = document.querySelectorAll('.settings-form');
            
            // Initialize - show the first tab content by default
            if (tabs.length > 0) {
                tabs[0].classList.add('active');
                const targetId = tabs[0].getAttribute('data-target');
                document.querySelectorAll(`.settings-form[data-form="${targetId}"]`).forEach(form => {
                    form.style.display = 'flex';
                });
            }
            
            // Hide all other forms initially
            settingsForms.forEach(form => {
                if (!form.getAttribute('data-form') || form.getAttribute('data-form') !== tabs[0]?.getAttribute('data-target')) {
                    form.style.display = 'none';
                }
            });
            
            // Add click event to tabs
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    tab.classList.add('active');
                    
                    // Hide all forms
                    settingsForms.forEach(form => {
                        form.style.display = 'none';
                    });
                    
                    // Show forms corresponding to the selected tab
                    const targetId = tab.getAttribute('data-target');
                    document.querySelectorAll(`.settings-form[data-form="${targetId}"]`).forEach(form => {
                        form.style.display = 'flex';
                    });
                });
            });
        }

        // Save Settings Form
        function setupSettingsForms() {
            const settingsForms = document.querySelectorAll('.settings-form');
            
            settingsForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const formId = form.getAttribute('data-form');
                    const formData = new FormData(form);
                    
                    // Show loading indicator
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitBtn.disabled = true;
                    
                    // Convert FormData to JSON
                    const data = {};
                    formData.forEach((value, key) => {
                        data[key] = value;
                    });
                    
                    // Send AJAX request
                    fetch('save_settings.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            form_id: formId,
                            data: data
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showAlert('success', data.message || 'Settings saved successfully!');
                        } else {
                            // Show error message
                            showAlert('error', data.message || 'Failed to save settings.');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving settings:', error);
                        showAlert('error', 'An unexpected error occurred. Please try again.');
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            });
        }

        // Add Blocked Date
        function setupBlockedDatesFunctionality() {
            const addBlockedDateForm = document.getElementById('add-blocked-date-form');
            const blockedDatesList = document.querySelector('.blocked-dates-list');
            
            if (addBlockedDateForm) {
                addBlockedDateForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const dateInput = addBlockedDateForm.querySelector('input[name="blocked_date"]');
                    const reasonInput = addBlockedDateForm.querySelector('input[name="block_reason"]');
                    
                    if (!dateInput.value) {
                        showAlert('error', 'Please select a date');
                        return;
                    }
                    
                    const date = new Date(dateInput.value);
                    const formattedDate = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    
                    const blockedDateItem = document.createElement('div');
                    blockedDateItem.className = 'blocked-date-item';
                    blockedDateItem.innerHTML = `
                        <div class="date-info">
                            <span class="date">${formattedDate}</span>
                            <span class="reason">${reasonInput.value || 'No reason provided'}</span>
                        </div>
                        <div class="delete-date" title="Remove this date">
                            <i class="fas fa-times"></i>
                        </div>
                    `;
                    
                    // Add delete functionality
                    const deleteBtn = blockedDateItem.querySelector('.delete-date');
                    deleteBtn.addEventListener('click', () => {
                        if (confirm('Are you sure you want to remove this blocked date?')) {
                            blockedDateItem.remove();
                            saveBlockedDates();
                        }
                    });
                    
                    blockedDatesList.appendChild(blockedDateItem);
                    saveBlockedDates();
                    
                    // Reset form
                    dateInput.value = '';
                    reasonInput.value = '';
                });
            }
            
            // Load existing blocked dates
            loadBlockedDates();
            
            // Setup delete buttons for existing items
            setupDeleteBlockedDates();
        }

        function saveBlockedDates() {
            const blockedDates = [];
            document.querySelectorAll('.blocked-date-item').forEach(item => {
                const dateText = item.querySelector('.date').textContent;
                const reason = item.querySelector('.reason').textContent;
                
                // Convert the date from "Month Day, Year" format to "YYYY-MM-DD"
                const date = new Date(dateText);
                let formattedDate = '';
                
                if (!isNaN(date.getTime())) {
                    // Format date as YYYY-MM-DD
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    formattedDate = `${year}-${month}-${day}`;
                } else {
                    // If we can't parse the date, use the original text
                    formattedDate = dateText;
                }
                
                blockedDates.push({
                    date: formattedDate,
                    reason: reason
                });
            });
            
            fetch('save_blocked_dates.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    blocked_dates: blockedDates
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Blocked dates updated');
                } else {
                    showAlert('error', data.message || 'Failed to update blocked dates');
                }
            })
            .catch(error => {
                console.error('Error saving blocked dates:', error);
            });
        }

        function loadBlockedDates() {
            const blockedDatesList = document.querySelector('.blocked-dates-list');
            
            if (blockedDatesList) {
                fetch('get_blocked_dates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.blocked_dates) {
                        blockedDatesList.innerHTML = '';
                        
                        data.blocked_dates.forEach(blockedDate => {
                            const blockedDateItem = document.createElement('div');
                            blockedDateItem.className = 'blocked-date-item';
                            blockedDateItem.innerHTML = `
                                <div class="date-info">
                                    <span class="date">${blockedDate.date}</span>
                                    <span class="reason">${blockedDate.reason || 'No reason provided'}</span>
                                </div>
                                <div class="delete-date" title="Remove this date">
                                    <i class="fas fa-times"></i>
                                </div>
                            `;
                            
                            blockedDatesList.appendChild(blockedDateItem);
                        });
                        
                        setupDeleteBlockedDates();
                    }
                })
                .catch(error => {
                    console.error('Error loading blocked dates:', error);
                });
            }
        }

        function setupDeleteBlockedDates() {
            document.querySelectorAll('.blocked-date-item .delete-date').forEach(deleteBtn => {
                deleteBtn.addEventListener('click', () => {
                    if (confirm('Are you sure you want to remove this blocked date?')) {
                        deleteBtn.closest('.blocked-date-item').remove();
                        saveBlockedDates();
                    }
                });
            });
        }

        // Initialize settings functionality
        document.addEventListener('DOMContentLoaded', function() {
            initSettingsTabs();
            setupSettingsForms();
            setupBlockedDatesFunctionality();
        });
    </script>
</body>
</html>