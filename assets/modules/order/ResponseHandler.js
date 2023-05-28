import Renderer from "./Renderer";

export default class ResponseHandler {
    static handleShowProductInfoModalResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.text().then(productTemplate => {
                    Renderer.showProductInfoModal(productTemplate)
                })
        }
    }

    static handleClearOrderNotificationsResponse(responsePromise) {
        switch (responsePromise.status) {
            case 200:
                responsePromise.json().then(responseData => {
                    if (responseData['message'] === 'ok') {
                        Renderer.removeNotificationIndicatorFromHeader()
                    }
                })
                break
        }
    }
}