// 1. Chargement des traductions (fichiers dans assets/locales/)

let translations = {};

async function loadTranslations() {
  translations = {
    fr: await fetch('./assets/locales/fr.json').then(r => r.json()),
    en: await fetch('./assets/locales/en.json').then(r => r.json())
  };
}

// 2. Déterminer la langue (localStorage ou défaut)

let lang = localStorage.getItem('lang') || 'fr';

// 3. Fonction qui injecte les textes sans recharger la page

const translate = () => {
  document.querySelectorAll('[data-i18n]').forEach(el => {
    el.innerHTML = translations[lang][el.dataset.i18n] || el.innerHTML;
  });
};

// 4. Fonction pour switcher (à appeler sur ton bouton)

window.switchLang = () => {
  lang = lang === 'fr' ? 'en' : 'fr';
  localStorage.setItem('lang', lang);
  translate();
};

// 5. Lancer au chargement — await obligatoire

document.addEventListener('DOMContentLoaded', async () => {
  await loadTranslations();
  translate();

  // Initialiser le drapeau affiché dans le bouton principal
  const dropbtn = document.querySelector('.dropbtn img');
  if (dropbtn) {
    dropbtn.src = `https://flagcdn.com/w40/${lang === 'fr' ? 'fr' : 'gb'}.png`;
    dropbtn.alt = lang === 'fr' ? 'Français' : 'English';
  }

  // Ajouter les écouteurs d'événements aux boutons dans le menu
  document.querySelectorAll('.dropdown-content .lang-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const newLang = btn.getAttribute('data-lang');
      lang = newLang;
      localStorage.setItem('lang', lang);
      translate();

      // Mettre à jour le drapeau dans le bouton principal
      const dropbtn = document.querySelector('.dropbtn img');
      if (dropbtn) {
        dropbtn.src = `https://flagcdn.com/w40/${newLang === 'fr' ? 'fr' : 'gb'}.png`;
        dropbtn.alt = newLang === 'fr' ? 'Français' : 'English';
      }
    });
  });
});