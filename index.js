let menu = document.querySelector('#menu-btn');
let navbar = document.querySelector('.header .nav');
let header = document.querySelector('.header');

menu.onclick = () => {
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}

window.onscroll = () => {
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');

    if (window.scrollY > 0) {
        header.classList.add('active');
    } else {
        header.classList.remove('active');
    }
}

// treatment search
function filterTreatments() {
    const input = document.getElementById('search');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('treatmentsTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td')[0];
        if (td) {
            const txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

const appointmentDetails = document.getElementById("appointmentDetails");
const appointmentForm = document.getElementById("appointmentForm");

// Form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    if (!form) return;

    // Set min date to today
    const dateInput = form.querySelector('input[name="date"]');
    if (dateInput) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        dateInput.min = `${yyyy}-${mm}-${dd}`;
    }

    // Phone input validation
    const phoneInput = form.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            
            // Only remove 91 prefix if it's at the start and the number is longer than 10 digits
            if (value.length > 10 && value.startsWith('91')) {
                const remainingDigits = value.substring(2);
                // Only remove the 91 if the remaining digits form a valid Indian mobile number
                if (remainingDigits.match(/^[6-9]\d{9}$/)) {
                    value = remainingDigits;
                }
            }
            
            // Keep only the first 10 digits if longer
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            
            e.target.value = value;
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Get form values
        const phone = formData.get('phone').trim();
        const date = new Date(formData.get('date'));
        const time = formData.get('time');
        const email = formData.get('email').trim();
        const name = formData.get('name').trim();
        const area = formData.get('area').trim();
        const city = formData.get('city').trim();
        const state = formData.get('state').trim();
        const postcode = formData.get('postcode').trim();

        // Basic validations
        if (!name) {
            showToast('Please enter your name');
            return;
        }

        if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            showToast('Please enter a valid email address');
            return;
        }

        // Phone validation
        let cleanPhone = phone.replace(/\D/g, '');
        // Only remove 91 prefix if the remaining number is valid
        if (cleanPhone.length > 10 && cleanPhone.startsWith('91')) {
            const remainingDigits = cleanPhone.substring(2);
            if (remainingDigits.match(/^[6-9]\d{9}$/)) {
                cleanPhone = remainingDigits;
            }
        }
        
        if (!cleanPhone.match(/^[6-9]\d{9}$/)) {
            showToast('Please enter a valid 10-digit mobile number starting with 6-9');
            return;
        }

        // Date and time validation
        const currentDate = new Date();
        const today = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
        const selectedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        if (selectedDate < today) {
            showToast('Please select today\'s or a future date');
            return;
        }

        if (date.getDay() === 0) {
            showToast('We are closed on Sundays');
            return;
        }

        const [hours, minutes] = time.split(':').map(Number);
        if (hours < 9 || hours >= 21) {
            showToast('Please select time between 9 AM and 9 PM');
            return;
        }

        if (selectedDate.getTime() === today.getTime() && 
            currentDate.getHours() >= hours) {
            showToast('Please select a future time');
            return;
        }

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Booking...';

        // Submit form using fetch
        fetch('appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Show appointment details
                const appointmentDetails = document.getElementById('appointmentDetails');
                if (appointmentDetails) {
                    const formattedDate = new Date(formData.get('date')).toLocaleDateString();
                    appointmentDetails.innerHTML = `
                        <div class="appointment-summary">
                            <h2>Appointment Details</h2>
                            <p><strong>Name:</strong> ${name}</p>
                            <p><strong>Email:</strong> ${email}</p>
                            <p><strong>Phone:</strong> ${phone}</p>
                            <p><strong>Date:</strong> ${formattedDate}</p>
                            <p><strong>Time:</strong> ${time}</p>
                            <p><strong>Address:</strong> ${area}, ${city}, ${state} - ${postcode}</p>
                        </div>
                    `;
                    appointmentDetails.style.display = 'block';
                }

                // Reset form
                form.reset();
                
                // Reset date input min value
                if (dateInput) {
                    const resetDate = new Date();
                    const resetDD = String(resetDate.getDate()).padStart(2, '0');
                    const resetMM = String(resetDate.getMonth() + 1).padStart(2, '0');
                    const resetYYYY = resetDate.getFullYear();
                    dateInput.min = `${resetYYYY}-${resetMM}-${resetDD}`;
                }
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            showToast(error.message || 'Failed to book appointment. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Book Appointment';
        });
    });
});

// Toast notification function
function showToast(message, type = 'error', duration = 3000) {
    const alertCont = document.getElementById('alert-container');
    if (!alertCont) return;

    const toastType = type === 'error';
    
    alertCont.innerHTML = `
        <div id="alert-wrapper" style="--bg-clr: ${toastType ? '#FFC9C9' : '#B9F8CF'}; --border-clr: ${toastType ? '#fca5a5' : '#00C951'};">
            <p class="alert-message">${message}</p>
            <button onclick="hideToast()" class="close-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>`;

    setTimeout(hideToast, duration);
}

function hideToast() {
    const alertCont = document.getElementById('alert-container');
    if (alertCont) alertCont.innerHTML = '';
}