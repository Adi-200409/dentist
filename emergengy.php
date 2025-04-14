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
    background-color: #e5ebeb;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.emg{
    animation: fadeInUp 1s ease-out forwards;

}
/* Emergency Container */
.emergency-container {
    width: 100%;
    max-width: 500px;
    background-image:url(https://moulanahospital.com/wp-content/uploads/2021/05/Endodontis-pedodontis-orthodontis-dental-surgery.jpg);
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    padding: 30px;
    text-align: center;
    animation: pulse 2s infinite;
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
    color: #0a021c;
    margin-bottom: 10px;
}

.emergency-container p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Call Now Button */
.call-now-btn {
    display: inline-block;
    background-color: #020303;
    color: #fff;
    padding: 12px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s ease;
}

.call-now-btn:hover {
    background-color: #178fa2;
}

/* Form Styles */
form {
    margin-top: 20px;
    text-align: left;
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.input-group input,
.input-group textarea,
.input-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
}

.input-group input:focus,
.input-group textarea:focus,
.input-group select:focus {
    border-color: #2287df;
    outline: none;
}

.submit-btn {
    width: 100%;
    background-color: #131716;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
}

.submit-btn:hover {
    background-color: #1a86a7;
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
@media (max-width: 600px) {
    .emergency-container {
        padding: 20px;
    }
}

.form-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.input-group input,
.input-group textarea,
.input-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.submit-btn {
    background: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
}

.submit-btn:hover {
    background: #45a049;
}

</style>
</head>
<body>
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
                    <label for="issue">Describe Your Issue*</label>
                    <textarea id="issue" name="issue" rows="4" required></textarea>
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

        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = `alert-message ${type}`;
            alertMessage.style.display = 'block';
            
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
            fetch('emer.php', {
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