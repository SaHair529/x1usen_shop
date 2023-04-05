import ElementsCreator from "./DOMElementsCreator"
import AttributesNaming from './HTMLAttributesNaming'
import ResponseHandler from "./ResponseHandler";

export default class CartController {
    static init() {
        this.productCardPressHandle()
        this.productInfoModalPressHandle()
    }

    // button handles------------------------
    // обработка всех нажатий в карточке продукта
    static productCardPressHandle() {
        const productsWindow = document.getElementById('details-window')
        if (productsWindow != null) {
            productsWindow.addEventListener('click', function (e) {
                if (e.target.classList.contains('product-card__actions-add_to_cart')) {
                    CartController.addToCart(e.target.dataset.productId)
                }
                else {
                    const productCard = e.target.classList.contains('product-card') ? e.target : e.target.closest('.product-card')
                    if (productCard !== null) {
                        const productInfoModal = document.getElementById('product-info-modal')
                        const productInfo = JSON.parse(productCard.dataset.product)
                        productInfoModal.dataset.productId = productInfo['id']
                        productInfoModal.querySelector('.name').innerHTML = '<b>Наименование: </b>'+productInfo['name']
                        productInfoModal.querySelector('.price').innerHTML = '<b>Стоимость: </b>'+productInfo['price']
                        productInfoModal.classList.remove('hidden')
                    }
                }
            })
        }
    }

    // обработка всех нажатий в модалке продукта
    static productInfoModalPressHandle() {
        const productInfoModal = document.getElementById('product-info-modal')
        if (productInfoModal != null) {
            productInfoModal.addEventListener('click', function (e) {
                if (e.target.classList.contains(AttributesNaming.BUTTONS.ADD_TO_CART.CLASS)) {
                    CartController.addToCart(productInfoModal.dataset.productId)
                }
                else if (e.target.classList.contains(AttributesNaming.BUTTONS.REMOVE_FROM_CART.CLASS)) {
                    CartController.decreaseCartItemQuantity(productInfoModal.dataset.productId)
                }
                else {
                    productInfoModal.classList.add('hidden')
                }
            })
        }
    }
    // ______________________________________

    // button handles actions--------
    static addToCart(productId) {
        fetch(`/cart/add_item?item_id=${productId}`).then(resp => {
            switch (resp.status) {
                case 200:
                    resp.text().then(responseText => {
                        if (responseText === 'ok') {
                            const newButtonsContainer = ElementsCreator.createNewModalButtonsContainer()
                            const buttonsContainer = document.getElementById(AttributesNaming.MODALS.PRODUCT_MODAL.ID)
                                .querySelector('.'+AttributesNaming.CONTAINERS.MODAL_BUTTONS.CLASS)
                            buttonsContainer.replaceWith(newButtonsContainer)
                            alert('Успешно')
                        }
                        else if (responseText === 'already in cart') {
                            alert('Товар уже в корзине')
                        }
                        else if (responseText === 'out of stock') {
                            alert('Товара нет в наличии');
                        }
                    })
                    break
                case 403:
                    resp.text().then(responseText => {
                        if (responseText === 'not authorized') {
                            alert('Требуется авторизация')
                        }
                    })
                    break
            }
        })
    }

    static decreaseCartItemQuantity(productId) {
        fetch(`/cart/decrease_quantity?product_id=${productId}`).then(resp => {
            ResponseHandler.handleDecreaseCartItemQuantityResponse(resp)
        })
    }
    static showProductInfo() {

    }

    // ______________________________
}