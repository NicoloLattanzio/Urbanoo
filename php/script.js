// Oggetto con regex e messaggi per i campi
const fieldValidators = {
    name: {
        regex: /^[^\d]{2,25}$/,  // 2-25 chars, no numbers
        message: "<p>Il nome non deve contenere numeri e avere tra 2 e 25 caratteri.</p>"
    },
    surname: {
        regex: /^[^\d]{2,25}$/,  // 2-25 chars, no numbers
        message: "<p>Il cognome non deve contenere numeri e avere tra 2 e 25 caratteri.</p>"
    },
    email: {
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
        message: "<p>Inserisci un'email valida.</p>"
    },
    phone: {
        regex: /^\+?[0-9 ]{8,15}$/,  // international number, 8-15 digits
        message: "<p>Inserisci un numero di cellulare valido.</p>"
    },
    password: {
        regex: /^.{6,}$/,  // at least 6 characters
        message: "<p>La password deve contenere almeno 6 caratteri.</p>"
    },
    description: {
        regex: /^[\s\S]{10,250}$/,  // 10-250 characters
        message: "<p>La descrizione deve essere composta da almeno 10 caratteri e non più di 250</p>"
    },
    price: {
        regex: /^\d+(\.\d{1,2})?$/,  // positive number with up to 2 decimal places
        message: "<p>Il prezzo deve essere un numero valido (decimali con massimo 2 cifre) maggiore di 0</p>"
    },
    size: {
        regex: /^[1-9]\d*$/,  // positive integer
        message: "<p>La superficie deve essere un numero intero maggiore di 0</p>"
    },
    rooms: {
        regex: /^[1-9]\d*$/,  // positive integer
        message: "<p>Il numero di locali deve essere un numero intero maggiore di 0</p>"
    },
    address: {
        regex: /^[\s\S]{5,30}$/,  // 5-30 characters
        message: "<p>L'indirizzo deve essere composto da almeno 5 caratteri e non più di 30</p>"
    },
    city: {
        regex: /^[\s\S]{2,20}$/,  // 2-20 characters, letters and spaces
        message: "<p>La città deve essere composta da almeno 2 caratteri e non più di 20</p>"
    }
};

function showError(errorId, errorMessage) {
    const errorEl = document.getElementById(errorId);
    if (!errorEl) return;

    errorEl.innerHTML = errorMessage;
    errorEl.classList.add("display-error");
}

function clearError(){
    let serverErrors = document.querySelectorAll("div.error-msg");
    if (!serverErrors) return;
    for (let serverError of serverErrors){
        serverError.innerHTML = "";
    }
    let errors = document.querySelectorAll(".error-msg");
    for(let error of errors){
        error.classList.remove("display-error");
    }
}

function validateField(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return false;

    let isValid = true;
    const value = field.value;
    const errorIds = field.getAttribute("aria-describedby")?.split(" ") || [];
    const [requiredId, invalidId] = errorIds;

    const errorRequiredDiv = document.getElementById(requiredId);
    const errorInvalidDiv = document.getElementById(invalidId);

    if (!errorRequiredDiv || !errorInvalidDiv) return false;

    // client side error reset
    errorRequiredDiv.textContent = "";
    errorInvalidDiv.textContent = "";
    // required validation
    if (field.hasAttribute("required") && value === "") {
        showError(requiredId, field.dataset.msgRequired);
        return false;
    }

    // input validation
    let errMsg = field.dataset.msgInvalid;
    // value = spaces
    if (value && !value.trim()){
        errMsg += "<p>Non puoi inserire soli spazi.</p>";
        isValid = false;    
    };
    
    // regex validation
    const validator = fieldValidators[fieldId];
    if (isValid && validator?.regex && value !== "" && !validator.regex.test(value)) {
        errMsg += validator.message;
        isValid = false;
    }
    if (!isValid) {
        showError(invalidId, errMsg);
    }
    return isValid;
}

function validateForm(form) {
    let isFormValid = true;

    const fields = form.querySelectorAll(
        "input:not([type='submit']):not([type='button']):not([type='reset']):not([type='hidden']), textarea, select"
    );

    fields.forEach(field => {
        const fieldIsValid = validateField(field.id);
        if (!fieldIsValid) {
            isFormValid = false;
        }
    });

    return isFormValid;
}

const form = document.querySelector("form");
if(form!= null){
    form.addEventListener("submit", function (e) {
        clearError(); // resets errors

        const isValid = validateForm(form);

        if (!isValid) {
            e.preventDefault(); // blocks submit
            focusFirstError(form);
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 1;
    showSlides(slideIndex);

    // Next/previous controls
    function plusSlides(n) {
        showSlides(slideIndex += n);

        console.log("Fatto");
    }

    // Thumbnail image controls
    function currentSlide(n) {
        showSlides(slideIndex = n);

        console.log("Fatto");
    }

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("carousel_slide");
        let dots = document.getElementsByClassName("dot");

        if (slides.length === 0) return; // Se non ci sono slides, esci

        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}

        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }

        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }

        slides[slideIndex-1].style.display = "block";

        if (dots.length > 0) {
            dots[slideIndex-1].className += " active";
        }
    }
    // A quanto pare senza sta roba non va nulla
    window.plusSlides = plusSlides;
    window.currentSlide = currentSlide;
});