import HTMLElements from "./HTMLElements";
import DOMElementsCreator from "./DOMElementsCreator";
import BaseElementsCreator from "../BaseElementsCreator";

export default class Renderer {

    static renderUnitCards(unitCards) {
        const detailsWindow = document.getElementById(HTMLElements.detailsWindow.id)
        detailsWindow.innerHTML = unitCards
    }

    static renderBrandsModal(brands) {
        const brandsModal = BaseElementsCreator.createModal()
        const brandsWrapper = document.createElement('div')

        brandsModal.classList.add('js-delete-modal')
        brandsWrapper.className = 'brands-wrapper'

        for (let i = 0; i < brands.length; i++) {
            const brand_P = document.createElement('p')
            const brand_A = document.createElement('a')

            brand_A.textContent = brands[i]['brand']
            brand_A.setAttribute('href', '#')

            brand_P.appendChild(brand_A)
            brandsWrapper.appendChild(brand_P)
        }
        brandsModal.appendChild(brandsWrapper)

        document.querySelector('body').appendChild(brandsModal)
    }

    static renderLoaderInDetailsWindow() {
        const detailsWindow = document.getElementById(HTMLElements.detailsWindow.id)
        const loader = DOMElementsCreator.createLoader()
        detailsWindow.innerHTML = ''
        detailsWindow.appendChild(loader)
    }
}