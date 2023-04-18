import AttributesNaming from './HTMLAttributesNaming'
import ResponseHandler from "./ResponseHandler";
import HTMLElements from "../main/HTMLElements";

export default class CartController {
    static init() {
        this.productCardPressHandle()
        this.productInfoModalPressHandle()
        this.notAuthorizedModalPressHandle()
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
                        CartController.showProductModal(JSON.parse(productCard.dataset.product))
                    }
                }
            })
        }
    }

    static productInfoModalPressHandle() {
        const productInfoModal = document.getElementById('product-info-modal')
        if (productInfoModal != null) {
            productInfoModal.addEventListener('click', function (e) {
                if (e.target.classList.contains(AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)) {
                    CartController.addToCart(productInfoModal.dataset.productId)
                }
                else if (e.target.classList.contains(AttributesNaming.BUTTONS.REMOVE_FROM_CART.CLASS)) {
                    CartController.decreaseCartItemQuantity(productInfoModal.dataset.productId)
                }
                else if (e.target.classList.contains('close-product-modal')) {
                    productInfoModal.classList.add('hidden')
                }
            })
        }
    }

    static notAuthorizedModalPressHandle() {
        const notAuthorizedModal = document.getElementById(AttributesNaming.MODALS.NOT_AUTHORIZED_MODAL.ID)
        if (notAuthorizedModal != null) {
            notAuthorizedModal.addEventListener('click', function (e) {
                if (e.target.classList.contains('close-not-authorized-modal')) {
                    notAuthorizedModal.classList.add('hidden')
                }
            })
        }
    }

    // ______________________________________

    // button handles actions--------
    static addToCart(productId) {
        fetch(`/cart/add_item?item_id=${productId}`).then(resp => {
            ResponseHandler.handleAddToCartResponse(resp)
        })
    }

    static decreaseCartItemQuantity(productId) {
        fetch(`/cart/decrease_quantity?product_id=${productId}`).then(resp => {
            ResponseHandler.handleDecreaseCartItemQuantityResponse(resp)
        })
    }

    static showProductModal(productInfo) {
        fetch(`/cart/get_product_cart_item?product_id=${productInfo['id']}`).then(resp => {
            ResponseHandler.handleShowProductModalResponse(resp, productInfo)
        })
    }

    // ______________________________
}