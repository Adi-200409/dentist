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

const appointmentForm =document.getElementById("appointmentForm");
const appointmentDetails = document.getElementById("appointmentDetails");

function handleFormSubmit(event) {
  event.preventDefault();
  const data = {};
  const form = event.target;
  const allInputs = form.querySelectorAll("input");
  const submitBtn = form.querySelector("button[type='submit']");

  allInputs.forEach((inp) => {
    if (inp.name) {
      data[inp.name] = inp.value;
    }
  });

  const userDate = new Date(data.date);
  const userTime = parseInt(data.time);
  const userDay = userDate.getDay();
  const currentDate = new Date();

  currentDate.setHours(0, 0, 0, 0);
  userDate.setHours(0, 0, 0, 0);

  if (userDate < currentDate) {
    // alert("Select today's or a future date.");
    showToast("Select today's or a future date.");
    return;
  }

  if (userDate.getTime() < currentDate.getTime()) {
    showToast("Select time after current time.");
  }

  if (userTime < 9 || userTime >= 21) {
    // alert("We are closed.");
    showToast("Clinic is closed");
    return;
  }

  if (userDay === 0) {
    // alert("We are closed on Sundays.");
    showToast("We are closed on Sundays.");
    return;
  }

//   alert("Appointment booked successfully!");
    showConfirmBox()
  submitBtn.disabled = true;
  submitBtn.innerText = "Booking...";
  setTimeout(() => {
    showDetails(data);
    submitBtn.innerText = "Book Now";
    submitBtn.disabled = false;
    showToast("Appointment booked successfully!", "success");
  }, 2 * 1000);
}



appointmentForm.addEventListener("submit", handleFormSubmit);

function showDetails(data) {
  const appointmentDetails = document.getElementById("appointmentDetails");
  appointmentDetails.innerHTML = `
        <h2>Appointment Details</h2>
        <p><strong>Name:</strong> <span id="displayName">${data.name}</span></p>
        <p><strong>Email:</strong> <span id="displayEmail">${data.email}</span></p>
        <p><strong>Phone:</strong> <span id="displayPhone">${data.phone}</span></p>
        <p><strong>Date:</strong> <span id="displayDate">${data.date}</span></p>
        <p><strong>Time:</strong> <span id="displayTime">${data.time.toLocaleString()}</span></p>
        <button class="cancal_btn">
            <svg viewBox="0 0 448 512" class="svgIcon"><path
                d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
          </button>
  `;
    appointmentDetails.classList.add("appointment-details");
  appointmentDetails.style.display = "block";
  console.log("showDetails", data);
}

const alertCont = document.getElementById("alert-container");

function showToast(
  message = "Something went wrong",
  type = "error",
  duration = 3000
) {
  toastType = type === "error" ? true : false;

  alertCont.innerHTML = `
    <div id="alert-wrapper" style="--bg-clr: ${
      toastType ? "#FFC9C9" : "#B9F8CF"
    }; --border-clr: ${toastType ? "#fca5a5" : "#00C951"};">
        <p class="alert-message" id="alert-message">${message}</p>
        <button onclick="hideToast()" class="close-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>`;

  setTimeout(() => {
    alertCont.innerHTML = "";
  }, duration);
}

function hideToast() {
  alertCont.innerHTML = "";
}
function showConfirmBox() {
    document.getElementById("overlay").style.display = "block";
    document.getElementById("confirmBox").style.display = "block";
}

function closeConfirmBox() {
    document.getElementById("overlay").style.display = "none";
    document.getElementById("confirmBox").style.display = "none";
}

function confirmAction() {
    alert("Confirmed!");
    closeConfirmBox();
}