let index = 0;
const slider = document.getElementById('SliderContainer');
const totalSlides = slider.children.length;
const checkbox = document.getElementById('checkbox');
const sendFormBtn = document.getElementById('sendForm');
const FormResponse = document.getElementById('FormResponse')

function nextSlide() {
    index = (index + 1) % totalSlides;
    slider.style.transform = `translateX(-${index * 100}vw)`;
}

setInterval(nextSlide, 5000);


checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
        sendFormBtn.disabled = false;
    } else {
        sendFormBtn.disabled = true;
    }
});

sendFormBtn.addEventListener('click', (e) => {
    e.preventDefault();

    let formData = new FormData();
    formData.append("lastname", document.querySelector("#lastname").value.trim())
    formData.append("firstname", document.querySelector("#firstname").value.trim())
    formData.append("email", document.querySelector("#email").value.trim())
    formData.append("phone", document.querySelector("#phone").value.trim())
    formData.append("message", document.querySelector("#message").value.trim())

    fetch('mail.php', {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            FormResponse.innerHTML = data.message
        })

})