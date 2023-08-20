import Renderer from './Renderer'
import CartRenderer from './../cart/Renderer'
import AttributesNaming from "../cart/HTMLAttributesNaming";

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
        }
    }

    static handleTableProductCardDecreaseCartItemQuantityResponse(responsePromise, tableProductCard) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        if (responseData['quantity'] > 0) {
                            Renderer.updateTableProductCardData(tableProductCard, responseData)
                            CartRenderer.enableCartItemCardIncreaseButton(tableProductCard)
                        }
                        else {
                            Renderer.updateTableProductCardData(tableProductCard, responseData)
                            CartRenderer.disableCartItemCardDecreaseButton(tableProductCard)
                        }
                    }
                })
                break
        }
    }
}