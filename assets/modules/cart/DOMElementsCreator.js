import AttributesNaming from './HTMLAttributesNaming'

export default class DOMElementsCreator {
    static createRemoveFromCartButton() {
        const btnAttrs = AttributesNaming.BUTTONS.REMOVE_FROM_CART
        return this.createButton('btn btn-outline-danger '+btnAttrs.CLASS, btnAttrs.TEXT)
    }

    static createAddToCartButton() {
        const btnAttrs = AttributesNaming.BUTTONS.ADD_TO_CART
        return this.createButton('btn btn-outline-primary '+btnAttrs.CLASS, btnAttrs.TEXT)
    }

    static createNewModalButtonsContainer() {
        const newButtonsContainer = document.createElement('div')
        newButtonsContainer.className = AttributesNaming.CONTAINERS.MODAL_BUTTONS.CLASS+' new'
        newButtonsContainer.appendChild(this.createRemoveFromCartButton())
        newButtonsContainer.appendChild(this.createAddToCartButton())

        return newButtonsContainer
    }

    static createButton(className, text) {
        const btn = document.createElement('button')
        btn.className = className
        btn.textContent = text

        return btn
    }
}