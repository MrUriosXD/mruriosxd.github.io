// Cargar el idioma seleccionado
let currentLang = 'es'; // Idioma por defecto

// Función para cargar el archivo de idioma
async function loadLanguage(lang) {
    const response = await fetch(`lang/${lang}.json`);
    const translations = await response.json();
    return translations;
}

// Función para actualizar el contenido de la página
async function updateContent(lang) {
    const translations = await loadLanguage(lang);
    document.querySelectorAll('[data-lang]').forEach(element => {
        const key = element.getAttribute('data-lang');
        if (translations[key]) {
            element.textContent = translations[key];
        }
    });
}

// Cambiar idioma al seleccionar una opción
document.getElementById('language-select').addEventListener('change', (event) => {
    currentLang = event.target.value;
    updateContent(currentLang);
});

// Inicializar con el idioma por defecto
updateContent(currentLang);

document.getElementById('contact-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const message = document.getElementById('message').value;

    if (name && email && message) {
        alert(`Gracias ${name}, hemos recibido tu mensaje.`);
        // Aquí podrías enviar el formulario a través de una API o correo electrónico.
        this.reset();
    } else {
        alert('Por favor, completa todos los campos.');
    }
});