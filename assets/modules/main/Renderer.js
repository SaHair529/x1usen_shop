import HTMLElements from "./HTMLElements";
import DOMElementsCreator from "./DOMElementsCreator";

export default class Renderer {

    static renderDetailCards(detailCards) {
        const detailsWindow = document.getElementById(HTMLElements.detailsWindow.id)
        if (detailCards) {
            detailsWindow.innerHTML = detailCards
        }
        else {
            detailsWindow.innerHTML = ''
            detailsWindow.appendChild(DOMElementsCreator.createDetails404())
        }
    }

    static renderLoaderInDetailsWindow() {
        const detailsWindow = document.getElementById(HTMLElements.detailsWindow.id)
        const loader = DOMElementsCreator.createLoader()
        detailsWindow.innerHTML = ''
        detailsWindow.appendChild(loader)
    }
}