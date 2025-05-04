// Emergency Detail Button Handlers
document.addEventListener('DOMContentLoaded', function() {
    // Get buttons by their text content
    const acceptButton = document.querySelector('button svg + span:contains("Accept")').closest('button');
    const completeButton = document.querySelector('button svg + span:contains("Complete")').closest('button');
    const cancelButton = document.querySelector('button svg + span:contains("Cancel")').closest('button');
    
    // Alternative selectors if the above don't work
    if (!acceptButton) {
        const allButtons = document.querySelectorAll('button');
        for (const btn of allButtons) {
            if (btn.textContent.trim() === 'Accept') {
                acceptButton = btn;
            } else if (btn.textContent.trim() === 'Complete') {
                completeButton = btn;
            } else if (btn.textContent.trim() === 'Cancel') {
                cancelButton = btn;
            }
        }
    }

    // Add event listeners
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
    // Get emergency ID from the URL or page data
    const emergencyId = getEmergencyIdFromPage();
    
    if (!emergencyId) {
        alert('Error: Could not determine emergency ID');
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
            alert('Emergency request updated successfully');
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
    
    // Try to find from the page content
    const pageContent = document.body.textContent;
    const emergencyMatch = pageContent.match(/Emergency #(\d+)/);
    if (emergencyMatch && emergencyMatch[1]) {
        return emergencyMatch[1];
    }
    
    // Last resort, try to find it in any data attribute
    const matches = document.body.innerHTML.match(/data-.*?-id="(\d+)"/);
    if (matches && matches[1]) {
        return matches[1];
    }
    
    return null;
} 