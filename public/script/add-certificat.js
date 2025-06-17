import { createCertificatRow } from './components/cartficatRow.js';

const ShowFormBtn = document.getElementById('ShowFormBtn')
const CloseFormBtn = document.getElementById('CloseFormBtn')
const FormWrapper = document.getElementById('FormWrapper')
const SubmitDomainBtn = document.getElementById('SubmitDomain')

const certificatStatus = document.getElementById('certificatStatus')
const responseSpan = certificatStatus.querySelector('span')

ShowFormBtn.addEventListener('click', () => {
    FormWrapper.style.display = FormWrapper.style.display === 'none' ? 'block' : 'none';
})

CloseFormBtn.addEventListener('click', () => {
    FormWrapper.style.display = FormWrapper.style.display === 'none' ? 'block' : 'none';
})

SubmitDomainBtn.addEventListener('click', async () => {
    const domain = document.getElementById('domain').value;
    const email = document.getElementById('email').value;
    try {
        certificatStatus.style.display = "flex"
        certificatStatus.innerHTML = "Demande de certificat envoyé, en attente de Let's Encrypt"
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'generate_cert',
                domain: domain,
                email: email
            })
        });
        const data = await response.json();

        if (data.status === 1) {
            try {
                const get_cert_response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'get_cert',
                        domain: domain,
                    })
                });
                const cert_data = await get_cert_response.json();

                if (cert_data.success === true) {
                    const tbody = document.getElementById('certificatsList')
                    tbody.appendChild(createCertificatRow(domain, cert_data.not_before, cert_data.not_after, cert_data.issuer, cert_data.valid))
                    certificatStatus.style.display = "flex"
                    certificatStatus.innerHTML = data.message
                    responseSpan.style.backgroundColor = "#68BD5D"
                }

            } catch (err) {
                console.error('Erreur lors de la requête DNS:', err);
                return 500;
            }
        } else {
            certificatStatus.style.display = "flex"
            certificatStatus.innerHTML = data.message
            certificatStatus.querySelector('span')
            responseSpan.style.backgroundColor = "#C03B38"
        }
    } catch (err) {
        console.error('Erreur lors de la requête DNS:', err);
        return 500;
    }
})

async function deleteCertificat(domain) {
    const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'delete-cert',
            domain: domain,
        })
    })

    return response

}

document.addEventListener('click', async (e) => {
    const showBtn = e.target.closest('.ShowOptionsBtn');
    const deleteBtn = e.target.closest('.deleteCertificatBtn');

    if (showBtn) {
        e.preventDefault();
        e.stopPropagation();

        const parent = showBtn.parentElement;
        const popup = parent.querySelector('.DomainOptionPopUp');

        document.querySelectorAll('.DomainOptionPopUp').forEach(p => {
            if (p !== popup) p.style.display = 'none';
        });

        popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';

        return;
    }

    if (deleteBtn) {
        e.preventDefault();

        const row = deleteBtn.closest('tr');
        const domain = row.querySelector('th').textContent.trim();

        try {
            const response = await deleteCertificat(domain);
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    const tr = deleteBtn.closest('tr');
                    if (tr) tr.remove();
                } else {
                    console.error('Erreur côté serveur :', data.message);
                }
            } else {
                console.error('Erreur HTTP :', response.status);
            }
        } catch (err) {
            console.error('Erreur fetch :', err);
        }

        return;
    }

    document.querySelectorAll('.DomainOptionPopUp').forEach(popup => {
        popup.style.display = 'none';
    });
});