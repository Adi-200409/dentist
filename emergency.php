<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Dental Call</title>
    <link rel="shortcut icon" href="images/Emergency-logo-removebg-preview.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    margin-top: 50px; /* Make room for the fixed header */
    background-color: #f0f6fc;
    background-image: 
        url('data:image/svg+xml;utf8,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><path d="M30 16c-2 0-3.5 1.5-3.5 3.5 0 1.5 0.8 2.8 2 3.3v4.2c0 0.8 0.7 1.5 1.5 1.5s1.5-0.7 1.5-1.5v-4.2c1.2-0.5 2-1.8 2-3.3 0-2-1.5-3.5-3.5-3.5z" fill="%233498db" fill-opacity="0.1"/><path d="M40 43c-2 2-4 3-6.5 3h-7c-2.5 0-4.5-1-6.5-3-1.5-1.5-4-5-4-8.5C16 29 19 25 23 25h14c4 0 7 4 7 9.5 0 3.5-2.5 7-4 8.5z" fill="%233498db" fill-opacity="0.07"/></svg>'),
        linear-gradient(to bottom, rgba(255,255,255,0.8) 0%, rgba(240,246,252,0.8) 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    background-attachment: fixed;
    background-position: center;
    background-repeat: repeat;
    background-size: 90px;
    position: relative;
}

/* Add a subtle dental-themed pattern overlay */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M40,40 L60,40 L60,60 L40,60 Z" fill="none" stroke="%2300a1e4" stroke-width="0.5" stroke-opacity="0.03"/><path d="M34,30 C32,28 28,25 25,25 C22,25 18,28 16,30 C14,32 10,35 10,38 C10,41 14,44 16,46 C18,48 22,51 25,51 C28,51 32,48 34,46 C36,44 40,41 40,38 C40,35 36,32 34,30 Z" fill="%23cdf0ff" fill-opacity="0.05"/><path d="M84,30 C82,28 78,25 75,25 C72,25 68,28 66,30 C64,32 60,35 60,38 C60,41 64,44 66,46 C68,48 72,51 75,51 C78,51 82,48 84,46 C86,44 90,41 90,38 C90,35 86,32 84,30 Z" fill="%23cdf0ff" fill-opacity="0.07"/></svg>');
    opacity: 0.5;
    z-index: -1;
}

.emg {
    width: 100%;
    max-width: 550px;
    animation: fadeInUp 1s ease-out forwards;
    position: relative;
}

/* Add tooth icon decorations around the emergency container */
.emg::before, .emg::after {
    content: "";
    position: absolute;
    width: 60px;
    height: 60px;
    background-image: url('data:image/svg+xml;utf8,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><path d="M30 10c-3 0-5 2-5 5 0 2 1 4 3 5v6c0 1 1 2 2 2s2-1 2-2v-6c2-1 3-3 3-5 0-3-2-5-5-5z" fill="%23ffffff" fill-opacity="0.6"/><path d="M40 43c-2 2-4 3-6.5 3h-7c-2.5 0-4.5-1-6.5-3-1.5-1.5-4-5-4-8.5C16 29 19 25 23 25h14c4 0 7 4 7 9.5 0 3.5-2.5 7-4 8.5z" fill="%23ffffff" fill-opacity="0.5"/></svg>');
    background-repeat: no-repeat;
    z-index: 5;
    opacity: 0.7;
}

.emg::before {
    top: -30px;
    left: -20px;
    transform: rotate(-15deg);
}

.emg::after {
    bottom: -30px;
    right: -20px;
    transform: rotate(15deg);
}

/* Add dental-specific header styles */
.dentist-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    padding: 10px 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.dentist-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #3498db;
    font-size: 18px;
    text-decoration: none;
}

.dentist-logo-icon {
    display: inline-block;
    width: 30px;
    height: 30px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%233498db"><path d="M12 2c-1.4 0-2.5 1.1-2.5 2.5 0 1.2 0.8 2.1 1.8 2.4v2.6c0 0.4 0.3 0.7 0.7 0.7 0.4 0 0.7-0.3 0.7-0.7V6.9c1-0.3 1.8-1.2 1.8-2.4C14.5 3.1 13.4 2 12 2z"/><path d="M17.6 15.9c-0.7 0.7-1.5 1.1-2.4 1.1h-6.4c-0.9 0-1.7-0.4-2.4-1.1-0.7-0.7-1.4-1.9-1.4-3.1 0-0.8 0.3-1.6 0.8-2.3s1.3-1.4 2.1-1.7H16c0.9 0.3 1.6 1 2.1 1.7s0.8 1.5 0.8 2.3c0.1 1.2-0.6 2.4-1.3 3.1z"/><path d="M2 10H0V7h2c1.2 0 2 0.8 2 2v1h-2z"/><path d="M22 10h2V7h-2c-1.2 0-2 0.8-2 2v1h2z"/></svg>');
    background-repeat: no-repeat;
}

/* Emergency Container Updates */
.emergency-container {
    width: 100%;
    background: linear-gradient(135deg, #3498db, #9b59b6);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    padding: 25px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

/* Medical cross pattern in the background */
.emergency-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        url('data:image/svg+xml;utf8,<svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg"><path d="M13 7V13H7V17H13V23H17V17H23V13H17V7H13Z" fill="%23ffffff" fill-opacity="0.07"/></svg>');
    background-repeat: repeat;
    z-index: 0;
    opacity: 0.7;
}

.emergency-container > * {
    position: relative;
    z-index: 1;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(50px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 15px rgba(9, 9, 9, 0.5); }
    50% { box-shadow: 0 0 30px rgba(16, 15, 15, 0.8); }
}

.emergency-container h2 {
    color: #ffffff;
    margin-bottom: 10px;
    font-size: 1.8rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.emergency-container p {
    font-size: 16px;
    margin-bottom: 20px;
    color: #ffffff;
}

/* Call Now Button */
.call-now-btn {
    display: inline-block;
    background-color: #ffffff;
    color: #6e8efb;
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s ease;
    margin-bottom: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.call-now-btn:hover {
    background-color: #ffffff;
    color: #a777e3;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

/* Form Styles */
.form-container {
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    max-height: 400px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.form-container::-webkit-scrollbar {
    width: 6px;
}

.form-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.form-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.form-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    text-align: left;
}

.input-group input,
.input-group textarea,
.input-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
    font-size: 14px;
}

.input-group input:focus,
.input-group textarea:focus,
.input-group select:focus {
    border-color: #2287df;
    outline: none;
    box-shadow: 0 0 5px rgba(34, 135, 223, 0.3);
}

.input-group textarea {
    resize: vertical;
    min-height: 80px;
    max-height: 150px;
}

.submit-btn {
    width: 100%;
    background: linear-gradient(to right, #6e8efb, #a777e3);
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    font-size: 16px;
    margin-top: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.submit-btn:hover {
    background: linear-gradient(to right, #5d7df9, #9462e0);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

/* Alert Message Styles */
.alert-message {
    padding: 15px;
    margin: 15px 0;
    border-radius: 8px;
    display: none;
    font-weight: 600;
    text-align: center;
}

.alert-message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .emergency-container {
        padding: 20px 15px;
    }
    
    .emergency-container h2 {
        font-size: 1.5rem;
    }
    
    .form-container {
        max-height: 350px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 15px;
    }
    
    .emergency-container {
        padding: 15px 12px;
    }
    
    .emergency-container h2 {
        font-size: 1.3rem;
    }
    
    .emergency-container p {
        font-size: 14px;
    }
    
    .call-now-btn {
        padding: 10px 20px;
        font-size: 14px;
    }
    
    .form-container {
        padding: 15px;
        max-height: 300px;
    }
    
    .input-group label {
        font-size: 14px;
    }
    
    .input-group input,
    .input-group textarea,
    .input-group select {
        padding: 8px;
        font-size: 13px;
    }
    
    .submit-btn {
        padding: 10px;
        font-size: 14px;
    }
}

/* For smaller screens */
@media (max-height: 700px) {
    body {
        align-items: flex-start;
        padding-top: 30px;
    }
    
    .form-container {
        max-height: 250px;
    }
}
</style>
</head>
<body>
<!-- Dental Header -->
<header class="dentist-header">
    <a href="#" class="dentist-logo">
        <span class="dentist-logo-icon"></span>
        <span>SmileCare Dental Clinic</span>
    </a>
</header>

<div class="emg">
    <!-- Emergency Call Section -->
    <div class="emergency-container">
        <h2>Dental Emergency?</h2>
        <p>We're here to help. Call now or fill out the form for immediate attention.</p>
        
        <!-- Immediate Call Button -->
        <a href="tel:+91 9148074307" class="call-now-btn">📞 Call Now</a>

        <!-- Add alert message container -->
        <div id="alertMessage" class="alert-message"></div>

        <!-- Emergency Form -->
        <div class="form-container">
            <form id="emergencyForm" method="POST">
                <div class="input-group">
                    <label for="name">Full Name*</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="input-group">
                    <label for="phone">Phone Number*</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="input-group">
                    <label for="location">Your Location*</label>
                    <input type="text" id="location" name="location" placeholder="Enter your address or location" required>
                </div>

                <div class="input-group">
                    <label for="issue">Describe Your Issue*</label>
                    <textarea id="issue" name="issue" rows="3" required></textarea>
                </div>

                <div class="input-group">
                    <label for="urgency">Urgency Level*</label>
                    <select id="urgency" name="urgency" required>
                        <option value="">Select urgency level</option>
                        <option value="Severe Pain">Severe Pain</option>
                        <option value="Broken Tooth">Broken Tooth</option>
                        <option value="Bleeding">Bleeding</option>
                        <option value="Swelling">Swelling</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Submit Emergency Request</button>
            </form>
        </div>
    </div>
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('emergencyForm');
        const alertMessage = document.getElementById('alertMessage');
        const formContainer = document.querySelector('.form-container');

        // Adjust form height based on screen size
        function adjustFormHeight() {
            const windowHeight = window.innerHeight;
            if (windowHeight < 700) {
                formContainer.style.maxHeight = (windowHeight * 0.45) + 'px';
            } else if (windowHeight < 900) {
                formContainer.style.maxHeight = (windowHeight * 0.5) + 'px';
            } else {
                formContainer.style.maxHeight = '400px';
            }
        }

        // Initial adjustment
        adjustFormHeight();

        // Adjust on resize
        window.addEventListener('resize', adjustFormHeight);

        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = `alert-message ${type}`;
            alertMessage.style.display = 'block';
            
            // Scroll to alert message
            alertMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Hide after 5 seconds
            setTimeout(() => {
                alertMessage.style.display = 'none';
            }, 5000);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const formData = new FormData(form);

            // Log form data for debugging
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Send the form data
            fetch('process_emergency.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                console.log('Server response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        showAlert('✅ ' + data.message, 'success');
                        form.reset();
                    } else {
                        showAlert('❌ ' + data.message, 'error');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    showAlert('❌ Server error. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('❌ ' + error.message, 'error');
            });
        });
    });
    </script>
</body>
</html>