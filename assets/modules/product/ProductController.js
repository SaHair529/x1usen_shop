// Класс обработки страницы детали (/details/item/{article})

import HTMLElements from "./HTMLElements";

export default class ProductController {
    static init() {
        this.productInfoPressHandle()
    }

    // handles ---------------------------
    static productInfoPressHandle() {
        const productInfo = document.querySelector('.detail-full-info')
        if (productInfo != null) {
            productInfo.addEventListener('click', function (e) {
                if (e.target.classList.contains(HTMLElements.addToCartButton.class)) {

                }
            })
        }
    }
    // ___________________________________
}