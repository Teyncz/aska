let index = 0;
const slider = document.getElementById('SliderContainer');
const totalSlides = slider.children.length;

function nextSlide() {
    index = (index + 1) % totalSlides;
    slider.style.transform = `translateX(-${index * 100}vw)`;
}

setInterval(nextSlide, 5000); 