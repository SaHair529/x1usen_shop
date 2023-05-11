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
                else if (e.target.classList.contains(HTMLElements.decreaseCartItemButton.class)) {
                    const productId = productInfo.dataset.detailId
                    ProductController.decreaseCartItemQuantity(productId).then(response => {
                        ResponseHandler.handleDecreaseCartItemQuantityResponse(response)
                    })
                }
            })
        }
    }
    // ___________________________________

    // actions ---------------------------
    static decreaseCartItemQuantity(productId) {
        return fetch(`/cart/decrease_quantity?product_id=${productId}`)
    }

    static addToCart(productId) {
        return fetch(`/cart/add_item?item_id=${productId}`)
    }
    // ___________________________________
}