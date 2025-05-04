// Emergency Management for Admin Panel
document.addEventListener('DOMContentLoaded', function() {
    // Load emergencies when page loads
    loadEmergencies();

    // Set up filter buttons
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const status = this.getAttribute('data-status') || 'all';
            filterEmergencies(status);
            
            // Update active state on buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

// Function to load all emergencies
function loadEmergencies() {
    fetch('get_emergencies.php')
        .then(response => response.json())
        .then(data => {
            // Store data globally for filtering
            window.emergenciesData = data;
            
            // Display all emergencies initially
            displayEmergencies(data);
            updateCounters(data);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load emergencies', 'error');
        });
}

// Function to display emergencies
function displayEmergencies(emergencies) {
    const emergencyList = document.getElementById('emergencyList');
    if (!emergencyList) return;
    
    emergencyList.innerHTML = '';
    
    if (emergencies.length === 0) {
        emergencyList.innerHTML = '<div class="no-data">No emergency requests found</div>';
        return;
    }
    
    emergencies.forEach(emergency => {
        const card = document.createElement('div');
        card.className = `emergency-card status-${emergency.status}`;
        card.innerHTML = `
            <div class="emergency-header">
                <div class="patient-name">
                    <i class="fas fa-user-circle"></i>
                    <h3>${emergency.name}</h3>
                </div>
                <span class="status-badge status-${emergency.status}">${formatStatus(emergency.status)}</span>
            </div>
            <div class="emergency-body">
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${emergency.location}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${emergency.issue.substring(0, 100)}${emergency.issue.length > 100 ? '...' : ''}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span>${new Date(emergency.created_at).toLocaleString()}</span>
                </div>
            </div>
            <div class="emergency-actions">
                <a href="emergency-view.php?id=${emergency.id}" class="action-btn view">
                    <i class="fas fa-eye"></i> View
                </a>
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
}

// Function to filter emergencies
function filterEmergencies(status) {
    const filteredData = status === 'all' 
        ? window.emergenciesData 
        : window.emergenciesData.filter(emergency => emergency.status === status);
    
    displayEmergencies(filteredData);
}

// Function to update emergency status from list view
function updateEmergencyStatus(id, status) {
    // Confirm before changing status
    const statusText = status === 'in_progress' ? 'accept' : status;
    if (!confirm(`Are you sure you want to ${statusText} this emergency request?`)) {
        return;
    }
    
    fetch('update_emergency_status.php', {
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
            
            // Reload emergencies to reflect changes
            loadEmergencies();
        } else {
            throw new Error(data.message || 'Failed to update emergency request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message, 'error');
    });
}

// Function to update counter displays
function updateCounters(emergencies) {
    // Update total count
    const totalCounter = document.getElementById('totalEmergencyCount');
    if (totalCounter) {
        totalCounter.textContent = emergencies.length;
    }
    
    // Update pending count
    const pendingCounter = document.getElementById('pendingEmergencyCount');
    if (pendingCounter) {
        const pendingCount = emergencies.filter(e => e.status === 'pending').length;
        pendingCounter.textContent = pendingCount;
    }
    
    // Update in progress count
    const progressCounter = document.getElementById('inProgressEmergencyCount');
    if (progressCounter) {
        const inProgressCount = emergencies.filter(e => e.status === 'in_progress').length;
        progressCounter.textContent = inProgressCount;
    }
    
    // Update completed count
    const completedCounter = document.getElementById('completedEmergencyCount');
    if (completedCounter) {
        const completedToday = emergencies.filter(e => {
            return e.status === 'completed' && 
                   new Date(e.updated_at).toDateString() === new Date().toDateString();
        }).length;
        completedCounter.textContent = completedToday;
    }
}

// Helper function to format status text
function formatStatus(status) {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Function to show notification
function showNotification(message, type) {
    const container = document.querySelector('.alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = message;
    
    container.appendChild(alert);
    
    // Remove after 3 seconds
    setTimeout(() => {
        alert.remove();
        // Remove container if empty
        if (container.children.length === 0) {
            container.remove();
        }
    }, 3000);
}

// Helper function to create alert container
function createAlertContainer() {
    const container = document.createElement('div');
    container.className = 'alert-container';
    document.body.appendChild(container);
    return container;
} 