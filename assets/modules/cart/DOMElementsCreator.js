export default class DOMElementsCreator {
    static createRemoveFromCartButton() {
        return DOMElementsCreator.createButton('btn btn-outline-danger remove-from-cart', '-1')
    }

    static createAddToCartButton() {
        return DOMElementsCreator.createButton('btn btn-outline-primary add-to-cart', '+1')
    }

    static createButton(className, text) {
        const btn = document.createElement('button')
        btn.className = className
        btn.textContent = text

        return btn
    }
}