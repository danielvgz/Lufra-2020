// Bootstrap basic theme JS (example)
document.addEventListener('DOMContentLoaded', function(){
    console.log('Bootstrap basic theme loaded');
    // Smooth scroll for internal anchors
    document.querySelectorAll('a[href^="#"]').forEach(function(a){
        a.addEventListener('click', function(e){
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
