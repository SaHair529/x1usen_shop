import AttributesNaming from "./HTMLAttributesNaming"
import ElementsCreator from "./DOMElementsCreator";
import DOMElementsCreator from "./DOMElementsCreator";
import MainDOMElementsCreator from "../main/DOMElementsCreator";
import BaseElementsCreator from "../BaseElementsCreator";

export default class Renderer {
    static renderCalculationModal() {
        const modal = DOMElementsCreator.createModal()
        const calculationModalWindow = DOMElementsCreator.createDOMElementByObject(AttributesNaming.calculationModalWindow_forCreator)

        modal.append(calculationModalWindow)

        document.querySelector('body').append(modal)
    }

    static shakeElement(element) {
        element.classList.add('shake')
        setTimeout(() => {
            element.classList.remove('shake')
        }, 1000)
    }

    static addEmptyCardMessage() {
        const container = document.querySelector('.container:nth-child(2)')
        container.innerHTML = ''
        const emptyCartMessage = DOMElementsCreator.createDOMElementByObject(AttributesNaming.emptyCartMessage_forCreator)
        container.appendChild(emptyCartMessage)
    }

    static renderOutOfStockAlertOnProductModal() {
        const alert = document.createElement('div')
            alert.className = 'text-danger out-of-stock-alert'
            alert.textContent = 'Нет в наличии'

        document.getElementById(AttributesNaming.MODALS.PRODUCT_MODAL.ID)
            .querySelector('.detail-link')
            .after(alert)

    }

    static renderDetailCardsModal(productCardsTemplate) {
        const detailCardsModal = BaseElementsCreator.createModal()
        const detailCardsWrapper = document.createElement('div')

        detailCardsModal.classList.add('js-delete-modal')
        detailCardsWrapper.className = 'detail-cards-wrapper'

        if (productCardsTemplate)
            detailCardsWrapper.innerHTML = productCardsTemplate
        else
            detailCardsWrapper.appendChild(DOMElementsCreator.createDetails404())

        detailCardsModal.appendChild(detailCardsWrapper)
        document.querySelector('body').appendChild(detailCardsModal)
    }

    static disableIncreaseButton() {
        const increaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)
        increaseBtn.setAttribute('disabled', '')
    }

    static disableCartItemCardIncreaseButton(cartItemCard) {
        const increaseBtn = cartItemCard.querySelector('.'+AttributesNaming.cartItemCard.increaseBtn.class)
        increaseBtn.classList.add('disabled')
    }

    static disableCartItemCardDecreaseButton(cartItemCard) {
        const decreaseBtn = cartItemCard.querySelector('.js-decrease-cart-item')
        decreaseBtn.classList.add('disabled')
    }

    static enableCartItemCardIncreaseButton(cartItemCard) {
        const increaseBtn = cartItemCard.querySelector('.'+AttributesNaming.cartItemCard.increaseBtn.class)
        increaseBtn.classList.remove('disabled')
    }

    static enableCartItemCardDecreaseButton(cartItemCard) {
        const decreaseBtn = cartItemCard.querySelector('.'+AttributesNaming.cartItemCard.decreaseBtn.class)
        if (decreaseBtn.classList.contains('disabled'))
            decreaseBtn.classList.remove('disabled')
    }

    static enableIncreaseButton() {
        const increaseBtn = document.querySelector('.'+AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)
        increaseBtn.removeAttribute('disabled')
    }

    static renderLoaderBeforeCalculating() {
        const loader = MainDOMElementsCreator.createLoader()
        loader.classList.add('calculate_loader')

        const calculateDataBlock = document.querySelector('.calculate-data')
        if (!calculateDataBlock)
            document.querySelector('.order-form__buttons').before(loader)
        else
            calculateDataBlock.replaceWith(loader)

    }

    static replaceLoaderWithCalculateData(calculateData) {
        const loader = document.querySelector('.calculate_loader')
        const calculateDataBlock = DOMElementsCreator.createCalculateDataBlock(calculateData)
        loader.replaceWith(calculateDataBlock)
    }

    static replaceLoaderWithCalculateClientError(calculateData) {
        const loader = document.querySelector('.calculate_loader')
        const calculateDataBlock = DOMElementsCreator.createCalculateClientErrorBlock(calculateData)
        loader.replaceWith(calculateDataBlock)
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

    static updateCartItemCardData(cartItemCard, data) {
        cartItemCard.querySelector('.cart-item-card__amount').textContent = data['quantity']
        cartItemCard.querySelector('.cart-item-card__price').textContent = (data['quantity'] * data['product_price'])+' ₽'
    }

    static showProductFullInfoModal(infoTemplate) {
        const modal = ElementsCreator.createModal()
        modal.innerHTML = infoTemplate
        document.querySelector('body').append(modal)
    }

    static renderUserCalculationResponseTable(calculateData) {
        const currentDate = new Date()
        const deliveryDate = new Date(calculateData['data']['orderDates']['giveoutFromOspReceiver'])
        const neededCalculateData = {
            company: 'Деловые Линии',
            deliveryDaysAmount: Math.floor((deliveryDate - currentDate) / (1000 * 60 * 60 * 24))+' дн.',
            price: calculateData.data.price
        }

        const calculationResponseWrapper = document.querySelector('.custom-calculation-modal-window__response-wrapper')
        const userCalculationResponseTable = DOMElementsCreator.createDOMElementByObject(AttributesNaming.userCalculationResponseTable_forCreator)
        const userCalculationResponseTableRow = DOMElementsCreator.createDOMElementByObject(AttributesNaming.userCalculationResponseTableRow_forCreator)

        userCalculationResponseTableRow.querySelector('.company').textContent = neededCalculateData.company
        userCalculationResponseTableRow.querySelector('.days').textContent = neededCalculateData.deliveryDaysAmount
        userCalculationResponseTableRow.querySelector('.price').textContent = neededCalculateData.price

        userCalculationResponseTable.querySelector('tbody').append(userCalculationResponseTableRow)
        calculationResponseWrapper.innerHTML = ''
        calculationResponseWrapper.append(userCalculationResponseTable)
    }
}