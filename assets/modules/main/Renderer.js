import HTMLElements from "./HTMLElements";

export default class Renderer {

    static renderDetailCards(detailCards) {
        const detailsWindow = document.getElementById(HTMLElements.detailsWindow.id)
        detailsWindow.innerHTML = detailCards
    }
}