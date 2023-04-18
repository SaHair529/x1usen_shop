import Renderer from "./Renderer"
import AttributesNaming from "./HTMLAttributesNaming";

export default class ResponseHandler {
    static handleDecreaseCartItemQuantityResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['quantity'] === 0) {
                        Renderer.replaceCounterWithToCartButton()
                        Renderer.updateSumPrice(0)
                    }

                    if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS)) {
                        Renderer.updateCounterValue(responseData['quantity'])
                        Renderer.updateSumPrice(responseData['quantity'] * responseData['product_price'])
                    }

                    const increaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)
                    if (increaseBtn.getAttribute('disabled') !== null)
                        Renderer.enableIncreaseButton()
                })
                break
            case 422:
                break
        }
    }

    static handleAddToCartResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        Renderer.updateSumPrice(responseData['product_price'] * responseData['quantity'])
                        if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS)) {
                            Renderer.updateCounterValue(responseData['quantity'])
                        }
                        else
                            Renderer.replaceToCartButtonWithCounter(responseData['quantity'])
                        if (!responseData['has_more_product'])
                            Renderer.disableIncreaseButton()
                    }
                })
                break
            case 403:
                responsePromise.text().then(responseText => {
                    if (responseText === 'not authorized') {
                        alert('Требуется авторизация')
                    }
                })
                break
        }
    }

    static handleShowProductModalResponse(responsePromise, productInfo) {
        const productInfoModal = document.getElementById(AttributesNaming.MODALS.PRODUCT_MODAL.ID)
        productInfoModal.dataset.productId = productInfo['id']
        productInfoModal.querySelector('.name').textContent = productInfo['name']
        productInfoModal.querySelector('.price').textContent = '0'

        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['quantity'] > 0) {
                        Renderer.replaceToCartButtonWithCounter(responseData['quantity'])
                        productInfoModal.querySelector('.price').innerHTML =
                            'Суммарная стоимость: <span class="sum-price">'+(productInfo['price'] * responseData['quantity'])+'</span>₽'
                        if (!responseData['has_more_product'])
                            Renderer.disableIncreaseButton()
                    }
                    else {
                        if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS))
                            Renderer.replaceCounterWithToCartButton();
                    }
                    productInfoModal.classList.remove('hidden')
                })
                break
            case 422:
                productInfoModal.querySelector('.price').innerHTML =
                    'Суммарная стоимость: <span class="sum-price">0</span>₽'
                productInfoModal.classList.remove('hidden')
                break
            case 403:
                alert('Не авторизован')
        }
    }
}