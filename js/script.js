/* ==========================================================================
   StudentHub Portal - Digital Campus Scripts
   CHAROTAR UNIVERSITY OF SCIENCE AND TECHNOLOGY (CHARUSAT)
   Department of Information Technology
   Vanilla JavaScript (No Frameworks)
   ========================================================================== */

/* ==========================================================================
   PRACTICAL 4 COMPONENTS (DOM Manipulation, Events, Slider & Theme Switcher)
   ========================================================================== */

/* 1. Notification Banner */
const notification = document.getElementById("notification");
const closeBtn = document.getElementById("closeBtn");
const showNotificationBtn = document.getElementById("show-notification-btn");

if (closeBtn && notification) {
    closeBtn.addEventListener("click", function () {
        notification.style.display = "none";
    });
}

if (showNotificationBtn && notification) {
    showNotificationBtn.addEventListener("click", function () {
        notification.style.display = "flex";
    });
}

/* 2. Change Greeting Text */
const changeTextBtn = document.getElementById("change-text-btn");
const greetingHeading = document.getElementById("yug");

if (changeTextBtn && greetingHeading) {
    changeTextBtn.addEventListener("click", function () {
        if (greetingHeading.textContent.includes("Bye")) {
            greetingHeading.textContent = "Hello my friend 👍";
        } else {
            greetingHeading.textContent = "Bye 👋";
        }
    });
}

/* 3. Dark / Light Mode Switcher (Persisted via localStorage) */
const toggleBtn = document.getElementById("theme-toggle");
const currentTheme = localStorage.getItem("theme");

// Apply stored theme on initial load
if (currentTheme === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
    if (toggleBtn) {
        toggleBtn.textContent = "Light Mode";
    }
} else {
    document.documentElement.setAttribute("data-theme", "light");
    if (toggleBtn) {
        toggleBtn.textContent = "Dark Mode";
    }
}

// Toggle theme on button click
if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
        const isDark = document.documentElement.getAttribute("data-theme") === "dark";

        if (isDark) {
            document.documentElement.setAttribute("data-theme", "light");
            localStorage.setItem("theme", "light");
            toggleBtn.textContent = "Dark Mode";
        } else {
            document.documentElement.setAttribute("data-theme", "dark");
            localStorage.setItem("theme", "dark");
            toggleBtn.textContent = "Light Mode";
        }
    });
}

/* 4. CHARUSAT Image Slider */
const sliderImages = [
    "../docs/campus.jpg",
    "../docs/wireframe/home-wireframe.png",
    "../docs/wireframe/dashboard-wireframe.png",
    "../docs/wireframe/events-wireframe.png",
    "../docs/wireframe/login-wireframe.png"
];

let currentSlide = 0;
const sliderImage = document.getElementById("slider-image");
const prevBtn = document.getElementById("prev-slide");
const nextBtn = document.getElementById("next-slide");

function showSlide(index) {
    if (!sliderImage) return;

    currentSlide = (index + sliderImages.length) % sliderImages.length;
    sliderImage.style.opacity = "0";

    setTimeout(function () {
        sliderImage.src = sliderImages[currentSlide];
        sliderImage.style.opacity = "1";
    }, 200);
}

if (nextBtn) {
    nextBtn.addEventListener("click", function () {
        showSlide(currentSlide + 1);
    });
}

if (prevBtn) {
    prevBtn.addEventListener("click", function () {
        showSlide(currentSlide - 1);
    });
}

// Automatic slide transition every 3 seconds (only on pages with slider)
if (sliderImage && prevBtn && nextBtn) {
    setInterval(function () {
        showSlide(currentSlide + 1);
    }, 3000);
}


/* ==========================================================================
   PRACTICAL 5: REGISTRATION FORM VALIDATION & USER-FRIENDLY ERROR HANDLING
   ==========================================================================
   Features Implemented:
   - Form field validations with robust Regular Expressions
   - Accessible error messages near fields (ARIA & Role alert)
   - Password strength meter (0-5 calculation with visual bar & checklist)
   - Password visibility show/hide toggle buttons
   - Intermediate Extension: Real-time validation (keyup/input/change/blur)
   - Advanced Extension: Custom Canvas-based CAPTCHA with distortion & noise
   - Clean reset and form success feedback
   ========================================================================== */

const registrationForm = document.getElementById("registrationForm");

if (registrationForm) {

    // --- Form Fields ---
    const studentName = document.getElementById("studentName");
    const email = document.getElementById("email");
    const mobile = document.getElementById("mobile");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const course = document.getElementById("course");
    const year = document.getElementById("year");
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    const genderFieldset = document.querySelector(".gender-fieldset");
    const terms = document.getElementById("terms");
    const captchaInput = document.getElementById("captchaInput");

    // --- Error Containers ---
    const nameError = document.getElementById("nameError");
    const emailError = document.getElementById("emailError");
    const mobileError = document.getElementById("mobileError");
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");
    const courseError = document.getElementById("courseError");
    const yearError = document.getElementById("yearError");
    const genderError = document.getElementById("genderError");
    const termsError = document.getElementById("termsError");
    const captchaError = document.getElementById("captchaError");

    // --- Global Form Alerts ---
    const formSuccessMessage = document.getElementById("formSuccessMessage");
    const formErrorMessage = document.getElementById("formErrorMessage");

    // --- Password Strength Meter Elements ---
    const strengthBarFill = document.getElementById("strengthBarFill");
    const strengthText = document.getElementById("strengthText");
    const ruleLength = document.getElementById("ruleLength");
    const ruleUpper = document.getElementById("ruleUpper");
    const ruleLower = document.getElementById("ruleLower");
    const ruleNumber = document.getElementById("ruleNumber");
    const ruleSpecial = document.getElementById("ruleSpecial");

    // --- Password Visibility Toggle Buttons ---
    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const toggleConfirmPasswordBtn = document.getElementById("toggleConfirmPasswordBtn");

    // --- Canvas CAPTCHA Elements ---
    const captchaCanvas = document.getElementById("captchaCanvas");
    const refreshCaptchaBtn = document.getElementById("refreshCaptchaBtn");
    let currentCaptchaCode = "";


    /* ======================================================================
       HELPER FUNCTIONS: Error & Success UI Handling
       ====================================================================== */

    function setFieldError(inputElement, errorElement, message) {
        if (inputElement && inputElement.classList) {
            inputElement.classList.add("input-error");
            inputElement.classList.remove("input-success");
            inputElement.setAttribute("aria-invalid", "true");
        }
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add("visible");
        }
        return false;
    }

    function setFieldSuccess(inputElement, errorElement) {
        if (inputElement && inputElement.classList) {
            inputElement.classList.remove("input-error");
            inputElement.classList.add("input-success");
            inputElement.setAttribute("aria-invalid", "false");
        }
        if (errorElement) {
            errorElement.textContent = "";
            errorElement.classList.remove("visible");
        }
        return true;
    }

    function clearFieldState(inputElement, errorElement) {
        if (inputElement && inputElement.classList) {
            inputElement.classList.remove("input-error");
            inputElement.classList.remove("input-success");
            inputElement.removeAttribute("aria-invalid");
        }
        if (errorElement) {
            errorElement.textContent = "";
            errorElement.classList.remove("visible");
        }
    }


    /* ======================================================================
       REGULAR EXPRESSION VALIDATIONS FOR FORM FIELDS
       ====================================================================== */

    // 1. Full Name Validation: Alphabets and spaces only, 3 to 50 characters
    function validateName() {
        const value = studentName.value.trim();
        const nameRegex = /^[A-Za-z\s]+$/;

        if (value === "") {
            return setFieldError(studentName, nameError, "Full name is required.");
        }
        if (!nameRegex.test(value)) {
            return setFieldError(studentName, nameError, "Full name should contain letters and spaces only.");
        }
        if (value.length < 3) {
            return setFieldError(studentName, nameError, "Full name must be at least 3 characters long.");
        }
        if (value.length > 50) {
            return setFieldError(studentName, nameError, "Full name cannot exceed 50 characters.");
        }
        return setFieldSuccess(studentName, nameError);
    }

    // 2. Email Validation: Standard email format regex
    function validateEmail() {
        const value = email.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (value === "") {
            return setFieldError(email, emailError, "Email address is required.");
        }
        if (!emailRegex.test(value)) {
            return setFieldError(email, emailError, "Please enter a valid email address (e.g., student@charusat.edu.in).");
        }
        return setFieldSuccess(email, emailError);
    }

    // 3. Mobile Number Validation: 10-digit number starting with 6, 7, 8, or 9
    function validateMobile() {
        const value = mobile.value.trim();
        const mobileRegex = /^[6-9][0-9]{9}$/;

        if (value === "") {
            return setFieldError(mobile, mobileError, "Mobile number is required.");
        }
        if (!/^\d+$/.test(value)) {
            return setFieldError(mobile, mobileError, "Mobile number must contain digits only.");
        }
        if (value.length !== 10) {
            return setFieldError(mobile, mobileError, "Mobile number must be exactly 10 digits.");
        }
        if (!mobileRegex.test(value)) {
            return setFieldError(mobile, mobileError, "Mobile number must start with 6, 7, 8, or 9.");
        }
        return setFieldSuccess(mobile, mobileError);
    }

    // 4. Password Strength Meter & Validation
    function updatePasswordCriterion(element, isMet) {
        if (!element) return;
        const iconSpan = element.querySelector(".rule-icon");
        if (isMet) {
            element.classList.add("met");
            if (iconSpan) iconSpan.textContent = "✓";
        } else {
            element.classList.remove("met");
            if (iconSpan) iconSpan.textContent = "✕";
        }
    }

    function checkPasswordStrength(val) {
        let score = 0;

        const hasLength = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSpecial = /[@$!%*?&#^]/.test(val);

        updatePasswordCriterion(ruleLength, hasLength);
        updatePasswordCriterion(ruleUpper, hasUpper);
        updatePasswordCriterion(ruleLower, hasLower);
        updatePasswordCriterion(ruleNumber, hasNumber);
        updatePasswordCriterion(ruleSpecial, hasSpecial);

        if (hasLength) score++;
        if (hasUpper) score++;
        if (hasLower) score++;
        if (hasNumber) score++;
        if (hasSpecial) score++;

        if (!strengthBarFill || !strengthText) return score;

        if (val.length === 0) {
            strengthBarFill.style.width = "0%";
            strengthBarFill.style.backgroundColor = "transparent";
            strengthText.textContent = "None";
            strengthText.style.color = "var(--text-color)";
        } else if (score <= 2) {
            strengthBarFill.style.width = "30%";
            strengthBarFill.style.backgroundColor = "var(--error-color)";
            strengthText.textContent = "Weak";
            strengthText.style.color = "var(--error-color)";
        } else if (score === 3 || score === 4) {
            strengthBarFill.style.width = "65%";
            strengthBarFill.style.backgroundColor = "var(--warning-color)";
            strengthText.textContent = "Medium";
            strengthText.style.color = "var(--warning-color)";
        } else if (score === 5) {
            strengthBarFill.style.width = "100%";
            strengthBarFill.style.backgroundColor = "var(--success-color)";
            strengthText.textContent = "Strong";
            strengthText.style.color = "var(--success-color)";
        }

        return score;
    }

    function validatePassword() {
        const value = password.value;
        const score = checkPasswordStrength(value);

        if (value === "") {
            return setFieldError(password, passwordError, "Password is required.");
        }
        if (score < 5) {
            return setFieldError(
                password,
                passwordError,
                "Password must contain min 8 chars, 1 uppercase, 1 lowercase, 1 number, and 1 special symbol."
            );
        }
        return setFieldSuccess(password, passwordError);
    }

    // 5. Confirm Password Validation: Exact match with password
    function validateConfirmPassword() {
        const val = confirmPassword.value;
        const passVal = password.value;

        if (val === "") {
            return setFieldError(confirmPassword, confirmPasswordError, "Please confirm your password.");
        }
        if (val !== passVal) {
            return setFieldError(confirmPassword, confirmPasswordError, "Passwords do not match.");
        }
        return setFieldSuccess(confirmPassword, confirmPasswordError);
    }

    // 6. Course Selection Validation
    function validateCourse() {
        if (course.value === "") {
            return setFieldError(course, courseError, "Please select your course.");
        }
        return setFieldSuccess(course, courseError);
    }

    // 7. Year Selection Validation
    function validateYear() {
        if (year.value === "") {
            return setFieldError(year, yearError, "Please select your academic year.");
        }
        return setFieldSuccess(year, yearError);
    }

    // 8. Gender Radio Validation
    function validateGender() {
        const checkedGender = document.querySelector('input[name="gender"]:checked');
        if (!checkedGender) {
            if (genderFieldset) genderFieldset.classList.add("fieldset-error");
            if (genderError) {
                genderError.textContent = "Please select your gender.";
                genderError.classList.add("visible");
            }
            return false;
        }
        if (genderFieldset) genderFieldset.classList.remove("fieldset-error");
        if (genderError) {
            genderError.textContent = "";
            genderError.classList.remove("visible");
        }
        return true;
    }

    // 9. Terms and Conditions Checkbox Validation
    function validateTerms() {
        if (!terms.checked) {
            if (termsError) {
                termsError.textContent = "You must agree to the Terms and Conditions before registering.";
                termsError.classList.add("visible");
            }
            terms.setAttribute("aria-invalid", "true");
            return false;
        }
        if (termsError) {
            termsError.textContent = "";
            termsError.classList.remove("visible");
        }
        terms.setAttribute("aria-invalid", "false");
        return true;
    }

    // 10. Advanced Extension: Custom Canvas CAPTCHA Generation & Validation
    function generateCaptcha() {
        if (!captchaCanvas) return;

        const ctx = captchaCanvas.getContext("2d");
        const chars = "23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz";
        let code = "";

        // Generate 6 random alphanumeric characters
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        currentCaptchaCode = code;

        // Clear previous drawing
        ctx.clearRect(0, 0, captchaCanvas.width, captchaCanvas.height);

        // Background styling: light gradient
        const gradient = ctx.createLinearGradient(0, 0, captchaCanvas.width, captchaCanvas.height);
        gradient.addColorStop(0, "#f3f4f6");
        gradient.addColorStop(1, "#e5e7eb");
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, captchaCanvas.width, captchaCanvas.height);

        // Draw random noise lines
        for (let i = 0; i < 5; i++) {
            ctx.beginPath();
            ctx.moveTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.lineTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.strokeStyle = "rgba(0, 0, 0, " + (0.15 + Math.random() * 0.2) + ")";
            ctx.lineWidth = 1 + Math.random() * 1.5;
            ctx.stroke();
        }

        // Draw random noise dots
        for (let i = 0; i < 35; i++) {
            ctx.beginPath();
            ctx.arc(
                Math.random() * captchaCanvas.width,
                Math.random() * captchaCanvas.height,
                1 + Math.random() * 1.5,
                0,
                Math.PI * 2
            );
            ctx.fillStyle = "rgba(0, 0, 0, " + (0.2 + Math.random() * 0.3) + ")";
            ctx.fill();
        }

        // Render characters with individual color, font size, and slight rotation
        const charSpacing = captchaCanvas.width / (code.length + 1);

        for (let i = 0; i < code.length; i++) {
            const char = code.charAt(i);
            const fontSize = Math.floor(20 + Math.random() * 6);
            ctx.font = "bold " + fontSize + "px 'Courier New', monospace";

            // Dark distinct colors for readability
            const colors = ["#003366", "#8b0000", "#006400", "#4b0082", "#2f4f4f", "#191970"];
            ctx.fillStyle = colors[Math.floor(Math.random() * colors.length)];

            ctx.save();
            const x = charSpacing * (i + 0.6);
            const y = captchaCanvas.height / 2 + 7;
            const angle = (Math.random() - 0.5) * 0.4; // slight tilt between -11 and +11 degrees

            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.fillText(char, 0, 0);
            ctx.restore();
        }
    }

    function validateCaptcha() {
        const val = captchaInput.value.trim();

        if (val === "") {
            return setFieldError(captchaInput, captchaError, "Security CAPTCHA is required.");
        }
        if (val.toLowerCase() !== currentCaptchaCode.toLowerCase()) {
            generateCaptcha();
            captchaInput.value = "";
            return setFieldError(captchaInput, captchaError, "Incorrect CAPTCHA. A new code has been generated.");
        }
        return setFieldSuccess(captchaInput, captchaError);
    }

    // Initialize CAPTCHA on page load
    generateCaptcha();

    if (refreshCaptchaBtn) {
        refreshCaptchaBtn.addEventListener("click", function () {
            generateCaptcha();
            captchaInput.value = "";
            clearFieldState(captchaInput, captchaError);
        });
    }


    /* ======================================================================
       PASSWORD VISIBILITY TOGGLE (Show/Hide)
       ====================================================================== */

    if (togglePasswordBtn && password) {
        togglePasswordBtn.addEventListener("click", function () {
            if (password.type === "password") {
                password.type = "text";
                togglePasswordBtn.textContent = "Hide";
                togglePasswordBtn.setAttribute("aria-label", "Hide password");
            } else {
                password.type = "password";
                togglePasswordBtn.textContent = "Show";
                togglePasswordBtn.setAttribute("aria-label", "Show password");
            }
        });
    }

    if (toggleConfirmPasswordBtn && confirmPassword) {
        toggleConfirmPasswordBtn.addEventListener("click", function () {
            if (confirmPassword.type === "password") {
                confirmPassword.type = "text";
                toggleConfirmPasswordBtn.textContent = "Hide";
                toggleConfirmPasswordBtn.setAttribute("aria-label", "Hide confirm password");
            } else {
                confirmPassword.type = "password";
                toggleConfirmPasswordBtn.textContent = "Show";
                toggleConfirmPasswordBtn.setAttribute("aria-label", "Show confirm password");
            }
        });
    }


    /* ======================================================================
       INTERMEDIATE EXTENSION: Real-Time Event Handlers (Input, Keyup, Change, Blur)
       ====================================================================== */

    // Full Name: validate on input and blur
    studentName.addEventListener("input", validateName);
    studentName.addEventListener("blur", validateName);

    // Email: validate on input and blur
    email.addEventListener("input", validateEmail);
    email.addEventListener("blur", validateEmail);

    // Mobile: auto-strip non-digits & validate
    mobile.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "");
        if (this.value.length === 10) {
            validateMobile();
        }
    });
    mobile.addEventListener("blur", validateMobile);

    // Password: real-time strength meter and criteria update
    password.addEventListener("input", function () {
        checkPasswordStrength(this.value);
        if (this.value.length >= 8) {
            validatePassword();
        }
        if (confirmPassword.value.length > 0) {
            validateConfirmPassword();
        }
    });
    password.addEventListener("blur", validatePassword);

    // Confirm Password
    confirmPassword.addEventListener("input", validateConfirmPassword);
    confirmPassword.addEventListener("blur", validateConfirmPassword);

    // Dropdowns
    course.addEventListener("change", validateCourse);
    year.addEventListener("change", validateYear);

    // Radios
    genderRadios.forEach(function (radio) {
        radio.addEventListener("change", validateGender);
    });

    // Terms Checkbox
    terms.addEventListener("change", validateTerms);

    // CAPTCHA Input
    captchaInput.addEventListener("input", function () {
        if (this.value.length >= 6) {
            validateCaptcha();
        }
    });
    captchaInput.addEventListener("blur", validateCaptcha);


    /* ======================================================================
       FORM SUBMISSION HANDLER
       ====================================================================== */

    registrationForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Stop default form submit to validate first

        // Validate all fields
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isMobileValid = validateMobile();
        const isPasswordValid = validatePassword();
        const isConfirmValid = validateConfirmPassword();
        const isCourseValid = validateCourse();
        const isYearValid = validateYear();
        const isGenderValid = validateGender();
        const isTermsValid = validateTerms();
        const isCaptchaValid = validateCaptcha();

        const isFormValid =
            isNameValid &&
            isEmailValid &&
            isMobileValid &&
            isPasswordValid &&
            isConfirmValid &&
            isCourseValid &&
            isYearValid &&
            isGenderValid &&
            isTermsValid &&
            isCaptchaValid;

        if (!isFormValid) {
            // Find and focus first invalid field for keyboard & accessibility
            const invalidFields = [
                { isValid: isNameValid, element: studentName },
                { isValid: isEmailValid, element: email },
                { isValid: isMobileValid, element: mobile },
                { isValid: isPasswordValid, element: password },
                { isValid: isConfirmValid, element: confirmPassword },
                { isValid: isCourseValid, element: course },
                { isValid: isYearValid, element: year },
                { isValid: isGenderValid, element: document.getElementById("genderMale") },
                { isValid: isTermsValid, element: terms },
                { isValid: isCaptchaValid, element: captchaInput }
            ];

            const firstInvalid = invalidFields.find(function (f) {
                return !f.isValid;
            });

            if (firstInvalid && firstInvalid.element) {
                firstInvalid.element.focus();
            }

            if (formErrorMessage) {
                formErrorMessage.textContent = "⚠️ Please review the form and resolve all highlighted errors before submitting.";
                formErrorMessage.style.display = "block";
            }
            if (formSuccessMessage) {
                formSuccessMessage.style.display = "none";
            }

            return;
        }

        // If form is valid:
        if (formErrorMessage) {
            formErrorMessage.style.display = "none";
        }

        if (window.location.protocol.startsWith("http")) {
            if (formSuccessMessage) {
                formSuccessMessage.innerHTML = "<strong>✅ Validation Passed!</strong> Submitting registration data to PHP server...";
                formSuccessMessage.style.display = "block";
            }
            // Submit form to PHP backend (Practical 7)
            setTimeout(function () {
                registrationForm.submit();
            }, 400);
        } else {
            // Static file preview
            if (formSuccessMessage) {
                formSuccessMessage.innerHTML = "<strong>✅ Validation Passed!</strong> All inputs are valid. Run via PHP server (XAMPP / WAMP / <code>php -S localhost:8000</code>) to store records into <code>data/registrations.csv</code>.";
                formSuccessMessage.style.display = "block";
                formSuccessMessage.scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        }
    });


    /* ======================================================================
       FORM RESET HANDLER
       ====================================================================== */

    const resetBtn = document.getElementById("resetBtn");

    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            // Clear all field error/success classes and messages
            clearFieldState(studentName, nameError);
            clearFieldState(email, emailError);
            clearFieldState(mobile, mobileError);
            clearFieldState(password, passwordError);
            clearFieldState(confirmPassword, confirmPasswordError);
            clearFieldState(course, courseError);
            clearFieldState(year, yearError);
            clearFieldState(terms, termsError);
            clearFieldState(captchaInput, captchaError);

            if (genderFieldset) genderFieldset.classList.remove("fieldset-error");
            if (genderError) {
                genderError.textContent = "";
                genderError.classList.remove("visible");
            }

            // Reset password strength bar and checklist
            if (strengthBarFill) {
                strengthBarFill.style.width = "0%";
                strengthBarFill.style.backgroundColor = "transparent";
            }
            if (strengthText) {
                strengthText.textContent = "None";
                strengthText.style.color = "var(--text-color)";
            }

            [ruleLength, ruleUpper, ruleLower, ruleNumber, ruleSpecial].forEach(function (el) {
                updatePasswordCriterion(el, false);
            });

            // Hide global alerts
            if (formSuccessMessage) formSuccessMessage.style.display = "none";
            if (formErrorMessage) formErrorMessage.style.display = "none";

            // Regenerate CAPTCHA
            setTimeout(function () {
                generateCaptcha();
            }, 50);
        });
    }

}