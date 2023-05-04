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
}