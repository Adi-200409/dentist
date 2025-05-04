// Simple direct solution for emergency buttons
document.addEventListener('DOMContentLoaded', function() {
    console.log('Emergency buttons script loaded');

    // Find buttons directly
    const acceptButton = document.querySelector('.btn-accept');
    const completeButton = document.querySelector('.btn-complete');
    const cancelButton = document.querySelector('.btn-cancel');

    // Add direct event listeners if buttons exist
    if (acceptButton) {
        console.log('Accept button found');
        acceptButton.addEventListener('click', function() {
            console.log('Accept button clicked');
            updateEmergencyStatus('in_progress');
        });
    } else {
        console.log('Accept button not found');
    }

    if (completeButton) {
        console.log('Complete button found');
        completeButton.addEventListener('click', function() {
            console.log('Complete button clicked');
            updateEmergencyStatus('completed');
        });
    } else {
        console.log('Complete button not found');
    }

    if (cancelButton) {
        console.log('Cancel button found');
        cancelButton.addEventListener('click', function() {
            console.log('Cancel button clicked');
            updateEmergencyStatus('cancelled');
        });
    } else {
        console.log('Cancel button not found');
    }
});

// Function to update emergency request status
function updateEmergencyStatus(status) {
    console.log('Updating emergency status to:', status);
    
    // Try to get emergency ID from data element first (most reliable)
    let emergencyId = document.getElementById('emergencyData')?.dataset?.emergencyId;
    
    // Fallback to URL if not found
    if (!emergencyId) {
        emergencyId = getEmergencyIdFromUrl();
    }
    
    console.log('Emergency ID:', emergencyId);
    
    if (!emergencyId) {
        console.error('Could not determine emergency ID');
        alert('Could not determine emergency ID');
        return;
    }
    
    // Confirm action
    const statusText = status === 'in_progress' ? 'accept' : status;
    if (!confirm(`Are you sure you want to ${statusText} this emergency request?`)) {
        return;
    }
    
    // Show loading state
    const buttons = document.querySelectorAll('.action-button');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    });
    
    // Send request
    console.log('Sending request to update_emergency_status.php');
    fetch('update_emergency_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: emergencyId, status: status })
    })
    .then(response => {
        console.log('Response received:', response);
        if (!response.ok) {
            throw new Error('Network response was not OK: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data);
        if (data.success) {
            alert('Emergency status updated successfully');
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            throw new Error(data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('Error updating emergency status:', error);
        alert('Error: ' + error.message);
        
        // Re-enable buttons
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    });
}

// Helper to get emergency ID from URL
function getEmergencyIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    console.log('ID from URL:', id);
    return id;
} 