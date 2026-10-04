document.addEventListener('DOMContentLoaded', function () {
    const elements = document.querySelectorAll('.home-reveal');

    if (!('IntersectionObserver' in window)) {
        elements.forEach(function (element) {
            element.classList.add('is-visible');
        });
        return;
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12 });

    elements.forEach(function (element) {
        observer.observe(element);
    });
});
