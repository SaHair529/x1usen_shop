import BaseElementsCreator from "./BaseElementsCreator";

export default class BaseRenderer {
    static renderFullscreenLoader() {
        const fullscreenLoader = BaseElementsCreator.createFullscreenLoader()
        document.querySelector('body').appendChild(fullscreenLoader)
    }

    static removeFullscreenLoader() {
        const fullscreenLoader = document.querySelector('.fullscreen-loader')
        if (fullscreenLoader)
            fullscreenLoader.remove()
    }
}