import AttributesNaming from "./HTMLAttributesNaming"
import ElementsCreator from "./DOMElementsCreator";

export default class Renderer {
    static disableDecreaseButton() {
        const decreaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.REMOVE_FROM_CART.CLASS)
        decreaseBtn.setAttribute('disabled', '')
    }

    static disableIncreaseButton() {
        const increaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)
        increaseBtn.setAttribute('disabled', '')
    }

    static enableIncreaseButton() {
        const increaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)
        increaseBtn.removeAttribute('disabled')
    }

    /**
     * Смена кнопки "В корзину" на кнопку увеличения и уменьшения количества товара в корзине
     */
    static replaceToCartButtonWithCounter(itemQuantity) {
        const cartItemCounter = ElementsCreator.createCartItemCounter(itemQuantity)
        const buttonsContainer = document.getElementById(AttributesNaming.MODALS.PRODUCT_MODAL.ID)
            .querySelector('.'+AttributesNaming.CONTAINERS.MODAL_BUTTONS.CLASS)
        buttonsContainer.replaceWith(cartItemCounter)
    }

    static replaceCounterWithToCartButton() {
        const newButtonsContainer = ElementsCreator.createToCartButton()
        const buttonsContainer = document.getElementById(AttributesNaming.MODALS.PRODUCT_MODAL.ID)
            .querySelector('.'+AttributesNaming.CONTAINERS.MODAL_BUTTONS.CLASS)
        buttonsContainer.replaceWith(newButtonsContainer)
    }

    static updateCounterValue(itemQuantity) {
        document.querySelector('.'+AttributesNaming.CART_ITEM_COUNTER.CLASS)
            .textContent = itemQuantity
    }

    static updateSumPrice(sumPrice) {
        document.querySelector('.'+AttributesNaming.productSumPriceCounter.class)
            .textContent = sumPrice
    }
}