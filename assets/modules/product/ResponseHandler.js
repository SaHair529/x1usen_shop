import Renderer from "./Renderer";

export default class ResponseHandler {
    static handleAddToCartResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        Renderer.updatePageAfterSuccessAddProductToCart(responseData)
                    }
                    else if (responseData['message'] === 'out of stock') {
                        Renderer.updatePageAfterOutOfStock()
                    }
                })
                break
        }
    }

    static handleDecreaseCartItemQuantityResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    Renderer.updatePageAfterSuccessDecreaseItem(responseData)
                })
                break
        }
    }
}