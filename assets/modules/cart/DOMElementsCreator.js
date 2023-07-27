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
        cartItemCounter.appendChild(this.createIncreaseCartItemQuantityButton())

        return cartItemCounter
    }

    static createToCartButton() {
        const buttonsContainer = document.createElement('div')
        buttonsContainer.className = AttributesNaming.CONTAINERS.MODAL_BUTTONS.CLASS
        const btnAttrs = AttributesNaming.BUTTONS.ADD_TO_CART
        const button = this.createButton('btn btn-primary '+btnAttrs.CLASS, btnAttrs.TEXT)
        buttonsContainer.appendChild(button)

        return buttonsContainer
    }

    static createDetails404() {
        const details404 = document.createElement('div')
        const details404icon = document.createElement('p')

        details404.classList.add('details404')
        details404icon.className = 'details404icon'

        details404icon.innerText = 'Ничего не найдено :('

        details404.appendChild(details404icon)

        return details404
    }

    static createArrows() {
        const leftArrow = document.createElement('div')
        const rightArrow = document.createElement('div')

        rightArrow.className = 'right_arrow'
        leftArrow.className = 'left_arrow'

        return {
            leftArrow: leftArrow,
            rightArrow: rightArrow
        }
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

    static createIncreaseCartItemQuantityButton() {
        const btnAttrs = AttributesNaming.BUTTONS.INCREASE_CART_ITEM
        return this.createButton('btn btn-outline-primary '+btnAttrs.CLASS, btnAttrs.TEXT)
    }

    static createButton(className, text) {
        const btn = document.createElement('button')
        btn.className = className
        btn.textContent = text

        return btn
    }

    static createModal() {
        const modal = document.createElement('div')
        modal.className = AttributesNaming.MODALS.customModal.class

        return modal
    }

    static createDOMElementByObject(elementObject) {
        const HElement = document.createElement(elementObject['tagName'])
        HElement.className = elementObject['class']
        if ('text' in elementObject)
            HElement.textContent = elementObject['text']
        if ('attributes' in elementObject)
            for (let i in elementObject['attributes'])  {
                const attrObj = elementObject['attributes'][i]
                for (let attrKey in attrObj) {
                    const attrValue = attrObj[attrKey]
                    HElement.setAttribute(attrKey, attrValue)
                }
            }
        if ('children' in elementObject) {
            for (let i = 0; i < elementObject['children'].length; i++) {
                HElement.appendChild(this.createDOMElementByObject(elementObject['children'][i]))
            }
        }

        return HElement
    }

    static createUnitsListItem(unitPartObject) {
        const $unitsListItem = document.createElement('div')

        const $order   = document.createElement('div')
        const $name    = document.createElement('div')
        const $number  = document.createElement('div')
        const $link    = document.createElement('a')
        const $linkTxt = document.createElement('span')

        $unitsListItem.className        = 'units-list-item'
        $order.className   = 'order'
        $name.className    = 'name'
        $number.className  = 'number'
        $link.className    = 'link'
        $linkTxt.className = 'link-text'

        $order.innerText = unitPartObject['codeOnImage']
        $name.innerText = unitPartObject['name']
        $number.innerText = unitPartObject['oem']
        $linkTxt.innerText = 'Цены и аналоги'

        $link.appendChild($linkTxt)
        $unitsListItem.appendChild($order)
        $unitsListItem.appendChild($name)
        $unitsListItem.appendChild($number)
        $unitsListItem.appendChild($link)

        return $unitsListItem
    }
}