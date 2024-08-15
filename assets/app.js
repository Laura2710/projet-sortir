import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import {CampusService} from "./js/CampusService.js";
import LieuMap from "./js/LieuMap.js";

if (window.location.pathname.includes('sortie/detail')) {
    LieuMap.getLieu();
}
if (window.location.pathname.includes('admin/campus')) {
    CampusService.addCampus();
    CampusService.editCampus();
}
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
