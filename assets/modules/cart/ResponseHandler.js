import Renderer from "./Renderer"
import MainRenderer from "../main/Renderer";
import AttributesNaming from "./HTMLAttributesNaming"
import DOMElementsCreator from "./DOMElementsCreator";
import BaseRenderer from "../BaseRenderer";

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
                    else if (responseData['message'] === 'out of stock') {
                        if (!document.querySelector('.out-of-stock-alert'))
                            Renderer.renderOutOfStockAlertOnProductModal()
                        const toCartBtn = document.querySelector('.'+AttributesNaming.BUTTONS.ADD_TO_CART.CLASS)
                        if (toCartBtn != null) {
                            Renderer.shakeElement(toCartBtn)
                            toCartBtn.setAttribute('disabled', '')
                        }
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

    static handleCartItemCardRemoveCartItemResponse(responsePromise, cartItemCard) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        cartItemCard.remove()
                    }
                    const cardItemCards = document.getElementsByClassName(AttributesNaming.cartItemCard.class)
                    if (cardItemCards.length === 0)
                        Renderer.addEmptyCardMessage()
                })
                break
        }
    }

    static handleCartItemCardDecreaseCartItemQuantityResponse(responsePromise, cartItemCard) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        if (responseData['quantity'] > 0) {
                            Renderer.updateCartItemCardData(cartItemCard, responseData)
                            Renderer.enableCartItemCardIncreaseButton(cartItemCard)
                        }
                        else {
                            cartItemCard.remove()
                            const cardItemCards = document.getElementsByClassName(AttributesNaming.cartItemCard.class)
                            if (cardItemCards.length === 0)
                                Renderer.addEmptyCardMessage()
                        }
                    }
                })
                break
        }
    }

    static handleCartItemCardIncreaseCartItemQuantityResponse(responsePromise, cartItemCard) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        Renderer.updateCartItemCardData(cartItemCard, responseData)
                    }
                    else if (responseData['message'] === 'out of stock') {
                        Renderer.shakeElement(cartItemCard.querySelector('.cart-item-card__amount'))
                        Renderer.disableCartItemCardIncreaseButton(cartItemCard)
                    }
                    if (responseData['has_more_product'] === false) {
                        Renderer.disableCartItemCardIncreaseButton(cartItemCard)
                    }
                })
                break
        }
    }

    static handleShowProductModalResponse(responsePromise, productInfo) {
        const productInfoModal = DOMElementsCreator.createDOMElementByObject(AttributesNaming.productModal_forCreator)
        document.querySelector('body').appendChild(productInfoModal)
        productInfoModal.dataset.productId = productInfo['id']
        productInfoModal.querySelector('.name').textContent = productInfo['name']
        productInfoModal.querySelector('.price').textContent = '0'
        productInfoModal.querySelector('.detail-link').setAttribute('href', productInfo.route)
        productInfoModal.dataset.productInfo = JSON.stringify(productInfo)

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
                if (document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS) != null)
                    Renderer.replaceCounterWithToCartButton()
                productInfoModal.classList.remove('hidden')
                break
            case 403:
                productInfoModal.remove()
                const notAuthorizedModal = DOMElementsCreator.createDOMElementByObject(AttributesNaming.notAuthorizedModal_forCreator)
                document.querySelector('body').appendChild(notAuthorizedModal)
        }
    }

    static handleShowUnitAvailableDetailsResponse(responsePromise) {
        responsePromise.text().then(productCardsTemplate => {
            Renderer.renderDetailCardsModal(productCardsTemplate)
            BaseRenderer.removeFullscreenLoader()
        })
    }

    static handleCalculateResponse(resp) {
        resp.json().then(calculateData => {
            switch (resp.status) {
                case 200:
                    Renderer.replaceLoaderWithCalculateData(calculateData)
                    break
                case 400:
                    Renderer.replaceLoaderWithCalculateClientError(calculateData)
                    break
            }
        })
    }

    static handleUserCalculateResponse(resp) {
        resp.json().then(calculateData => {
            const $submitBtn = document.querySelector('.js-submit-calculate-form')
            switch (resp.status) {
                case 200:
                    Renderer.renderUserCalculationResponseTable(calculateData)
                    MainRenderer.offButtonLoadingState($submitBtn)
                    break
                case 400:
                    Renderer.renderAlertOnUserCalculationResponseWrapper()
                    MainRenderer.offButtonLoadingState($submitBtn)
                    break
            }
        })
    }
}