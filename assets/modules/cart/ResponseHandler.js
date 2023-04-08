import Renderer from "./Renderer"
import AttributesNaming from "./HTMLAttributesNaming";

export default class ResponseHandler {
    static handleDecreaseCartItemQuantityResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['quantity'] === 0)
                        Renderer.replaceCounterWithToCartButton()

                    if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS))
                        Renderer.updateCounterValue(responseData['quantity'])
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
                        if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS))
                            Renderer.updateCounterValue(responseData['quantity'])
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
        productInfoModal.querySelector('.name').innerHTML = '<b>Наименование: </b>'+productInfo['name']
        productInfoModal.querySelector('.price').innerHTML = '<b>Стоимость: </b>'+productInfo['price']

        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['quantity'] > 0) {
                        Renderer.replaceToCartButtonWithCounter(responseData['quantity'])
                        if (!responseData['has_more_product'])
                            Renderer.disableIncreaseButton()
                    }
                    else {
                        if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS))
                            Renderer.replaceCounterWithToCartButton();
                    }
                })
                break
        }
        productInfoModal.classList.remove('hidden')
    }
}