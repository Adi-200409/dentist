// Emergency View Functions
document.addEventListener('DOMContentLoaded', function() {
    // Get all emergency action buttons
    const acceptButton = document.querySelector('.action-buttons button:nth-child(1)');
    const completeButton = document.querySelector('.action-buttons button:nth-child(2)');
    const cancelButton = document.querySelector('.action-buttons button:nth-child(3)');

    // Add event listeners to buttons if they exist
    if (acceptButton) {
        acceptButton.addEventListener('click', function() {
            updateEmergencyStatus('in_progress');
        });
    }

    if (completeButton) {
        completeButton.addEventListener('click', function() {
            updateEmergencyStatus('completed');
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            updateEmergencyStatus('cancelled');
        });
    }
});

// Function to update emergency status
function updateEmergencyStatus(status) {
    // Get emergency ID from the page
    const emergencyId = getEmergencyIdFromPage();
    
    if (!emergencyId) {
        showAlert('Error: Could not determine emergency ID', 'error');
        return;
    }
    
    // Confirm before changing status
    const statusText = status === 'in_progress' ? 'accept' : status;
    if (!confirm(`Are you sure you want to ${statusText} this emergency request?`)) {
        return;
    }
    
    // Send request to update emergency status
    fetch('update_emergency_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: emergencyId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Emergency request updated successfully', 'success');
            // Refresh the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to update emergency request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message, 'error');
    });
}

// Function to get emergency ID from the current page
function getEmergencyIdFromPage() {
    // Try to extract emergency ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    if (id) {
        return id;
    }
    
    // As a fallback, try to find it in the page data
    const emergencyElement = document.querySelector('[data-emergency-id]');
    if (emergencyElement && emergencyElement.dataset.emergencyId) {
        return emergencyElement.dataset.emergencyId;
    }
    
    return null;
}

// Function to show alert
function showAlert(message, type) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type}`;
    alertContainer.innerHTML = message;
    
    document.body.appendChild(alertContainer);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        alertContainer.remove();
    }, 3000);
} 