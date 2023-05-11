import Renderer from "./Renderer";

export default class ResponseHandler {
    static handleAddToCartResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        Renderer.updatePageAfterAddProductToCart(responseData)
                    }
                })
        }
    }
}