const ShowFormBtn = document.getElementById('ShowFormBtn')
const CloseFormBtn = document.getElementById('CloseFormBtn')
const FormWrapper = document.getElementById('FormWrapper')
const SubmitDomainBtn = document.getElementById('SubmitDomain')

ShowFormBtn.addEventListener('click', () => {
    FormWrapper.style.display = FormWrapper.style.display === 'none' ? 'block' : 'none';
})

CloseFormBtn.addEventListener('click', () => {
    FormWrapper.style.display = FormWrapper.style.display === 'none' ? 'block' : 'none';
})

function checkDomain(domainValue) {
    const domainIp = document.getElementById('domainIp');
    const domainStatus = document.getElementById('domainStatus');

    fetch(`https://dns.google/resolve?name=${domainValue}&type=A`)
        .then(response => response.json())
        .then(data => {
            console.log('Réponse DNS :', data);

            if (data.Status === 0 && data.Answer) {
                const ip = data.Answer[0].data;
                domainIp.style.display = 'flex'
                domainStatus.style.display = 'flex'

                const domainStatus_Span = domainStatus.querySelector('span');
                const domainStatus_P = domainStatus.querySelector('p');
                domainStatus_Span.style.backgroundColor = "#68BD5D"
                domainStatus_P.innerHTML = 'Le domaine est existant'

                const domainIp_Span = domainIp.querySelector('span');
                const domainIp_P = domainIp.querySelector('p');
                if (ip === '37.187.38.166') {
                    domainIp_Span.style.backgroundColor = "#68BD5D"
                    domainIp_P.innerHTML = `IP de redirection du domaine : ${ip}`
                } else {
                    domainIp_Span.style.backgroundColor = "#C03B38"
                    domainIp_P.innerHTML = `Mauvaise IP de redirection du domaine : ${ip} ( ip == 37.187.38.166 )`
                    return (502)
                }

                return (200)


            } else {
                console.log('Domaine invalide ou pas d\'enregistrement A.');
            }
        })
        .catch(err => console.error(err));
}

SubmitDomainBtn.addEventListener('click', () => {
    const domainValue = document.getElementById('domain').value;
    const domainValueClean = domainValue.replace(/\./g, '-')
    const apacheStatus = document.getElementById('apacheStatus');

    checkDomain(domainValue)
    console.log(checkDomain(domainValue))
    if (checkDomain(domainValue) === 200) {
        fetch('../api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'add_domain',
                domain: domainValueClean
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success === true && checkDomain(domainValue) === 200) {
                    apacheStatus.style.display = 'flex'

                    const apacheStatus_Span = apacheStatus.querySelector('span');
                    const apacheStatus_P = apacheStatus.querySelector('p');
                    apacheStatus_Span.style.backgroundColor = "#68BD5D"
                    apacheStatus_P.innerHTML = 'Domaine configuré avec succès'


                } else if (checkDomain(domainValue) >= 500 && checkDomain(domainValue) < 600) {

                    const apacheStatus_Span = apacheStatus.querySelector('span');
                    const apacheStatus_P = apacheStatus.querySelector('p');
                    apacheStatus_Span.style.backgroundColor = "#C03B38"
                    apacheStatus_P.innerHTML = 'Configuration du domaine avec Apache non exécuté mauvaise IP'

                } else {
                    apacheStatus.style.display = 'flex'

                    const apacheStatus_Span = apacheStatus.querySelector('span');
                    const apacheStatus_P = apacheStatus.querySelector('p');
                    apacheStatus_Span.style.backgroundColor = "#C03B38"
                    apacheStatus_P.innerHTML = 'Erreur lors de la configuration du domaine avec Apache'
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'envoi :', error);
            });
    } else if (checkDomain(domainValue) >= 500 && checkDomain(domainValue) < 600) {
        const apacheStatus_Span = apacheStatus.querySelector('span');
        const apacheStatus_P = apacheStatus.querySelector('p');
        apacheStatus_Span.style.backgroundColor = "#C03B38"
        apacheStatus_P.innerHTML = 'Configuration du domaine avec Apache non exécuté mauvaise IP'
    }

})
