import {HttpRequest} from "./HttpRequest.js";

export default class LieuMap {
    static getLieu() {
        let latitude = document.getElementById('latitude').innerText;
        let longitude = document.getElementById('longitude').innerText;

        if (latitude !== 'non renseignée' && longitude !== 'non renseignée') {
            let api = "https://api-adresse.data.gouv.fr/reverse/?lon="+longitude+"&lat="+latitude;
            HttpRequest.get(api)
                .then(data => {
                    let gps = document.getElementById('gps');
                    let mapLeaflet = document.createElement('div');
                    mapLeaflet.id = 'map';
                    gps.appendChild(mapLeaflet);
                    this.generateMap(latitude, longitude)
                })
                .catch(err => console.log(err));
        }
    }

    static generateMap(latitude, longitude) {
        let map = L.map('map').setView([latitude, longitude], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([latitude, longitude]).addTo(map)
            .bindPopup('On est là!')
            .openPopup();
    }
}