function ztools_showhide_div(controlElement, divId) {
    divElement = document.getElementById(divId);
    if (controlElement.checked) {
        divElement.style.display = "block";
    } else {
        divElement.style.display = "none";
    }
}
