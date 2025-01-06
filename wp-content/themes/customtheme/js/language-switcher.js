function load_tlib() {
    if (!window.gt_translate_script) {
        window.gt_translate_script = document.createElement('script');
        window.gt_translate_script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2';
        document.body.appendChild(window.gt_translate_script);
    }
}

// Initialize the Google Translate element
function googleTranslateElementInit2() {
    new google.translate.TranslateElement({
        pageLanguage: 'en', // Default language of the page
        includedLanguages: 'en,uk', // Languages available for translation
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
}

document.getElementById('ua-button').addEventListener('click', switchLanguage);
document.getElementById('en-button').addEventListener('click', switchLanguage);

function switchLanguage(evt) {
    const buttonSide = evt.target.closest('.btn__switch__side');
    if (!buttonSide) return; // Ignore clicks outside of the language buttons

    evt.preventDefault(); // Prevent default behavior

    const selectedLang = buttonSide.getAttribute('data-lang');
    if (selectedLang) {
        // Remove 'active' class from all buttons
        document.querySelectorAll('.btn__switch__side').forEach(function (side) {
            side.classList.remove('active');
        });

        buttonSide.classList.add('active');
        load_tlib();

        setTimeout(function () {
            changeLanguage(selectedLang); // Change language after the delay
        }, 500); // Slight delay to ensure the script is loaded
    }
}

// Button click event handler
document.querySelector('.btn__switch').addEventListener('click', function (evt) {
    evt.preventDefault(); // Prevent default action

    const selectedLang = evt.target.closest('.btn__switch__side').getAttribute('data-lang');

    if (selectedLang) {
        // Toggle the active class to highlight the selected language
        document.querySelectorAll('.btn__switch__side').forEach(function (side) {
            side.classList.remove('active');
        });

        evt.target.closest('.btn__switch__side').classList.add('active');

        // Load the translation script if it's not already loaded
        load_tlib();

        // Change language once the script is loaded
        setTimeout(function () {
            changeLanguage(selectedLang); // Change the language based on the selected option
        }, 500); // Delay slightly to ensure the script is loaded before triggering translation
    }
});

// Function to change the language by simulating a click on the GTranslate link
function changeLanguage(lang) {
    const languageLinks = document.querySelectorAll('a[data-gt-lang]');

    const langLink = Array.from(languageLinks).find(function (link) {
        return link.getAttribute('data-gt-lang') === lang;
    });

    if (langLink) {
        if (langLink.style.display === 'none' || window.getComputedStyle(langLink).display === 'none') {
            console.warn('The language link is hidden. Consider making it visible or directly invoking the language change logic.');
        } else {
            langLink.click(); // Simulate the click on the GTranslate language link
        }
    }
}