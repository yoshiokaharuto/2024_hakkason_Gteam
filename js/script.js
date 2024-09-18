"use strict";

const subHeader = document.getElementById('sub-header');
const subHeaderCheckbox = document.getElementById('sub-header-checkbox');
const subHeaderButton = document.getElementById('sub-header-button');

function isSubHeaderCheckboxChecked() {
    if (subHeaderCheckbox.checked) {
        subHeader.style.display = 'block';
        subHeaderButton.textContent = 'close';
    } else {
        subHeader.style.display = 'none';
        subHeaderButton.textContent = 'menu';
    }
}

window.onload = isSubHeaderCheckboxChecked;
subHeaderCheckbox.addEventListener('change', isSubHeaderCheckboxChecked);

function copyButton(elementId) {
    var element = document.getElementById(elementId);
    navigator.clipboard.writeText(element.innerText);
}

function copyAllButton() {
    navigator.clipboard.writeText(document.getElementById('recipe-dataToCopy').innerText);
}