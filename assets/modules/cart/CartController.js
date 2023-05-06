import AttributesNaming from './HTMLAttributesNaming'
import ResponseHandler from "./ResponseHandler";

export default class CartController {
    static init() {
        this.productCardPressHandle()
        this.productInfoModalPressHandle()
        this.notAuthorizedModalPressHandle()
        this.cartItemCardPressHandle()
    }

    // button handles------------------------
    // обработка всех нажатий в карточке продукта
    static productCardPressHandle() {
        const productsWindow = document.getElementById('details-window')
        if (productsWindow != null) {
            productsWindow.addEventListener('click', function (e) {
                if (e.target.classList.contains('product-card__actions-add_to_cart')) {
                    CartController.addToCart(e.target.dataset.productId).then(resp => {
                        ResponseHandler.handleAddToCartResponse(resp)
                    })
                }
                else if (e.target.classList.contains(AttributesNaming.productCard.imageZoomBtn.class)) {
                    let imageTag = e.target.nextElementSibling || e.target.parentElement.nextElementSibling
                    const imageUrl = imageTag.getAttribute('src')
                    CartController.showProductImageModal(imageUrl)
                }
                else {
                    const productCard = e.target.classList.contains('product-card') ? e.target : e.target.closest('.product-card')
                    if (productCard !== null) {
                        let productInfo = JSON.parse(productCard.dataset.product)
                        productInfo.route = productCard.dataset.productRoute
                        CartController.showProductModal(productInfo)
                    }
                }
            })
        }
    }

    static productInfoModalPressHandle() {
        const productInfoModal = document.getElementById(AttributesNaming.MODALS.PRODUCT_MODAL.ID)
        if (productInfoModal != null) {
            productInfoModal.addEventListener('click', function (e) {
                if (e.target.classList.contains(AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)) {
                    CartController.addToCart(productInfoModal.dataset.productId).then(resp => {
                        ResponseHandler.handleAddToCartResponse(resp)
                    })
                }
                else if (e.target.classList.contains(AttributesNaming.BUTTONS.REMOVE_FROM_CART.CLASS)) {
                    CartController.decreaseCartItemQuantity(productInfoModal.dataset.productId).then(resp => {
                        ResponseHandler.handleDecreaseCartItemQuantityResponse(resp)
                    })
                }
                else if (e.target.classList.contains('close-product-modal')) {
                    productInfoModal.classList.add('hidden')
                }
                else if (e.target.classList.contains(AttributesNaming.MODALS.PRODUCT_MODAL.detailInfoLink.class)) {
                    e.preventDefault()
                    // CartController.showProductFullInfoModal(e.target.getAttribute('href'))
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

    static cartItemCardPressHandle() {
        const cartItems = document.getElementById('cart-items')
        if (cartItems != null) {
            cartItems.addEventListener('click', function (e) {
                const productId = e.target.closest('.'+AttributesNaming.cartItemCard.class).dataset.productId
                const cartItemCard = e.target.closest('.cart-item-card')

                if (e.target.classList.contains(AttributesNaming.cartItemCard.increaseBtn.class) &&
                    !e.target.classList.contains('disabled'))
                {
                    CartController.addToCart(productId).then(resp => {
                        ResponseHandler.handleCartItemCardIncreaseCartItemQuantityResponse(resp, cartItemCard)
                    })
                }
                else if (e.target.classList.contains(AttributesNaming.cartItemCard.decreaseBtn.class) &&
                        !e.target.classList.contains('disabled'))
                {
                    CartController.decreaseCartItemQuantity(productId).then(resp => {
                        ResponseHandler.handleCartItemCardDecreaseCartItemQuantityResponse(resp, cartItemCard)
                    })
                }
                else if (e.target.classList.contains(AttributesNaming.cartItemCard.delButton.class)) {
                    const cartItemId = e.target.closest('.'+AttributesNaming.cartItemCard.class).dataset.cartItemId
                    CartController.deleteCartItem(cartItemId).then(resp => {
                        ResponseHandler.handleCartItemCardRemoveCartItemResponse(resp, cartItemCard)
                    })
                }
            })
        }
    }
    // ______________________________________

    // button handles actions--------
    static addToCart(productId) {
        return fetch(`/cart/add_item?item_id=${productId}`)
    }

    static decreaseCartItemQuantity(productId) {
        return fetch(`/cart/decrease_quantity?product_id=${productId}`)
    }

    static deleteCartItem(cartItemId) {
        return fetch(`/cart/remove_item?item_id=${cartItemId}`)
    }

    static showProductModal(productInfo) {
        fetch(`/cart/get_product_cart_item?product_id=${productInfo['id']}`).then(resp => {
            ResponseHandler.handleShowProductModalResponse(resp, productInfo)
        })
    }

    static showProductFullInfoModal(productLink) {
        fetch(productLink).then(resp => {
            ResponseHandler.handleShowProductFullInfoModal(resp)
        })
    }

    static showProductImageModal(imgUrl) {
        const modal = document.getElementById(AttributesNaming.MODALS.imageModal.id)
        const modalImage = modal.querySelector('img')
        modalImage.setAttribute('src', imgUrl)
        modal.classList.remove('hidden')
    }

    // ______________________________
}