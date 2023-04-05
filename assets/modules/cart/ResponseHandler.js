import Renderer from "./Renderer"

export default class ResponseHandler {
    static handleDecreaseCartItemQuantityResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['current_quantity'] === 0) {
                        Renderer.disableDecreaseButton()
                    }
                })
                break
            case 422:
                break
        }
    }
}