import Renderer from './Renderer'

export default class ResponseHandler {
    static handleGetDetailItemsResponse(resp) {
        resp.text().then(detailCards => Renderer.renderDetailCards(detailCards))
    }
}