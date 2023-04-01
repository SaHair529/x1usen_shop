/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import 'bootstrap';
import bsCustomFileInput from "bs-custom-file-input";

// start the Stimulus application
import './bootstrap';

bsCustomFileInput.init();

import ProductController from './ProductController'

document.addEventListener('DOMContentLoaded', function()
{
    const productsWindow = document.getElementById('details-window')
    productsWindow.addEventListener('click', function (e) {
        if (e.target.classList.contains('product-card__actions-add_to_cart')) {
            ProductController.addToCart(e.target.dataset.productId)
        }
    })
})