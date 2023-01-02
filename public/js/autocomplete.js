const input = document.querySelector("input#distributor");
const autocom = document.querySelector("#autocom");
input.addEventListener('input', e => {
    const value = e.target.value;
    if(value.length > 1) autoComplete(value);
})



function autoComplete(text) {
    fetch(`/autocomplete?name=${text}`)
        .then(function (res) { return res.json(); })
        .then(function (data) { 
            autocom.innerHTML = "";
            autocom.classList.remove("d-none")
            data.forEach(el => {
                const li = document.createElement('li');
                li.onclick = listClick;
                li.textContent = checkValue(el, text);
                autocom.appendChild(li);
            });
         })
        .catch(function (error) { return console.error("An error occurred", error); });
}

function checkValue(el, text) {
    if (el.id == text) return text;
    if (el.username.includes(text)) return el.username;
    if (el.first_name.includes(text)) return el.first_name;
    if (el.last_name.includes(text)) return el.last_name;
}

function listClick(e) {
    input.value = e.target.textContent;
    autocom.classList.add("d-none")
}