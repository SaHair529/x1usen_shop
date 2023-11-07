import Renderer from './Renderer'
import CartRenderer from './../cart/Renderer'
import AttributesNaming from "../cart/HTMLAttributesNaming";
import CartDOMElementsCreator from "../cart/DOMElementsCreator";

export default class ResponseHandler {
    static handleGetUnits(resp) {
        resp.text().then(detailCards => Renderer.renderUnitCards(detailCards))
    }

    static handleShowBrandsModalResponse(resp) {
        resp.json().then(brands => Renderer.renderBrandsModal(brands))
    }

    static handleShowModelsModalResponse(resp) {
        resp.json().then(models => Renderer.renderModelsModal(models))
    }

    static handleTableProductCardIncreaseCartItemQuantityResponse(responsePromise, tableProductCard) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    CartRenderer.enableCartItemCardDecreaseButton(tableProductCard)
                    if (responseData['message'] === 'ok') {
                        Renderer.updateTableProductCardData(tableProductCard, responseData)
                    }
                    else if (responseData['message'] === 'out of stock') {
                        CartRenderer.shakeElement(tableProductCard.querySelector('.cart-item-card__amount'))
                        CartRenderer.disableCartItemCardIncreaseButton(tableProductCard)
                    }
                    if (responseData['has_more_product'] === false) {
                        CartRenderer.disableCartItemCardIncreaseButton(tableProductCard)
                    }
                })
                break
            case 403:
                const notAuthorizedModal = CartDOMElementsCreator.createDOMElementByObject(AttributesNaming.notAuthorizedModal_forCreator)
                document.querySelector('body').appendChild(notAuthorizedModal)
                break
        }
    }

    static handleTableProductCardDecreaseCartItemQuantityResponse(responsePromise, tableProductCard) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        Renderer.updateTableProductCardData(tableProductCard, responseData)
                        if (responseData['product_total_balance'] > responseData['quantity'])
                            CartRenderer.enableCartItemCardIncreaseButton(tableProductCard)
                        else
                            CartRenderer.disableCartItemCardDecreaseButton(tableProductCard)
                    }
                })
                break
            case 403:
                const notAuthorizedModal = CartDOMElementsCreator.createDOMElementByObject(AttributesNaming.notAuthorizedModal_forCreator)
                document.querySelector('body').appendChild(notAuthorizedModal)
                break
        }
    }
}