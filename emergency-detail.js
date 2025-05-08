// Emergency Detail Button Handlers
document.addEventListener('DOMContentLoaded', function() {
    console.log('Emergency detail script loaded');
    
    // Get buttons by their classes
    const acceptButton = document.querySelector('.btn-accept');
    const completeButton = document.querySelector('.btn-complete');
    const cancelButton = document.querySelector('.btn-cancel');
    
    // Add event listeners
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
    
    // Load emergency details
    const emergencyData = document.getElementById('emergencyData');
    if (emergencyData) {
        const emergencyId = emergencyData.dataset.emergencyId;
        console.log('Loading details for emergency ID:', emergencyId);
        showEmergencyDetails(emergencyId);
    }
});

// Function to update emergency status
function updateEmergencyStatus(status) {
    // Get emergency ID from the data element or URL
    const emergencyId = getEmergencyIdFromPage();
    
    if (!emergencyId) {
        console.error('Could not determine emergency ID');
        alert('Error: Could not determine emergency ID');
        return;
    }
    
    console.log('Updating status to:', status, 'for ID:', emergencyId);
    
    // Confirm before changing status
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not OK: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            alert('Emergency request updated successfully');
            
            // Update UI first
            updateUIStatus(status);
            
            // Refresh the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Failed to update emergency request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
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

// Function to get emergency ID from the current page
function getEmergencyIdFromPage() {
    // Try to get emergency ID from data element
    const emergencyData = document.getElementById('emergencyData');
    if (emergencyData && emergencyData.dataset.emergencyId) {
        return emergencyData.dataset.emergencyId;
    }
    
    // Try to extract emergency ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    if (id) {
        return id;
    }
    
    return null;
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
        descriptionEl.innerHTML = emergency.issue || 'No description provided';
    }
} 