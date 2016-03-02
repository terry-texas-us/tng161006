
$('div.form-login #resetpass').on('click', function () {
    if (this.checked) {
        document.getElementById('resetrow').style.display = '';
    } else {
        document.getElementById('resetrow').style.display = 'none';
    }
});
