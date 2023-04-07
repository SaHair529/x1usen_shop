import Renderer from "./Renderer"
import AttributesNaming from "./HTMLAttributesNaming";

export default class ResponseHandler {
    static handleDecreaseCartItemQuantityResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['quantity'] === 0)
                        Renderer.disableDecreaseButton()
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
                        Renderer.replaceToCartButtonWithCounter(responseData['quantity'])
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
                    }
                })
                break
        }
        productInfoModal.classList.remove('hidden')
    }
}