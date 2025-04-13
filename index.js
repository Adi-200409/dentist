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
function handleFormSubmit(event) {
  event.preventDefault(); // Prevent form submission initially

  const data = {};
  const form = event.target;
  const allInputs = form.querySelectorAll("input");
  const submitBtn = form.querySelector("button[type='submit']");

  allInputs.forEach((inp) => {
      if (inp.name) {
          data[inp.name] = inp.value;
      }
  });

  // Convert time string to Date object for proper time formatting
  const userDate = new Date(data.date);
  const userTime = new Date(`1970-01-01T${data.time}:00`);
  const userDay = userDate.getDay();
  const currentDate = new Date();

  // Reset the time for date comparison (set to midnight)
  currentDate.setHours(0, 0, 0, 0);
  userDate.setHours(0, 0, 0, 0);

  // Date validation
  if (userDate < currentDate) {
      showToast("Select today's or a future date.");
      return;
  }

  // Time validation (if date is today, ensure it's not in the past)
  if (userDate.getTime() === currentDate.getTime() && userTime.getTime() < new Date().getTime()) {
      showToast("Select time after current time.");
      return;
  }

  // Clinic hours validation: Only between 9 AM and 9 PM
  if (userTime.getHours() < 9 || userTime.getHours() >= 21) {
      showToast("We are closed.");
      return;
  }

  // Prevent Sundays
  if (userDay === 0) {
      showToast("We are closed on Sundays.");
      return;
  }

  // If everything is valid, submit the form normally
  form.submit(); // This submits the form after validation

  // Disable the submit button while processing
  submitBtn.disabled = true;
  submitBtn.innerText = "Booking...";

  // Show the success message after 2 seconds (for UI effect)
  setTimeout(() => {
      submitBtn.innerText = "Book Now";
      submitBtn.disabled = false;
      showToast("Appointment booked successfully!", "success");
  }, 2000);
}

appointmentForm.addEventListener("submit", handleFormSubmit);
