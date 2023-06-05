import Renderer from './Renderer'

export default class ResponseHandler {
    static handleGetUnits(resp) {
        resp.text().then(detailCards => Renderer.renderUnitCards(detailCards))
    }
}