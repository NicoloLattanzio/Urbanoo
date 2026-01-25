// Oggetto con regex e messaggi per i campi
const fieldValidators = {
    name: {
        regex: /^[^\d]{2,25}$/,  // 2-25 chars, no numbers
        message: "<li>Il nome non deve contenere numeri e avere tra 2 e 25 caratteri.</li>"
    },
    surname: {
        regex: /^[^\d]{2,25}$/,  // 2-25 chars, no numbers
        message: "<li>Il cognome non deve contenere numeri e avere tra 2 e 25 caratteri.</li>"
    },
    email: {
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
        message: "<li>Inserisci un'email valida.</li>"
    },
    phone: {
        regex: /^\+?[0-9 ]{8,15}$/,  // international number, 8-15 digits
        message: "<li>Inserisci un numero di cellulare valido.</li>"
    },
    password: {
        regex: /^.{6,}$/,  // at least 6 characters
        message: "<li>La password deve contenere almeno 6 caratteri.</li>"
    },
    description: {
        regex: /^[\s\S]{10,250}$/,  // 10-250 characters
        message: "<li>La descrizione deve essere composta da almeno 10 caratteri e non più di 250</li>"
    },
    price: {
        regex: /^\d+(\.\d{1,2})?$/,  // positive number with up to 2 decimal places
        message: "<li>Il prezzo deve essere un numero valido (decimali con massimo 2 cifre) maggiore di 0</li>"
    },
    size: {
        regex: /^[1-9]\d*$/,  // positive integer
        message: "<li>La superficie deve essere un numero intero maggiore di 0</li>"
    },
    rooms: {
        regex: /^[1-9]\d*$/,  // positive integer
        message: "<li>Il numero di locali deve essere un numero intero maggiore di 0</li>"
    },
    address: {
        regex: /^[\s\S]{5,30}$/,  // 5-30 characters
        message: "<li>L\'indirizzo deve essere composto da almeno 5 caratteri e non più di 30</li>"
    },
    city: {
        regex: /^[\s\S]{2,20}$/,  // 2-20 characters, letters and spaces
        message: "<li>La città deve essere composta da almeno 2 caratteri e non più di 20</li>"
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
    const requiredId = errorIds[0];
    const invalidId = errorIds[1];

    const errorRequiredSpan = document.getElementById(requiredId);
    if (!errorRequiredSpan) return false;
    // client side error reset
    errorRequiredSpan.textContent = "";
    errorRequiredSpan.classList.add("none");
    // required validation
    if (field.hasAttribute("required") && value === "") {
        showError(requiredId, field.dataset.msgRequired);
        errorRequiredSpan.classList.remove("none");
        isValid = false;
    }

    const errorInvalidSpan = document.getElementById(invalidId);
    if (!errorInvalidSpan) return false;
    // client side error reset
    errorInvalidSpan.textContent = "";
    errorInvalidSpan.classList.add("none");

    // input validation
    let errMsg = field.dataset.msgInvalid+"<ul>";
    // value = spaces
    if (value && !value.trim()){
        errMsg += "<li>Non puoi inserire soli spazi.</li>";
        errorInvalidSpan.classList.remove("none");
        isValid = false;
    };
    // regex validation
    const validator = fieldValidators[fieldId];
    if (validator?.regex && value !== "" &&!validator.regex.test(value)) {
        errMsg += validator.message;
        errorInvalidSpan.classList.remove("none");
        isValid = false;
    }
    errMsg += "</ul>";
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

form.addEventListener("submit", function (e) {
    clearError(); // reset messaggi

    const isValid = validateForm(form);

    if (!isValid) {
        e.preventDefault(); // blocca submit
        focusFirstError(form);
    }
});
