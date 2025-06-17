const ShowFormBtns = document.querySelectorAll('#ShowFormBtn')
const overlay = document.getElementById('overlay')
const CloseFormBtn = document.querySelectorAll('#CloseFormBtn')
const SaveBtns = document.querySelectorAll('#SaveBtn')

async function updateRGPD(type, content) {
    const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'edit_rgpd',
            content: content,
            type: type,
        })
    })

    return response

}

ShowFormBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        const li = btn.closest('li');
        const contentArea = li.querySelector('.contentArea');

        contentArea.style.display = contentArea.style.display === "flex" ? "none" : "flex"
        overlay.style.opacity = contentArea.style.display === "flex" ? 1 : 0
    });
});

CloseFormBtn.forEach(btn => {
    btn.addEventListener('click', (e) => {
        overlay.style.opacity = 0
        const li = btn.closest('li');
        const contentArea = li.querySelector('.contentArea');

        contentArea.style.display = "none"
    })
});

SaveBtns.forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const type = btn.ariaLabel

        const contentArea = btn.closest('.contentArea');
        const textArea = contentArea.querySelector('textarea')
        const content = textArea.value

        const response = await updateRGPD(type, content);
        const status = contentArea.querySelector('#responseStatus')

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                status.innerHTML = data.message
                status.style.display = "flex"
            } else {
                status.innerHTML = data.message
                status.style.display = "flex"

            }
        } else {
            console.error('Erreur HTTP :', response.status);
        }
    })
})