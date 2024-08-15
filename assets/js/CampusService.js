'use strict';

import {ErrorHandler} from "./ErrorHandler.js";
import {HttpRequest} from "./HttpRequest.js";

export class CampusService {

    static addCampus() {
        let btnAdd = document.getElementById('add-campus');
        btnAdd.addEventListener('click', (e) => {
            e.preventDefault();
            let nomCampus = document.getElementById('nom-campus');
            if (nomCampus.value.trim() !== '') {
                let url = '/projet-sortir/public/admin/campus/creer';
                let data = {nomCampus: nomCampus.value.trim()};
                this.handleCampus(url, data)
            }
        })
    }

    static editCampus() {
        let btnEdit = document.getElementsByClassName('btn-edit-campus');
        for (let i = 0; i < btnEdit.length; i++) {
            btnEdit[i].addEventListener('click', (e) => {
                e.preventDefault();
                let campusId = btnEdit[i].getAttribute('data-id');
                let nomCampus = document.querySelector(`.nom-campus[data-id='${campusId}']`);
                if (nomCampus && nomCampus.innerText.trim() !== '') {
                    let url = '/projet-sortir/public/admin/campus/modifier';
                    let data = {id:campusId, nomCampus: nomCampus.innerText.trim()};
                    this.handleCampus(url, data)
                }
            })
        }
    }


    static handleCampus(chemin, data) {
        ErrorHandler.deleteErrors();
        HttpRequest.post(chemin, data)
            .then(data => {
                if (data.status === 'error') {
                    ErrorHandler.displayErrorCampus(data.message)
                } else {
                    location.reload();
                }
            })
            .catch(err => console.log(err));
    }
}