import ElementsCreator from "./ElementsCreator";

export default class Renderer {
    static showProductInfoModal(infoTemplate) {
        const modal = ElementsCreator.createModal()
        modal.innerHTML = infoTemplate
        document.querySelector('body').append(modal)
    }
}