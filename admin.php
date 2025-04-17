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
    </style>
</head>
<body>
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
            <a href="#" class="nav-item" data-section="emergencies">
                <i class="fas fa-calendar-check"></i>
                <span>Appointments</span>
            </a>
            <a href="#" class="nav-item" data-section="users">
                <i class="fas fa-ambulance"></i>
                <span>Emergencies</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="#" class="nav-item">
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
            <div class="section-header">
                <h3 class="section-title">Emergency Requests</h3>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterEmergencies('all')">All</button>
                    <button class="filter-btn" onclick="filterEmergencies('pending')">Pending</button>
                    <button class="filter-btn" onclick="filterEmergencies('in_progress')">In Progress</button>
                    <button class="filter-btn" onclick="filterEmergencies('completed')">Completed</button>
                    <button class="filter-btn" onclick="filterEmergencies('cancelled')">Cancelled</button>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="emergenciesTableBody"></tbody>
            </table>
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

    <script>
        let appointmentsData = [];
        let emergenciesData = [];
        let usersData = [];

        // Load all data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadAppointments();
            loadEmergencies();
            loadUsers();
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
                    emergenciesData = data;
                    const tbody = document.getElementById('emergenciesTableBody');
                    tbody.innerHTML = '';
                    data.forEach(emergency => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${emergency.name}</td>
                                <td>${emergency.phone}</td>
                                <td>${emergency.location}</td>
                                <td><span class="status-badge status-${emergency.status.toLowerCase()}">${emergency.status}</span></td>
                                <td>
                                    <button class="action-btn" onclick="updateEmergencyStatus(${emergency.id}, 'in_progress')" title="Mark as In Progress">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="action-btn" onclick="updateEmergencyStatus(${emergency.id}, 'completed')" title="Mark as Completed">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn" onclick="updateEmergencyStatus(${emergency.id}, 'cancelled')" title="Cancel Emergency">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to load emergencies', 'error');
                });
        }

        function loadUsers() {
            fetch('get_users.php')
                .then(response => response.json())
                .then(data => {
                    usersData = data;
                    const tbody = document.getElementById('usersTableBody');
                    tbody.innerHTML = '';
                    data.forEach(user => {
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
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to load users', 'error');
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

        function filterEmergencies(status) {
            // Update filter buttons
            document.querySelectorAll('#emergencies .filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const tbody = document.getElementById('emergenciesTableBody');
            tbody.innerHTML = '';
            
            const filteredData = status === 'all' 
                ? emergenciesData 
                : emergenciesData.filter(emergency => emergency.status.toLowerCase() === status);
            
            filteredData.forEach(emergency => {
                tbody.innerHTML += `
                    <tr>
                        <td>${emergency.name}</td>
                        <td>${emergency.phone}</td>
                        <td>${emergency.location}</td>
                        <td><span class="status-badge status-${emergency.status.toLowerCase()}">${emergency.status}</span></td>
                        <td>
                            <button class="action-btn" onclick="updateEmergencyStatus(${emergency.id}, 'in_progress')" title="Mark as In Progress">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="action-btn" onclick="updateEmergencyStatus(${emergency.id}, 'completed')" title="Mark as Completed">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="action-btn" onclick="updateEmergencyStatus(${emergency.id}, 'cancelled')" title="Cancel Emergency">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        function updateAppointmentStatus(id, status) {
            if (!confirm(`Are you sure you want to mark this appointment as ${status}?`)) {
                return;
            }

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

        function updateEmergencyStatus(id, status) {
            if (!confirm(`Are you sure you want to mark this emergency as ${status}?`)) {
                return;
            }

            fetch('update_emergency.php', {
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

        function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                return;
            }

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

        function logout() {
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
    </script>
</body>
</html> 