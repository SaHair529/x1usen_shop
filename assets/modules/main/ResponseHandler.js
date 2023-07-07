import Renderer from './Renderer'

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
}