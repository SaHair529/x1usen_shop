import AttributesNaming from './HTMLAttributesNaming'
import ResponseHandler from "./ResponseHandler";
import DOMElementsCreator from "./DOMElementsCreator";

export default class CartController {
    static init() {
        this.productCardPressHandle()
        this.productInfoModalPressHandle()
        this.cartItemCardPressHandle()
        this.imagesModalPressHandle()
    }

    // button handles------------------------
    // обработка всех нажатий в карточке продукта
    static productCardPressHandle() {
        const productsWindow = document.getElementById('details-window')
        if (productsWindow != null) {
            productsWindow.addEventListener('click', function (e) {
                const productCard = CartController.findParentElementByClass(e.target, 'product-card')
                if (e.target.classList.contains('product-card__actions-add_to_cart')) {
                    CartController.addToCart(e.target.dataset.productId).then(resp => {
                        ResponseHandler.handleAddToCartResponse(resp)
                    })
                }
                else if (e.target.classList.contains(AttributesNaming.productCard.imageZoomBtn.class)) {
                    let imageTag = e.target.nextElementSibling || e.target.parentElement.nextElementSibling
                    let imagesUrls = JSON.parse(productCard.dataset.product)['additionalImagesLinks'].split(',')
                    if (!Array.isArray(imagesUrls))
                        imagesUrls = []
                    imagesUrls.unshift(imageTag.getAttribute('src'))

                    CartController.showProductImageModal(imagesUrls)
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
        document.querySelector('body').addEventListener('click', function (e) {
            const productInfoModal = document.querySelector('#product-info-modal')

            if (productInfoModal != null) {
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
                else if (e.target.classList.contains('remove-product-modal')) {
                    e.target.remove()
                }
            }
        })
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

    static imagesModalPressHandle() {
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('right_arrow')) {
                const imagesModal = document.querySelector('.detail-image-modal')
                CartController.nextImg(imagesModal)
            }
            else if (e.target.classList.contains('left_arrow')) {
                const imagesModal = document.querySelector('.detail-image-modal')
                CartController.previousImg(imagesModal)
            }
        })
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

    static showProductImageModal(imgUrls) {
        const modal = DOMElementsCreator.createDOMElementByObject(AttributesNaming.imageModal_forCreator)
        for (let i = 0; i < imgUrls.length; i++) {
            if (imgUrls[i] === '')
                continue

            let img = document.createElement('img')

            if (i !== 0)
                img.className = 'hidden'
            else
                img.className = 'visible'

            img.setAttribute('src', imgUrls[i].trim())
            img.setAttribute('alt', 'Деталь')

            modal.appendChild(img)
        }

        if (imgUrls.length > 1) {
            const arrows = DOMElementsCreator.createArrows()
            modal.appendChild(arrows.leftArrow)
            modal.appendChild(arrows.rightArrow)
        }

        document.querySelector('body').appendChild(modal)
    }

    static previousImg(modal) {
        const currentImg = modal.querySelector('.visible')
        const previousImg = currentImg.previousElementSibling

        if (previousImg) {
            currentImg.classList.remove('visible')
            currentImg.classList.add('hidden')

            previousImg.classList.remove('hidden')
            previousImg.classList.add('visible')
        }
        else {
            const images = currentImg.parentNode.querySelectorAll('img')
            const previousImg = images[images.length-1]

            currentImg.classList.remove('visible')
            currentImg.classList.add('hidden')

            previousImg.classList.remove('hidden')
            previousImg.classList.add('visible')
        }
    }

    static nextImg(modal) {
        const currentImg = modal.querySelector('.visible')
        const nextImg = currentImg.nextElementSibling

        if (nextImg && nextImg.tagName === 'IMG') {
            currentImg.classList.remove('visible')
            currentImg.classList.add('hidden')

            nextImg.classList.remove('hidden')
            nextImg.classList.add('visible')
        }
        else {
            const nextImg = currentImg.parentNode.querySelector('img')

            currentImg.classList.remove('visible')
            currentImg.classList.add('hidden')

            nextImg.classList.remove('hidden')
            nextImg.classList.add('visible')
        }
    }

    // ______________________________

    // helper functions-------------------------------------

    static findParentElementByClass(element, className) {
        if (element.parentElement) {
            let parentElement = element.parentElement

            if (parentElement.classList.contains(className))
                return parentElement
            else
                return this.findParentElementByClass(parentElement, className)
        }
        else
            return null
    }
}