<?php

include "conn.php";
print_r($_POST); // Debugging line to check if data is being received correctly
?> 

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JustSmile Dentist website</title>

    <!--font awesome cdn link-->
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
      integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!--custom style file link-->
    <link rel="stylesheet" href="./style.css">

    <!--bootstrap cdn link-->
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/css/bootstrap.min.css"
      integrity="sha512-T584yQ/tdRR5QwOpfvDfVQUidzfgc2339Lc8uBDtcp/wYu80d7jwBgAxbyMh0a9YM9F8N3tdErpFI8iaGx6x5g=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body>

    <div class="overlay" id="overlay"></div>

    <div class="confirm-box" id="confirmBox">
      <p>Confirm your details...!</p>
      <div class="buttons">
        <button class="btn btn-confirm"
          onclick="confirmAction()">Confirm</button>
        <button class="btn btn-cancel"
          onclick="closeConfirmBox()">Cancel</button>
      </div>
    </div>
    <div class="alert-container" id="alert-container"></div>
    <header class="header fixed-top" id="header">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <b><a href="#home" class="logo">JUST<span>Smile</span></a></b>

          <nav class="nav">
            <a href="#home">home</a>
            <a href="#ezy__about13">about</a>
            <a href="#services">services</a>
            <a href="#treatment">Treatment</a>
            <a href="#ezy__contact13">contact</a>
          </nav>

          <!-- <a href="" class="link-btn">Emergency call</a> -->
          <div class="nav-btn">
            <button class="btn_nav">
              <span><a href="emergengy.php">Emergency</a></span>
            </button>
          </div>
          <div id="menu-btn" class="fas fa-bars"></div>
        </div>
      </div>
    </header>

    <!--home section starts-->
   
      <div class="ezy__header34 dark">
        <!-- shape one -->
        <svg
          class="position-absolute top-0 start-0"
          width="62"
          height="62"
          viewBox="0 0 62 62"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path d="M0 62V0H62C62.0281 34.238 34.2662 62 0 62Z" fill="#FF6A35" fill-opacity="0.26" />
        </svg>
      
        <!-- shape two -->
        <svg
          class="ezy__header34-shape-two"
          width="49"
          height="40"
          viewBox="0 0 49 40"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M39.7007 19.7167C64.5265 24.2635 33.2736 43.5256 19.8503 39.4334C6.42703 35.3413 0 30.6059 0 19.7167C0 8.82747 8.8873 0 19.8503 0C30.8134 0 14.8749 15.1699 39.7007 19.7167Z"
            fill="#1DC9FF"
            fill-opacity="0.6"
          />
        </svg>
      
        <!-- shape three -->
        <svg
          class="position-absolute bottom-0 start-0"
          width="100"
          height="301"
          viewBox="0 0 100 301"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M100 7.37581C100 3.30288 96.795 0 92.8428 0C88.8892 0 85.6856 3.30288 85.6856 7.37581H100ZM22.4316 235.974C18.4794 235.974 15.2744 239.275 15.2744 243.349C15.2744 247.422 18.4794 250.725 22.4316 250.725V235.974ZM-78.8428 320.248C-82.795 320.248 -86 323.551 -86 327.624C-86 331.699 -82.795 335 -78.8428 335V320.248ZM6.44099 264.17C6.44099 260.097 3.23743 256.794 -0.716209 256.794C-4.66842 256.794 -7.87341 260.097 -7.87341 264.17H6.44099ZM85.6856 7.37581V97.6368H100V7.37581H85.6856ZM85.6856 97.6368C85.6856 107.708 77.6867 115.966 67.6953 115.966V130.717C85.4809 130.717 100 115.969 100 97.6368H85.6856ZM67.6953 115.966C49.9067 115.966 35.3919 130.728 35.3919 149.058H49.7063C49.7063 138.981 57.7081 130.717 67.6953 130.717V115.966ZM35.3919 149.058V154.391H49.7063V149.058H35.3919ZM35.3919 154.391C35.3919 172.72 49.9067 187.483 67.6953 187.483V172.732C57.7081 172.732 49.7063 164.466 49.7063 154.391H35.3919ZM67.6953 187.483C77.6838 187.483 85.6856 195.747 85.6856 205.823H100C100 187.494 85.4852 172.732 67.6953 172.732V187.483ZM85.6856 205.823V211.728H100V205.823H85.6856ZM85.6856 211.728C85.6856 225.065 75.0972 235.974 61.9208 235.974V250.725C82.9 250.725 100 233.318 100 211.728H85.6856ZM61.9208 235.974H45.9016V250.725H61.9208V235.974ZM45.9016 235.974H22.4316V250.725H45.9016V235.974ZM-78.8428 335H-62.3655V320.248H-78.8428V335ZM-62.3655 335C-24.3722 335 6.44099 303.297 6.44099 264.17H-7.87341C-7.87341 295.134 -32.2623 320.248 -62.3655 320.248V335Z"
            fill="#FDB314"
            fill-opacity="0.32"
          />
        </svg>
        <img
          src="https://cdn.easyfrontend.com/pictures/featured56.png"
          alt=""
          class="position-absolute bottom-0 start-0"
        />
      
        <!-- shape four -->
        <svg
          class="ezy__header34-shape-four"
          width="90"
          height="81"
          viewBox="0 0 90 81"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M85.8436 9.44612L39.2469 77.0032L4.08537 2.92747L85.8436 9.44612Z"
            stroke="#4175DF"
            stroke-opacity="0.78"
            stroke-width="4"
          />
        </svg>
      
        <!-- shape five -->
        <svg
          class="position-absolute bottom-0 end-0"
          width="134"
          height="133"
          viewBox="0 0 134 133"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M66.9999 133C104.003 133 134 103.227 134 66.5C134 29.773 104.003 0 66.9999 0C29.9968 0 0 29.773 0 66.5C0 103.227 29.9968 133 66.9999 133Z"
            fill="#FF9100"
            fill-opacity="0.59"
          />
        </svg>
      
        <!-- shape six -->
        <svg
          class="ezy__header34-shape-six"
          width="149"
          height="255"
          viewBox="0 0 149 255"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <g opacity="0.2">
            <path
              d="M29.9138 200.627C16.1542 200.627 5 211.61 5 225.159C5 238.709 16.1542 249.692 29.9138 249.692C43.6732 249.692 54.8275 238.709 54.8275 225.159V124.964"
              stroke="#4484AB"
              stroke-width="10"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M82.5575 92.916C97.6331 92.916 109.854 80.6999 109.854 65.6317C109.854 50.5624 97.6331 38.3473 82.5575 38.3473C67.4819 38.3473 55.2607 50.5624 55.2607 65.6317C55.2607 80.6999 67.4819 92.916 82.5575 92.916Z"
              stroke="#34C69F"
              stroke-width="10"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M82.7746 124.964C116.395 124.964 143.651 98.1094 143.651 64.9819C143.651 31.8548 116.395 5 82.7746 5C49.1538 5 21.8984 31.8548 21.8984 64.9819"
              stroke="#FDB314"
              stroke-width="10"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </g>
        </svg>
      
        <div class="container">
          <div class="row align-items-center">
            <div class="col-12 col-lg-7 text-center text-lg-start mb-5 mb-lg-0">
              <h2 class="ezy__header34-heading mb-4">Your Smile, Our Passion!</h2>
              <div class="row">
                <div class="col-12 col-lg-8">
                  <p class="ezy__header34-sub-heading mb-">
                    We are committed to providing top-quality dental care
              with a gentle touch. Whether it's a routine check-up
              or a complete smile makeover, we ensure a pain-free
              and pleasant experience.
                  </p>
                  <div class="btns">
                    <button class="btn_nav">
                      <span> <a href="#contact">Make appointment</a></span>
                    </button>
                    <button class="btn_nav">
                      <span> <a href="consultation.html">online Consultation</a></span>
                    </button>
                  </div>
                </div>
                
              </div>
            </div>
            <div class="col-12 col-lg-5 position-relative text-center">
              <div>
                <img class="main_img" src="https://cdn.easyfrontend.com/pictures/quiz_1.png" alt="" class="img-fluid ezy__header34-img" />
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <section class="ezy__about13 light" id="ezy__about13">
        <div class="container">
          <div class="row justify-content-center align-items-center">
            <div class="col-12 col-lg-5 mb-5 mb-lg-0">
              <div class>
                <h1 class="ezy__about13-heading">ABOUT US</h1>
                <hr class="ezy__about13-divider my-4" />
                <p class="ezy__about13-sub-heading mb-2">
                  Welcome to Your Dental Clinic, where your smile is our top
                  priority. Our dedicated team of dental professionals is
                  committed to providing exceptional oral care using
                  state-of-the-art technology in a warm and welcoming
                  environment.</p>

                <h3>Our Mission</h3>
                <p class="ezy__about13-sub-heading mb-0">
                  We believe that everyone deserves a healthy and confident
                  smile. Our mission is to deliver high-quality dental care
                  tailored to each patient’s needs, ensuring comfort and
                  satisfaction in every visit.
                </p>
                <h3>Patient Testimonials</h3>
                <p class="ezy__about13-sub-heading mb-0">
                  Our patients love the care and attention they receive at Your
                  Dental Clinic. Here’s what they have to say:
                </p>

                <blockquote>
                  "The team at Your Dental Clinic is amazing! They made me feel comfortable and explained every step of my treatment. Highly recommend!"
                  – <em>Sarah L.</em>
                </blockquote>
                <blockquote>
                  "Excellent service and friendly staff. My smile has never looked better!"
                  – <em>John M.</em>
                </blockquote>
                <h3>Why Choose Us?</h3>
                <ul>
                  <li><strong>Experienced Professionals:</strong> Our dentists
                    have years of expertise in various dental fields.</li>
                  <li><strong>Advanced Technology:</strong> We use the latest
                    dental equipment for precise and comfortable
                    treatments.</li>
                  <li><strong>Personalized Care:</strong> Each patient
                    receives a customized treatment plan for optimal
                    results.</li>
                  <li><strong>Relaxing Environment:</strong> Our clinic is
                    designed to provide a stress-free and comfortable
                    experience.</li>
                  <li><strong>Affordable Pricing:</strong> We offer
                    transparent pricing and flexible payment options.</li>
                </ul>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="ezy__about13-bg-holder"></div>
              <div class="position-relative">
                <img src="img/images (1).jpeg" alt class="img-fluid" />
                <img
                  src="img/0170678001639425089.jpg"
                  alt
                  class="img-fluid ezy__about13-img2" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="ezy__service20 light" id="services">
        <!-- shape one -->
        <svg
          class="ezy__service20-shape-one position-absolute"
          width="405"
          height="626"
          viewBox="0 0 405 626"
          fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <rect
            x="-302.65"
            y="296.986"
            width="433.92"
            height="140"
            rx="73.8464"
            transform="rotate(-33.796 -302.65 296.986)"
            fill="#7434F8"
            fill-opacity="0.5" />
          <rect
            x="-315"
            y="502.403"
            width="666.584"
            height="140"
            rx="73.8464"
            transform="rotate(-33.796 -315 502.403)"
            fill="#FAA515"
            fill-opacity="0.5" />
        </svg>
        <!-- shape two -->
        <svg
          class="ezy__service20-shape-two position-absolute end-0"
          width="340"
          height="658"
          viewBox="0 0 495 778"
          fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <circle cx="389" cy="389" r="389" fill="var(--ezy-theme-color)"
            fill-opacity="0.19" />
        </svg>

        <div class="container">
          <div class="row">
            <div class="col-12 col-md-4">
              <h2 class="ezy__service20-heading mb-3">
                Exceptional Dental Care for a Healthy Smile
              </h2>
              <p class="ezy__service20-sub-heading">
                We provide high-quality dental services, ensuring comfort and
                precision with state-of-the-art technology.
              </p>
            </div>
            <div class="col-12 col-md-8">
              <div class="row ezy__service20-cards">
                <div class="col-12 col-md-6">
                  <div>
                    <div
                      class="card ezy__service20-card rounded-0 border-0 p-3">
                      <div class="card-body">
                        <div class="ezy__service20-icon mb-2">
                          <i class="fas fa-tooth"></i>
                        </div>
                        <h5 class="my-4">Teeth Whitening</h5>
                        <p class="opacity-75 mt-3">
                          Brighten your smile with our advanced teeth whitening
                          treatments, providing a long-lasting and radiant look.
                        </p>
                      </div>
                    </div>
                    <div
                      class="card ezy__service20-card rounded-0 border-0 p-3 mt-4">
                      <div class="card-body">
                        <div class="ezy__service20-icon mb-2">
                          <i class="fas fa-user-md"></i>
                        </div>
                        <h5 class="my-4">Dental Check-ups</h5>
                        <p class="opacity-75 mt-3">
                          Regular check-ups and cleanings to maintain optimal
                          oral
                          health and prevent potential dental issues.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="mt-md-5">
                    <div
                      class="card ezy__service20-card rounded-0 border-0 p-3 mt-4">
                      <div class="card-body">
                        <div class="ezy__service20-icon mb-2">
                          <i class="fas fa-teeth"></i>
                        </div>
                        <h5 class="my-4">Dental Implants</h5>
                        <p class="opacity-75 mt-3">
                          Restore your smile with high-quality dental implants
                          designed for durability and a natural appearance.
                        </p>
                      </div>
                    </div>
                    <div
                      class="card ezy__service20-card rounded-0 border-0 p-3 mt-4">
                      <div class="card-body">
                        <div class="ezy__service20-icon mb-2">
                          <i class="fas fa-smile"></i>
                        </div>
                        <h5 class="my-4">Cosmetic Dentistry</h5>
                        <p class="opacity-75 mt-3">
                          Enhance your smile with cosmetic treatments, including
                          veneers, bonding, and contouring for a perfect look.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!--process section starts-->
      <section class="process" id="process">
        <h1 class="heading">
          treatment process
        </h1>

        <div class="box-container container">
          <div class="box">
            <img src="img/process-1 (1).png" alt>
            <h3>cosmetic dentistry</h3>
            <p>a method of professional oral care that focuses on
              improving the appearance of your teeth.</p>
          </div>

          <div class="box">
            <img src="./img/process-2 (1).png" alt>
            <h3>pediatric dentistry</h3>
            <p>we focuses on caring for children from infancy through
              the teenage years, for thier tooth care </p>
          </div>

          <div class="box">
            <img src="./img/process-3 (1).png" alt>
            <h3>dental dentistry</h3>
            <p>dental and oral medicine conditions, procedures, and
              surgery. Peer reviewed and up-to-date
              recommendations</p>
          </div>
        </div>
      </section>
      <!-- treatment -->
      <section class="treatment" id="treatment">
        <div class="cont">
          <h1 class="heading">Dental Treatments and Prices</h1>
          <input type="text" id="search"
            placeholder="Search for treatments..."
            onkeyup="filterTreatments()">
          <table id="treatmentsTable">
            <thead>
              <tr>
                <th>Treatment</th>
                <th>Price (INR)</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Consultation</td><td>500</td></tr>
              <tr><td>Teeth Cleaning &
                  Polishing</td><td>1,500</td></tr>
              <tr><td>Composite Filling (per
                  tooth)</td><td>1,500</td></tr>
              <tr><td>Root Canal Treatment
                  (Anterior)</td><td>5,000</td></tr>
              <tr><td>Root Canal Treatment
                  (Molar)</td><td>6,000</td></tr>
              <tr><td>Porcelain-Fused-to-Metal
                  Crown</td><td>8,000</td></tr>
              <tr><td>Zirconia Crown</td><td>15,000</td></tr>
              <tr><td>Dental Implant</td><td>35,000</td></tr>
              <tr><td>Complete Denture (Upper or
                  Lower)</td><td>17,000</td></tr>
              <tr><td>Metal Braces</td><td>40,000</td></tr>
              <tr><td>Clear Aligners</td><td>150,000</td></tr>
              <tr><td>Tooth Extraction
                  (Simple)</td><td>2,000</td></tr>
              <tr><td>Wisdom Tooth Extraction</td><td>7,000</td></tr>
              <tr><td>Teeth Whitening</td><td>7,900</td></tr>
              <tr><td>Gum Surgery (Per
                  Quadrant)</td><td>5,000</td></tr>
              <tr><td>Fluoride Treatment</td><td>2,500</td></tr>
              <tr><td>Dental Sealants (per
                  tooth)</td><td>1,000</td></tr>
              <tr><td>Full Mouth
                  Rehabilitation</td><td>120,000</td></tr>
              <tr><td>Night Guard</td><td>5,500</td></tr>
              <tr><td>Smile Designing</td><td>50,000</td></tr>
              <tr><td>Laser Gum Contouring</td><td>10,000</td></tr>
              <tr><td>Veneers (per tooth)</td><td>12,000</td></tr>
              <tr><td>TMJ Treatment</td><td>25,000</td></tr>
              <tr><td>Jaw Surgery</td><td>200,000</td></tr>
              <tr><td>Pediatric Dentistry</td><td>Varies</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- team -->
      <section class="ezy__team20 gray">
        <div class="container">
          <div class="row justify-content-center mb-4 mb-md-5">
            <div class="col-lg-6 col-xl-5 text-center">
              <h2 class="ezy__team20-heading mb-3">Meet Our Dental Experts</h2>
              <p class="ezy__team20-sub-heading mb-0">
                Our experienced and caring team is dedicated to providing
                top-quality dental care to ensure your best smile.
              </p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-lg-3">
              <div class="ezy__team20-item mt-4 p-3">
                <div class="ezy__team20-content">
                  <img class="profile_pic"
                    src="img/489529876_18046618604370679_6801083904091207359_n.webp"
                    alt="Dr. Aryan Sharma"
                    class="img-fluid w-100" />
                  <div class="p-3">
                    <h5 class="mb-1 fw-bold">Dr. ADITYA Patil</h5>
                    <p class="mb-0 small opacity-75">Founder / Chief Dentist</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="ezy__team20-item mt-4 p-3">
                <div class="ezy__team20-content">
                  <img class="profile_pic"
                    src="img/Shreepati.jpg"
                    alt="Dr. Priya Mehta"
                    class="img-fluid w-100" />
                  <div class="p-3">
                    <h5 class="mb-1 fw-bold">Dr. SHREEPATI Patil</h5>
                    <p class="mb-0 small opacity-75">Pediatric Dentist</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="ezy__team20-item mt-4 p-3">
                <div class="ezy__team20-content">
                  <img class="profile_pic"
                    src="img/nutti.jpg"
                    alt="Dr. Raj Malhotra"
                    class="img-fluid w-100" />
                  <div class="p-3">
                    <h5 class="mb-1 fw-bold">Dr. NUTAN Holi</h5>
                    <p class="mb-0 small opacity-75">Orthodontist</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="ezy__team20-item mt-4 p-3">
                <div class="ezy__team20-content">
                  <img class="profile_pic"
                    src="img/akash.jpg"
                    alt="Dr. Anjali Verma"
                    class="img-fluid w-100" />
                  <div class="p-3">
                    <h5 class="mb-1 fw-bold">Dr. AKASH Manlokar</h5>
                    <p class="mb-0 small opacity-75">Cosmetic Dentist</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!--reviews section starts-->
      <section class="ezy__testimonial23 light">
        <!-- shape one -->
        <svg class="ezy__testimonial23-shape-one" width="404" height="572"
          viewBox="0 0 404 572" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="118" cy="286" r="265.5" stroke="#4175DF"
            stroke-opacity="0.2" stroke-width="41" />
        </svg>
        <!-- shape two -->
        <svg class="ezy__testimonial23-shape-two" width="269" height="479"
          viewBox="0 0 269 479" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="239.5" cy="239.5" r="239.5" fill="#FC4755"
            fill-opacity="0.25" />
        </svg>

        <div class="container">
          <div
            class="row align-items-center justify-content-between mb-4 mb-md-5">
            <div class="col-12 col-md-6 col-lg-4">
              <h2 class="ezy__testimonial23-heading mb-0">What Our Patients
                Say</h2>
            </div>
            <div class="col-12 col-md-6 col-lg-5">
              <p class="ezy__testimonial23-sub-heading mb-0">
                Our patients' smiles and satisfaction speak for our quality
                dental
                care. Read their experiences below.
              </p>
            </div>
          </div>
          <div id="ezy__testimonial23-carousel" class="carousel slide"
            data-bs-ride="carousel">
            <div class="carousel-indicators m-0">
              <button type="button"
                data-bs-target="#ezy__testimonial23-carousel"
                data-bs-slide-to="0" class="active" aria-current="true"
                aria-label="Slide 1"></button>
              <button type="button"
                data-bs-target="#ezy__testimonial23-carousel"
                data-bs-slide-to="1" aria-label="Slide 2"></button>
              <button type="button"
                data-bs-target="#ezy__testimonial23-carousel"
                data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner overflow-visible">
              <div class="carousel-item active">
                <div class="row">
                  <div class="col-12 col-lg-4">
                    <div class="card ezy__testimonial23-item border-0 p-4 mt-4">
                      <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                          <div class="me-3">
                            <img
                              src="img/arpita.jpg"
                              alt class="img-fluid rounded-circle border"
                              width="65" />
                          </div>
                          <div>
                            <h4 class="mb-0 fs-5">Sarah Johnson</h4>
                            <p class="mb-0 small">Dental Implant Patient</p>
                          </div>
                        </div>
                        <p class="opacity-75">
                          The dental implant procedure was seamless, and the
                          results exceeded my expectations. Highly recommend!
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-lg-4">
                    <div class="card ezy__testimonial23-item border-0 p-4 mt-4">
                      <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                          <div class="me-3">
                            <img
                              src="https://cdn.easyfrontend.com/pictures/testimonial/testimonial_square_2.jpeg"
                              alt class="img-fluid rounded-circle border"
                              width="65" />
                          </div>
                          <div>
                            <h4 class="mb-0 fs-5">Michael Lee</h4>
                            <p class="mb-0 small">Teeth Whitening Patient</p>
                          </div>
                        </div>
                        <p class="opacity-75">
                          My teeth have never looked whiter! The treatment was
                          quick and painless. I’m so happy with my new smile.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-lg-4">
                    <div class="card ezy__testimonial23-item border-0 p-4 mt-4">
                      <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                          <div class="me-3">
                            <img
                              src="https://cdn.easyfrontend.com/pictures/testimonial/testimonial_square_3.jpeg"
                              alt class="img-fluid rounded-circle border"
                              width="65" />
                          </div>
                          <div>
                            <h4 class="mb-0 fs-5">Emma Brown</h4>
                            <p class="mb-0 small">Cosmetic Dentistry Patient</p>
                          </div>
                        </div>
                        <p class="opacity-75">
                          The cosmetic work I had done here transformed my
                          confidence. The team was professional and caring.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!--conatct section starts-->
      <!-- <section class="contact" id="contact">
        <h1 class="heading">our appointment</h1>

        <form action="./appointment.php" method="POST">
            <span>your name:</span>
            <input type="text" name="name" placeholder="Enter your name" class="box">

            <span>your meail:</span>
            <input type="email" name="email" placeholder="Enter your email" class="box">

            <span>your number:</span>
            <input type="number" name="phone" placeholder="Enter your mobile number" class="box">

            <span>appointment date:</span>
            <input type="datetime-local" name="date" class="box">

            <input type="submit" value="make appointment" name="appointment" class="link-btn">
        </form>
    </section> -->

      <!-- appointment -->
      <section class="contact" id="contact">
        <h1 class="appointment_heading">BOOK YOUR APPOINTMENT</h1>
        <div class="formbold-main-wrapper">
          <div class="formbold-form-wrapper">
            <!-- <form action="appointment.php" method="POST" id="appointmentForm">
              <div class="formbold-mb-5">
                <label for="name" class="formbold-form-label"> Full Name </label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  placeholder="Full Name"
                  class="formbold-form-input" 
                />
              </div>
              <div class="formbold-mb-5">
                <label for="phone" class="formbold-form-label"> Phone Number </label>
                <input
                  type="text"
                  name="phone"
                  id="phone"
                  placeholder="Enter your phone number"
                  class="formbold-form-input" 
                />
              </div>
              <div class="formbold-mb-5">
                <label for="email" class="formbold-form-label"> Email Address </label>
                <input
                  type="email"
                  name="email"
                  id="email"
                  placeholder="Enter your email"
                  class="formbold-form-input" required
                />
              </div>
              <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                  <div class="formbold-mb-5 w-full">
                    <label for="date" class="formbold-form-label"> Date </label>
                    <input
                      type="date"
                      name="date"
                      id="date"
                      class="formbold-form-input" required
                    />
                  </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                  <div class="formbold-mb-5">
                    <label for="time" class="formbold-form-label"> Time </label>
                    <input
                      type="time"
                      name="time"
                      id="time"
                      class="formbold-form-input" required
                    />
                  </div>
                </div>
              </div>
        
              <div class="formbold-mb-5 formbold-pt-3">
                <label class="formbold-form-label formbold-form-label-2">
                  Address Details
                </label>
                <div class="flex flex-wrap formbold--mx-3">
                  <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                      <input
                        type="text"
                        name="area"
                        id="area"
                        placeholder="Enter area"
                        class="formbold-form-input" required
                      />
                    </div>
                  </div>
                  <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                      <input
                        type="text"
                        name="city"
                        id="city"
                        placeholder="Enter city"
                        class="formbold-form-input" required
                      />
                    </div>
                  </div>
                  <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                      <input
                        type="text"
                        name="state"
                        id="state"
                        placeholder="Enter state"
                        class="formbold-form-input" required
                      />
                    </div>
                  </div>
                  <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                      <input
                        type="text"
                        name="postcode"
                        id="postcode"
                        placeholder="Post Code"
                        class="formbold-form-input" required
                      />
                    </div>
                  </div>
                </div>
              </div>
        
              <div>
                <button type="submit" class="formbold-btn" name="book">Book Appointment</button>
              </div>
            </form> -->
            <?php if (isset($success)): ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form action="appointment.php" method="POST" id="appointmentForm">
        <div class="formbold-mb-5">
            <label for="name" class="formbold-form-label">Full Name</label>
            <input
                type="text"
                name="name"
                id="name"
                placeholder="Full Name"
                class="formbold-form-input"
                required
            />
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?php echo $errors['name']; ?></span>
            <?php endif; ?>
        </div>

        <div class="formbold-mb-5">
            <label for="phone" class="formbold-form-label">Phone Number</label>
            <input
                type="text"
                name="phone"
                id="phone"
                placeholder="Enter your phone number"
                class="formbold-form-input"
                required
            />
            <?php if (isset($errors['phone'])): ?>
                <span class="error"><?php echo $errors['phone']; ?></span>
            <?php endif; ?>
        </div>

        <div class="formbold-mb-5">
            <label for="email" class="formbold-form-label">Email Address</label>
            <input
                type="email"
                name="email"
                id="email"
                placeholder="Enter your email"
                class="formbold-form-input"
                required
            />
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
        </div>

        <div class="flex flex-wrap formbold--mx-3">
            <div class="w-full sm:w-half formbold-px-3">
                <div class="formbold-mb-5 w-full">
                    <label for="date" class="formbold-form-label">Date</label>
                    <input
                        type="date"
                        name="date"
                        id="date"
                        class="formbold-form-input"
                        required
                    />
                    <?php if (isset($errors['date'])): ?>
                        <span class="error"><?php echo $errors['date']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="w-full sm:w-half formbold-px-3">
                <div class="formbold-mb-5">
                    <label for="time" class="formbold-form-label">Time</label>
                    <input
                        type="time"
                        name="time"
                        id="time"
                        class="formbold-form-input"
                        required
                    />
                    <?php if (isset($errors['time'])): ?>
                        <span class="error"><?php echo $errors['time']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="formbold-mb-5 formbold-pt-3">
            <label class="formbold-form-label formbold-form-label-2">
                Address Details
            </label>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <input
                            type="text"
                            name="area"
                            id="area"
                            placeholder="Enter area"
                            class="formbold-form-input"
                            required
                        />
                        <?php if (isset($errors['area'])): ?>
                            <span class="error"><?php echo $errors['area']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <input
                            type="text"
                            name="city"
                            id="city"
                            placeholder="Enter city"
                            class="formbold-form-input"
                            required
                        />
                        <?php if (isset($errors['city'])): ?>
                            <span class="error"><?php echo $errors['city']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <input
                            type="text"
                            name="state"
                            id="state"
                            placeholder="Enter state"
                            class="formbold-form-input"
                            required
                        />
                        <?php if (isset($errors['state'])): ?>
                            <span class="error"><?php echo $errors['state']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <input
                            type="text"
                            name="postcode"
                            id="postcode"
                            placeholder="Post Code"
                            class="formbold-form-input"
                            required
                        />
                        <?php if (isset($errors['postcode'])): ?>
                            <span class="error"><?php echo $errors['postcode']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <button type="submit" class="formbold-btn" name="book">
                <?php echo isset($_POST['book']) ? 'Processing...' : 'Book Appointment'; ?>
            </button>
        </div>
       
    </form>
          </div>
          </div>
        </section>

        <section id="appointmentDetails">

        </section>
      </section>
      <!-- contact -->
     <!-- <div class="get_tuch">
<div class="form-container">
  <div class="form">
      <span class="heading">Get in touch</span>
      <input placeholder="Name" type="text" class="input">
      <input placeholder="Email" id="mail" type="email" class="input">
      <textarea placeholder="Write Message" rows="10" cols="30" id="message" name="message" class="textarea"></textarea>
      <div class="button-container">
      <div class="send-button reset-button">Send</div>
      <div class="reset-button-container">
          <div id="reset-btn" class="reset-button">Reset</div>
      </div>
  </div>
</div>
</div>
</div> -->
      <!--footer section starst-->
      <section class="ezy__footer15 dark">
        <div class="container">
          <div class="row text-center text-sm-start mb-md-5">
            <div class="col-lg-4">
              <h2 class="fw-bold margin"><span
                  class="color">JUST</span><span>Smile</span></h2>
              <div class="row">
                <div class="col-12 col-lg-7">
                  <p class="ezy__footer15-text opacity-50 mt-3">
                    clab rood camp belguam
                  </p>
                </div>
              </div>

              <h5 class="mt-4 mt-lg-5 mb-3 margin">Follow Us</h5>
              <ul
                class="ezy__footer15-social justify-content-center justify-content-sm-start nav mb-4 mb-lg-0">
                <li>
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fab fa-pinterest-p"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </li>
                <li>
                  <a href="#"><i class="fab fa-instagram"></i></a>
                </li>
              </ul>
            </div>
            <div class="col-sm-6 col-lg-2 mt-4 mt-lg-0">
              <h5 class="mb-3">Menu</h5>
              <ul class="nav flex-column ezy__footer15-quick-links size">
                <li>
                  <a href="#!">Home</a>
                </li>
                <li>
                  <a href="#!">About</a>
                </li>
                <li>
                  <a href="#!">Service</a>
                </li>
                <li>
                  <a href="#!">Process</a>
                </li>
              </ul>
            </div>
            <div class="col-sm-6 col-lg-2 mt-4 mt-lg-0">
              <h5 class="mb-3">Menu</h5>
              <ul class="nav flex-column ezy__footer15-quick-links size">
                <li>
                  <a href="#!">Treatment</a>
                </li>
                <li>
                  <a href="#!">Team</a>
                </li>
                <li>
                  <a href="#!">Reviews</a>
                </li>
                <li>
                  <a href>Appointment</a>
                </li>
              </ul>
            </div>

          </div>
          <hr />
          <div
            class="row d-flex justify-content-between align-items-center text-center text-lg-start">
            <div class="col-md-6">
              <p class="opacity-50 mb-0 mt-1">Copyright &copy; JUSTSmile, All
                rights reserved</p>
            </div>
            <div class="col-md-6">
              <ul
                class="ezy__footer15-nav nav justify-content-center justify-content-md-end mt-1">
                <li class="nav-item">
                  <a class="opacity-50 nav-link" href="#!">Privacy</a>
                </li>
                <li class="nav-item">
                  <a class="opacity-50 nav-link" href="#!">Security</a>
                </li>
                <li class="nav-item">
                  <a class="opacity-50 nav-link" href="#!">Terms</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <script src="./index.js"></script>

    </body>
  </html>