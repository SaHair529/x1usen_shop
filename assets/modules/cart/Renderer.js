import AttributesNaming from "./HTMLAttributesNaming"

export default class Renderer {
    static disableDecreaseButton() {
        const decreaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.REMOVE_FROM_CART.CLASS)
        decreaseBtn.setAttribute('disabled', '')
    }
}