document.addEventListener('DOMContentLoaded', function(){
  // Set year in footer
  const yearEl = document.getElementById('year');
  if(yearEl) yearEl.textContent = new Date().getFullYear();

  // Gallery: open modal and set image src when a thumb is clicked
  const galleryItems = document.querySelectorAll('.gallery-item');
  const galleryModal = document.getElementById('galleryModal');
  const galleryModalImg = document.getElementById('galleryModalImg');
  if(galleryItems && galleryModal && galleryModalImg){
    galleryItems.forEach(item=>{
      item.addEventListener('click', function(e){
        e.preventDefault();
        const src = item.getAttribute('data-src');
        galleryModalImg.src = src;
        const modal = new bootstrap.Modal(galleryModal);
        modal.show();
      });
    });
  }

  // Contact form basic client-side validation
  const contactForm = document.getElementById('contactForm');
  if(contactForm){
    contactForm.addEventListener('submit', function(e){
      if(!contactForm.checkValidity()){
        e.preventDefault();
        e.stopPropagation();
        contactForm.classList.add('was-validated');
      } else {
        e.preventDefault();
        // Aquí podrías enviar por fetch a un endpoint; por ahora mostramos mensaje
        alert('Gracias — tu mensaje ha sido enviado (simulado).');
        contactForm.reset();
        contactForm.classList.remove('was-validated');
      }
    });
  }

});
