const parallaxItems = document.querySelectorAll('[data-parallax]');
const updateParallax = () => {
    const scrollY = window.pageYOffset;
    parallaxItems.forEach((el) => {
        const speed = parseFloat(el.dataset.parallax || 0);
        el.style.transform = `translate3d(0, ${scrollY * speed}px, 0)`;
    });
};
window.addEventListener('scroll', updateParallax, { passive: true });
window.addEventListener('load', updateParallax);

const revealItems = document.querySelectorAll('.animate-on-scroll');
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.2 });
revealItems.forEach((item) => revealObserver.observe(item));

const counters = document.querySelectorAll('[data-count]');
const animateCount = (el) => {
    const target = parseFloat(el.dataset.count);
    const duration = 1200;
    const suffix = el.textContent.replace(/[0-9.,]/g, '');
    const startValue = 0;
    const startTime = performance.now();
    const update = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.floor(startValue + (target - startValue) * progress);
        el.textContent = suffix ? `${current}${suffix}` : `${current}`;
        if (progress < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
};
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            animateCount(entry.target);
            counterObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });
counters.forEach((counter) => counterObserver.observe(counter));

const variantButtons = document.querySelectorAll('[data-variant-btn]');
const variantPanels = document.querySelectorAll('[data-variant-panel]');
variantButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
        const variant = btn.dataset.variantBtn;
        variantButtons.forEach((b) => b.classList.toggle('active', b === btn));
        variantPanels.forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.variantPanel === variant);
        });
    });
});

const testimonialButtons = document.querySelectorAll('[data-testimonial-filter]');
const testimonialCards = document.querySelectorAll('[data-testimonial]');
testimonialButtons.forEach((button) => {
    button.addEventListener('click', () => {
        testimonialButtons.forEach((btn) => btn.classList.toggle('active', btn === button));
        const filter = button.dataset.testimonialFilter;
        testimonialCards.forEach((card) => {
            const type = card.dataset.testimonial;
            card.style.display = (filter === 'all' || type === filter) ? 'flex' : 'none';
        });
    });
});

const rippleButtons = document.querySelectorAll('[data-ripple]');
rippleButtons.forEach((btn) => {
    btn.addEventListener('click', (e) => {
        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.classList.add('ripple-effect');
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
        ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});

const timelineButtons = document.querySelectorAll('[data-timeline]');
const progressBar = document.getElementById('timeline-progress');
const timelineLabel = document.getElementById('timeline-label');
const timelineCopy = {
    1: { label: 'Step 01 : Upload photos', width: '10%' },
    2: { label: 'Step 02 : Approve your proof', width: '50%' },
    3: { label: 'Step 03 : Delivery day', width: '100%' }
};
timelineButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.timeline;
        timelineButtons.forEach((b) => b.classList.toggle('active', b === btn));
        progressBar.style.width = timelineCopy[id].width;
        timelineLabel.textContent = timelineCopy[id].label;
    });
});