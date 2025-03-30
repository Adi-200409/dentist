let menu = document.querySelector('#menu-btn');
let navbar =document.querySelector('.header .nav');
let header=document.querySelector('.header');

menu.onclick =()=>{
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}

window.onscroll=() =>{
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');

    if(window.scrollY>0){
        header.classList.add('active');
    }
    else{
        header.classList.remove('active');
    }
}

document.getElementById("appointmentForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent normal form submission
    
    // Hide appointment details initially
    const appointmentDetails = document.querySelector(".appointment-detail");
    appointmentDetails.style.display = "none"; 

    // Get form values
    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let date = document.getElementById("date").value;
    let time = document.getElementById("time").value;
    let message = document.getElementById("message").value.trim() || "No additional message.";

    // Validation: Ensure all fields are filled
    if (!name || !email || !phone || !date || !time) {
        alert("⚠️ Check your details. All fields are required.");
        return;
    }

    // Email & Phone Validation
    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    let phonePattern = /^[0-9]{10}$/;

    if (!emailPattern.test(email)) {
        alert("⚠️ Check your details. Please enter a valid email address.");
        return;
    }

    if (!phonePattern.test(phone)) {
        alert("⚠️ Check your details. Please enter a valid 10-digit phone number.");
        return;
    }

    // **Fix: Properly Convert Selected Date**
    const selectedDate = new Date(`${date}T00:00:00`); // Ensures correct date format
    const selectedDay = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

    // Convert selected time to hours
    const selectedHour = parseInt(time.split(":")[0], 10); // Extract hour from "HH:MM"

    // **Check if Sunday**
    if (selectedDay === 0) {
        alert("❌ Appointments are not available on Sundays. Please select another day.");
        return;
    }

    // **Check if time is outside 9 AM - 7 PM**
    if (selectedHour < 9 || selectedHour >= 19) {
        alert("❌ Please select a time between 9:00 AM and 7:00 PM.");
        return;
    }

    // Show Appointment Details
    document.getElementById("displayName").textContent = name;
    document.getElementById("displayEmail").textContent = email;
    document.getElementById("displayPhone").textContent = phone;
    document.getElementById("displayDate").textContent = date;
    document.getElementById("displayTime").textContent = time;
    document.getElementById("displayMessage").textContent = message;

    // Display the Appointment Details section
    appointmentDetails.style.display = "block";
    
    // Reset the form after showing details
    this.reset();
});

// Confirm Cancelation Modal
let conf = document.querySelector(".confirm-modal");
conf.style.display = "none";

document.querySelector(".cancel-btn").addEventListener("click", function () {
    document.getElementById("customConfirm").style.display = "flex"; // Show the modal
});

document.getElementById("confirmYes").addEventListener("click", function () {
    document.querySelector(".appointment-detail").style.display = "none"; // Hide details
    document.getElementById("customConfirm").style.display = "none"; // Hide modal
});

document.getElementById("confirmNo").addEventListener("click", function () {
    document.getElementById("customConfirm").style.display = "none"; // Hide modal
});


window.addEventListener("scroll", function () {
    const sign= document.querySelector(".btn2")
    const header = document.querySelector(".header");
    if (window.scrollY > 50) {
        header.classList.add("scrolled");
        sign.style.display="none";
    } else {
        header.classList.remove("scrolled");
        sign.style.display="";
    }
});


// treatment
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

