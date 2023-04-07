import AttributesNaming from './HTMLAttributesNaming'

export default class DOMElementsCreator {

    /**
     * Создание счётчика с кнопками
     * @param itemQuantity
     * @returns {HTMLDivElement}
     */
    static createCartItemCounter(itemQuantity) {
        const cartItemCounter = document.createElement('div')
        cartItemCounter.className = AttributesNaming.CONTAINERS.MODAL_BUTTONS.CLASS+' new'
        cartItemCounter.appendChild(this.createRemoveFromCartButton())
        cartItemCounter.appendChild(this.createCounter(itemQuantity))
        cartItemCounter.appendChild(this.createAddToCartButton())

        return cartItemCounter
    }

    /**
     * Создание непосредственно элемента, в котором будет вестись счёт
     * @param itemQuantity
     */
    static createCounter(itemQuantity) {
        const counter = document.createElement('span')
        counter.className = AttributesNaming.CART_ITEM_COUNTER.CLASS
        counter.textContent = itemQuantity
        return counter
    }

    static createRemoveFromCartButton() {
        const btnAttrs = AttributesNaming.BUTTONS.REMOVE_FROM_CART
        return this.createButton('btn btn-outline-danger '+btnAttrs.CLASS, btnAttrs.TEXT)
    }

    static createAddToCartButton() {
        const btnAttrs = AttributesNaming.BUTTONS.ADD_TO_CART
        return this.createButton('btn btn-outline-primary '+btnAttrs.CLASS, btnAttrs.TEXT)
    }

    static createButton(className, text) {
        const btn = document.createElement('button')
        btn.className = className
        btn.textContent = text

        return btn
    }
}