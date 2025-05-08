// Simple direct solution for emergency buttons
document.addEventListener('DOMContentLoaded', function() {
    console.log('Emergency buttons script loaded');

    // Find buttons by their classes
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

    // Show emergency details in the details section if it exists
    const emergencyData = document.getElementById('emergencyData');
    if (emergencyData) {
        showEmergencyDetails(emergencyData.dataset.emergencyId);
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
            
            // Update UI without reloading
            updateUIStatus(status);
            
            // Reload after short delay
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

// Function to update UI status without reloading
function updateUIStatus(status) {
    // Update status badge
    const statusBadge = document.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.className = 'status-badge status-' + status;
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
    }
    
    // Update button states
    const acceptButton = document.querySelector('.btn-accept');
    const completeButton = document.querySelector('.btn-complete');
    const cancelButton = document.querySelector('.btn-cancel');
    
    if (acceptButton) acceptButton.disabled = (status !== 'pending');
    if (completeButton) completeButton.disabled = (status !== 'in_progress');
    if (cancelButton) cancelButton.disabled = (status === 'completed' || status === 'cancelled');
}

// Function to show emergency details
function showEmergencyDetails(emergencyId) {
    if (!emergencyId) return;
    
    const detailsSection = document.querySelector('.emergency-description');
    if (!detailsSection) return;
    
    // If there's already content, don't fetch again
    if (detailsSection.querySelector('p')?.textContent.trim() !== 'No description provided' &&
        detailsSection.querySelector('p')?.textContent.trim() !== '') {
        return;
    }
    
    // Only fetch if additional details are needed
    fetch(`get_emergency_details.php?id=${emergencyId}`)
    .then(response => {
        if (!response.ok) throw new Error('Failed to fetch details');
        return response.json();
    })
    .then(data => {
        if (data.success && data.emergency) {
            // Update any missing details in the UI
            updateEmergencyDetailsUI(data.emergency);
        }
    })
    .catch(error => {
        console.error('Error fetching emergency details:', error);
    });
}

// Function to update emergency details in the UI
function updateEmergencyDetailsUI(emergency) {
    // Update patient info
    const patientName = document.querySelector('.patient-info h2');
    if (patientName && !patientName.textContent.trim()) {
        patientName.textContent = emergency.name || 'Unknown';
    }
    
    const patientPhone = document.querySelector('.patient-info p');
    if (patientPhone && patientPhone.textContent.includes('N/A')) {
        patientPhone.innerHTML = `<i class="fas fa-phone"></i> ${emergency.phone || 'N/A'}`;
    }
    
    // Update description
    const descriptionEl = document.querySelector('.emergency-description p');
    if (descriptionEl && (descriptionEl.textContent === 'No description provided' || !descriptionEl.textContent.trim())) {
        descriptionEl.innerHTML = emergency.issue ? emergency.issue.replace(/\n/g, '<br>') : 'No description provided';
    }
}

// Helper to get emergency ID from URL
function getEmergencyIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    console.log('ID from URL:', id);
    return id;
} 