// Класс обработки страницы детали (/details/item/{article})

import HTMLElements from "./HTMLElements";
import ResponseHandler from "./ResponseHandler";

export default class ProductController {
    static init() {
        this.productInfoPressHandle()
    }

    // handles ---------------------------
    static productInfoPressHandle() {
        const productInfo = document.querySelector('.detail-full-info')
        if (productInfo != null) {
            productInfo.addEventListener('click', function (e) {
                if (e.target.classList.contains(HTMLElements.addToCartButton.class) && !e.target.classList.contains('disabled')) {
                    const productId = productInfo.dataset.detailId
                    ProductController.addToCart(productId).then(response => {
                        ResponseHandler.handleAddToCartResponse(response)
                    })
                }
            })
        }
    }
    // ___________________________________

    // actions ---------------------------
    static addToCart(productId) {
        return fetch(`/cart/add_item?item_id=${productId}`)
    }
    // ___________________________________
}