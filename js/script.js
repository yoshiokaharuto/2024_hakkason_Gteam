"use strict";

// ハンバーガーメニュー
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

function responsiveSubHeader() { // 画面の横幅によって表示状態のハンバーガーメニュー(サブヘッダー)を非表示に
    if (window.innerWidth >= 600 && subHeader.style.display === 'block') {
        subHeader.style.display = 'none';
        subHeaderCheckbox.checked = false;
        subHeaderButton.textContent = 'menu';
    }
}

window.onload = () => {
    isSubHeaderCheckboxChecked();
    responsiveSubHeader();
};

subHeaderCheckbox.addEventListener('change', isSubHeaderCheckboxChecked);
window.addEventListener('resize', responsiveSubHeader);

// 未ログインかつ画面の横幅が狭い状態で、ヘッダーの新規投稿ボタンを押したときのメッセージ
document.addEventListener('DOMContentLoaded', function() {
    const cantClickElements = document.querySelectorAll('.cant-click');

    cantClickElements.forEach(element => {
        element.addEventListener('click', function() {
            if (window.innerWidth <= 600) {
                alert('レシピを投稿するにはログインが必要です');
            }
        });
    });
});

// detail.phpのコピーボタン
function copyButton(elementId) {
    var element = document.getElementById(elementId);
    navigator.clipboard.writeText(element.innerText);
}

function copyAllButton() {
    navigator.clipboard.writeText(document.getElementById('recipe-dataToCopy').innerText);
}