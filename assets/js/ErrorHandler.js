export class ErrorHandler {
    static deleteErrors() {
        let errors = document.getElementsByClassName('error');
        while (errors.length > 0) {
            errors[0].remove();
        }
    }

    static displayErrorCampus(message) {
        let div = document.createElement('div');
        div.classList.add('error');
        div.innerText = message;
        let tableCampus = document.getElementsByClassName("table-campus");
        tableCampus[0].insertAdjacentElement('afterend', div);
    }
}
