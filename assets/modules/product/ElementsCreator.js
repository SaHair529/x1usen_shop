import HTMLElements from "./HTMLElements";

export default class ElementsCreator {
    static createProductCounter(productQuantity) {
        const counter = ElementsCreator.createElement(HTMLElements.quantityCounter_forCreator)
        counter.querySelector('.'+HTMLElements.quantityCounter.amountWrapper.class).textContent = productQuantity

        return counter
    }

    static createElement(elementObject) {
        const HElement = document.createElement(elementObject['tagName'])
        HElement.className = elementObject['class']
        if ('text' in elementObject)
            HElement.textContent = elementObject['text']
        if ('children' in elementObject) {
            for (let i = 0; i < elementObject['children'].length; i++) {
                HElement.appendChild(this.createElement(elementObject['children'][i]))
            }
        }

        return HElement
    }
}