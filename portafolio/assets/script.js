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

// Puedes agregar interactividad con JavaScript aquí
document.getElementById('contact-form').addEventListener('submit', function(event) {
    event.preventDefault();
    alert('Gracias por tu mensaje. Te contactaré pronto.');
    // Aquí podrías agregar código para enviar el formulario a un servidor
});
