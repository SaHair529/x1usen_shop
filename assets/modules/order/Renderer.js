import ElementsCreator from "./ElementsCreator";

export default class Renderer {
    static showProductInfoModal(infoTemplate) {
        const modal = ElementsCreator.createModal()
        modal.innerHTML = infoTemplate
        document.querySelector('body').append(modal)
    }

    static removeNotificationIndicatorFromHeader() {
        const headerNotificationIndicator = document.querySelector('div.user-button__notifications-indicator')
        if (headerNotificationIndicator)
            headerNotificationIndicator.remove()
    }
}