import HTMLElements from "./HTMLElements";
import DOMElementsCreator from "./DOMElementsCreator";
import BaseElementsCreator from "../BaseElementsCreator";
import Routes from "../Routes";

export default class Renderer {

    static renderUnitCards(unitCards) {
        const detailsWindow = document.getElementById(HTMLElements.detailsWindow.id)
        detailsWindow.innerHTML = unitCards
    }

    static renderModelsModal(models) {
        const modelsModal = document.querySelector('.js-delete-modal')
        const modelsWrapper = document.createElement('div')

        modelsWrapper.className = 'brands-wrapper'

        for (let i = 0; i < models.length; i++) {
            const model_P = document.createElement('p')
            const model_A = document.createElement('a')

            model_A.textContent = models[i]
            model_A.setAttribute('href', '#'/*Routes.DetailsController.detail_brand_models+models[i]['brand']*/)

            model_P.appendChild(model_A)
            modelsWrapper.appendChild(model_P)
        }

        modelsModal.appendChild(modelsWrapper)

        document.querySelector('.brands-wrapper').remove()
        document.querySelector('body').appendChild(modelsModal)
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
            brand_A.setAttribute('href', Routes.DetailsController.detail_brand_models+brands[i]['brand'])
            brand_A.className = 'js-show-models-modal'

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