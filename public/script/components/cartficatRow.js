export function createCertificatRow(domain,not_before, not_after, issuer, valid ) {
    console.log(domain)
    const tr = document.createElement('tr');
    tr.classList.add('flex');
    tr.setAttribute('aria-label', domain.id);

    const createCell = (tag, content, classes) => {
        const cell = document.createElement(tag);
        cell.className = classes;
        cell.textContent = content ?? 'null';
        return cell;
    };

    tr.appendChild(createCell('th', domain, 'w-[300px] flex justify-start text-[16px] font-[300]'));
    tr.appendChild(createCell('td', not_before, 'w-[175px] flex justify-start text-[16px] font-[300]'));
    tr.appendChild(createCell('td', not_after, 'w-[150px] flex justify-start text-[16px] font-[300]'));
    tr.appendChild(createCell('td', issuer, 'w-[250px] flex justify-start text-[16px] font-[300]'));
    tr.appendChild(createCell('td', valid, 'w-[225px] flex justify-start text-[16px] font-[300]'));

    const tdOptions = document.createElement('td');
    tdOptions.className = 'w-[50px] flex justify-start text-[16px] font-[300] relative';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.ariaLabel = domain;
    btn.className = 'group cursor-pointer ShowOptionsBtn';
    btn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 20 20" fill="none">
            <path class="group-hover:fill-[#C03C3A]" d="M10.0001 4.16664C11.1507 4.16664 12.0834 3.23391 12.0834 2.08332C12.0834 0.932734 11.1507 0 10.0001 0C8.84948 0 7.91675 0.932734 7.91675 2.08332C7.91675 3.23391 8.84948 4.16664 10.0001 4.16664Z" fill="#1E1E1E"/>
            <path class="group-hover:fill-[#C03C3A]" d="M10.0001 12.0833C11.1507 12.0833 12.0834 11.1506 12.0834 9.99999C12.0834 8.84941 11.1507 7.91667 10.0001 7.91667C8.84948 7.91667 7.91675 8.84941 7.91675 9.99999C7.91675 11.1506 8.84948 12.0833 10.0001 12.0833Z" fill="#1E1E1E"/>
            <path class="group-hover:fill-[#C03C3A]" d="M10.0001 20C11.1507 20 12.0834 19.0672 12.0834 17.9167C12.0834 16.7661 11.1507 15.8333 10.0001 15.8333C8.84948 15.8333 7.91675 16.7661 7.91675 17.9167C7.91675 19.0672 8.84948 20 10.0001 20Z" fill="#1E1E1E"/>
        </svg>
    `;

    const popup = document.createElement('div');
    popup.className = 'DomainOptionPopUp bg-[#F4F6F8] w-[135px] px-[20px] py-[10px] absolute top-0 translate-x-[30px] rounded-[4px] flex flex-col gap-[5px] items-start';
    popup.style.display = 'none';

    const editBtn = document.createElement('button');
    editBtn.type = 'button';
    editBtn.ariaLabel = domain;
    editBtn.className = 'text-[16px] hover:underline cursor-pointer';
    editBtn.textContent = 'Modifier';

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.ariaLabel = domain;
    deleteBtn.className = 'deleteDomainBtn text-[#C03C3A] text-[16px] hover:underline cursor-pointer';
    deleteBtn.textContent = 'Supprimer';

    popup.appendChild(editBtn);
    popup.appendChild(deleteBtn);

    tdOptions.appendChild(btn);
    tdOptions.appendChild(popup);
    tr.appendChild(tdOptions);

    return tr;
}
