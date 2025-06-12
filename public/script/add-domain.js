import { createDomainRow } from './components/domainRow.js';

const ShowFormBtn = document.getElementById('ShowFormBtn')
const CloseFormBtn = document.getElementById('CloseFormBtn')
const FormWrapper = document.getElementById('FormWrapper')
const SubmitDomainBtn = document.getElementById('SubmitDomain')

const deleteDomainBtns = document.querySelectorAll('.deleteDomainBtn')
const ShowOptionsBtn = document.querySelectorAll('.ShowOptionsBtn')

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

const token = getCookie('token');

ShowFormBtn.addEventListener('click', () => {
    FormWrapper.style.display = FormWrapper.style.display === 'none' ? 'block' : 'none';
})

CloseFormBtn.addEventListener('click', () => {
    FormWrapper.style.display = FormWrapper.style.display === 'none' ? 'block' : 'none';
})

async function deleteDomain(domainId,domainName, token) {
    const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            action: 'delete_domain',
            domain: domainName,
            domainId: domainId,
        })
    })

    return response

}

document.addEventListener('click', async (e) => {
    const showBtn = e.target.closest('.ShowOptionsBtn');
    const deleteBtn = e.target.closest('.deleteDomainBtn');

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

        const domainId = Number(deleteBtn.getAttribute('aria-label'));
        const row = deleteBtn.closest('tr');
        const domainName = row.querySelector('th').textContent.trim();

        try {
            const response = await deleteDomain(domainId, domainName, token);
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
async function checkDomain(domainValue) {
    const domainIp = document.getElementById('domainIp');
    const domainStatus = document.getElementById('domainStatus');

    try {
        const response = await fetch(`https://dns.google/resolve?name=${domainValue}&type=A`);
        const data = await response.json();
        console.log('Réponse DNS :', data);

        if (data.Status === 0 && data.Answer) {
            const ip = data.Answer[0].data;

            domainIp.style.display = 'flex';
            domainStatus.style.display = 'flex';

            const domainStatus_Span = domainStatus.querySelector('span');
            const domainStatus_P = domainStatus.querySelector('p');
            domainStatus_Span.style.backgroundColor = "#68BD5D";
            domainStatus_P.innerHTML = 'Le domaine est existant';

            const domainIp_Span = domainIp.querySelector('span');
            const domainIp_P = domainIp.querySelector('p');

            if (ip === '37.187.38.166') {
                domainIp_Span.style.backgroundColor = "#68BD5D";
                domainIp_P.innerHTML = `IP de redirection du domaine : ${ip}`;
                return 200;
            } else {
                domainIp_Span.style.backgroundColor = "#C03B38";
                domainIp_P.innerHTML = `Mauvaise IP de redirection du domaine : ${ip} (ip attendu : 37.187.38.166)`;
                return 502;
            }
        } else {
            console.log('Domaine invalide ou pas d\'enregistrement A.');
            return 404;
        }
    } catch (err) {
        console.error('Erreur lors de la requête DNS:', err);
        return 500;
    }
}

SubmitDomainBtn.addEventListener('click', () => {
    const domainValue = document.getElementById('domain').value;
    const domainValueClean = domainValue.replace(/\./g, '-')
    const apacheStatus = document.getElementById('apacheStatus');

    checkDomain(domainValue).then(status => {
        if (status === 200) {
            fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_domain',
                    domain: domainValueClean,
                    domainRaw: domainValue,
                    redirection: '37.187.38.166',
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success === true && data.id > 0 && data.status === 1) {
                        apacheStatus.style.display = 'flex'

                        const apacheStatus_Span = apacheStatus.querySelector('span');
                        const apacheStatus_P = apacheStatus.querySelector('p');
                        apacheStatus_Span.style.backgroundColor = "#68BD5D"
                        apacheStatus_P.innerHTML = 'Domaine configuré avec succès'

                        console.log(data.id)
                        const domain =
                            { id: data.id, name: domainValue, redirection: '37.187.38.166' }

                        const tbody = document.getElementById('domainList')
                        tbody.appendChild(createDomainRow(domain))

                    } else {
                        apacheStatus.style.display = 'flex'

                        const apacheStatus_Span = apacheStatus.querySelector('span');
                        const apacheStatus_P = apacheStatus.querySelector('p');
                        apacheStatus_Span.style.backgroundColor = "#C03B38"
                        apacheStatus_P.innerHTML = data.message
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de l\'envoi :', error);
                });
        } else if (status === 502) {
            const apacheStatus_Span = apacheStatus.querySelector('span');
            const apacheStatus_P = apacheStatus.querySelector('p');
            apacheStatus_Span.style.backgroundColor = "#C03B38"
            apacheStatus_P.innerHTML = 'Configuration du domaine avec Apache non exécuté mauvaise IP'
        }
    });
})
