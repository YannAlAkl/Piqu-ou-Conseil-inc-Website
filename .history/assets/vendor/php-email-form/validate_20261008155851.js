(function () {
  "use strict";
  document.querySelectorAll('.php-email-form').forEach(function(thisForm) {
    thisForm.addEventListener('submit', function(event) {
      event.preventDefault();

      let action = thisForm.getAttribute('action');
      if (!action) {
        alert('L\'attribut action du formulaire n\'est pas configuré !');
        return;
      }
      
      // Gestion visuelle des messages du template
      if(thisForm.querySelector('.loading')) thisForm.querySelector('.loading').classList.add('d-block');
      if(thisForm.querySelector('.error-message')) thisForm.querySelector('.error-message').classList.remove('d-block');
      if(thisForm.querySelector('.sent-message')) thisForm.querySelector('.sent-message').classList.remove('d-block');

      fetch(action, {
        method: 'POST',
        body: new FormData(thisForm),
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(response => {
        if (response.ok) return response.text();
        throw new Error(response.status + ' ' + response.statusText);
      })
      .then(data => {
        if(thisForm.querySelector('.loading')) thisForm.querySelector('.loading').classList.remove('d-block');
        if (data.trim() === 'OK') {
          if(thisForm.querySelector('.sent-message')) thisForm.querySelector('.sent-message').classList.add('d-block');
          thisForm.reset(); 
        } else {
          throw new Error(data ? data : 'La soumission a échoué.'); 
        }
      })
      .catch((error) => {
        if(thisForm.querySelector('.loading')) thisForm.querySelector('.loading').classList.remove('d-block');
        if(thisForm.querySelector('.error-message')) {
          thisForm.querySelector('.error-message').innerHTML = error;
          thisForm.querySelector('.error-message').classList.add('d-block');
        }
      });
    });
  });
})();
