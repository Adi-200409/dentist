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

document.addEventListener("DOMContentLoaded", () => {
    // Smooth Scrolling for Navigation Links
    document.querySelectorAll("nav ul li a").forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const targetId = this.getAttribute("href").substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 50,
                    behavior: "smooth"
                });
            }
        });
    });

    // Fade-in Effect on Scroll
    const elements = document.querySelectorAll(".about h2, .about h3, .about p, .about ul, blockquote");
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = 1;
                entry.target.style.transform = "translateY(0)";
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(element => {
        element.style.opacity = 0;
        element.style.transform = "translateY(20px)";
        observer.observe(element);
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("appointmentForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission

        let selectedDate = document.getElementById("date").value;
        let selectedTime = document.getElementById("time").value;

        console.log("Selected Date:", selectedDate);
        console.log("Selected Time:", selectedTime);

        if (!selectedDate || !selectedTime) {
            showCustomAlert("Please select both date and time.");
            return;
        }

        let dateObj = new Date(selectedDate);
        let selectedDay = dateObj.getDay(); // 0 = Sunday

        let [hours, minutes] = selectedTime.split(":").map(Number);

        if (selectedDay === 0) {
            showCustomAlert("We are closed on Sundays. Please select another day.");
            return;
        }

        if (hours < 9 || hours >= 21) {
            showCustomAlert("We are closed during this time. Please select a time between 9 AM and 9 PM.");
            return;
        }

        // If all validations pass, proceed with the booking
        showCustomAlert("Your appointment has been booked successfully!", "success");
    });

    // Custom alert function
    function showCustomAlert(message, type = "error") {
        let alertBox = document.createElement("div");
        alertBox.className = `custom-alert ${type}`;
        alertBox.innerHTML = `<p>${message}</p><button onclick="this.parentElement.remove()">OK</button>`;
        document.body.appendChild(alertBox);
    }

    // Add CSS for styling the custom alert
    let style = document.createElement("style");
    style.innerHTML = `
        .custom-alert {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ff4d4d;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }
        .custom-alert.success {
            background: #4caf50;
        }
        .custom-alert button {
            margin-top: 10px;
            background: white;
            color: #ff4d4d;
            border: none;
            padding: 5px 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .custom-alert.success button {
            color: #4caf50;
        }
        .custom-alert button:hover {
            background: #f1f1f1;
        }
    `;
    document.head.appendChild(style);
});
