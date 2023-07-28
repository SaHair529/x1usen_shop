import AttributesNaming from './HTMLAttributesNaming'
import ResponseHandler from "./ResponseHandler";
import DOMElementsCreator from "./DOMElementsCreator";
import Routes from "../Routes";
import BaseRenderer from "../BaseRenderer";

export default class CartController {
    static init() {
        this.productCardPressHandle()
        this.productInfoModalPressHandle()
        this.cartItemCardPressHandle()
        this.imagesModalPressHandle()
        this.unitCardPressHandle()
    }

    // button handles------------------------
    // обработка всех нажатий в карточке продукта
    static productCardPressHandle() {
        document.querySelector('body').addEventListener('click', function (e) {
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

    static unitCardPressHandle() {
        const detailsWindow = document.getElementById('details-window')
        if (detailsWindow != null) {
            detailsWindow.addEventListener('click', function (e) {
                if (e.target.classList.contains(AttributesNaming.unitCard.itemsLink.class)) {
                    e.preventDefault()
                    BaseRenderer.renderFullscreenLoader()
                    const unitCard = CartController.findParentElementByClass(e.target, 'unit-card')
                    CartController.showUnitAvailableDetails(unitCard.dataset.unitPartsOems)
                }
                else if (e.target.classList.contains('js-show-unit-nodes-modal')) {
                    const unitCard = e.target.closest('.unit-card')
                    const unitParts = JSON.parse(unitCard.dataset.unitPartsJson)
                    const unitImageMaps = JSON.parse(unitCard.dataset.unitImageMaps)
                    const imageUrl = e.target.closest('img').getAttribute('src').replace(/\/150/g, '/source')
                    CartController.showUnitNodesModal(unitParts, unitImageMaps, imageUrl)
                    CartController.addUnitNodesModalEventsListeners()
                }
            })
        }
    }

    static addUnitNodesModalEventsListeners() {
        const unitNodesWindow = document.querySelector('.unit-nodes-window')

        unitNodesWindow.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains(AttributesNaming.unitNodesWindow.unitsList.unitsListItem.class)) {
                CartController.highlightHoveredNodeOnListAndImage(unitNodesWindow, e.target)
            }
        })
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

    static showUnitAvailableDetails(unitPartsOems) {
        fetch(Routes.DetailsController.details_list_details, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ oems: unitPartsOems })
        }).then(resp => {
            ResponseHandler.handleShowUnitAvailableDetailsResponse(resp)
        })
    }

    static showUnitNodesModal(unitParts, unitImageMaps, imageUrl) {
        const $modal = DOMElementsCreator.createDOMElementByObject(AttributesNaming.unitNodesModal_forCreator)
        const $unitNodesImage_img = $modal.querySelector('.unit-nodes-image')
        $unitNodesImage_img.setAttribute('src', imageUrl)

        CartController.appendUnitNodesToModalList(unitParts, $modal.querySelector('.units-list'))

        document.querySelector('body').appendChild($modal) // Сначала рендерим модалку, т.к. для дальнейших действий требуется информация по размерам

        setTimeout(() => {
            CartController.appendUnitNodesToModalMap($unitNodesImage_img, unitImageMaps, $modal.querySelector('.unit-nodes-image-map'))
        }, 250)
    }

    static appendUnitNodesToModalMap($unitNodesImage_img, unitImageMaps, $unitNodesImageMap) {

        const $mapObjectsWrapper = document.createElement('div')
        $mapObjectsWrapper.classList.add('map-objects-wrapper')
        $mapObjectsWrapper.style.position = 'absolute'
        $mapObjectsWrapper.style.width = $unitNodesImage_img.width+'px'
        $mapObjectsWrapper.style.height = $unitNodesImage_img.height+'px'

        $unitNodesImageMap.appendChild($mapObjectsWrapper)

        for (let imageMapKey in unitImageMaps) {
            const imageMap = unitImageMaps[imageMapKey]
            for (let mapObjectKey in imageMap['mapObjects']) {
                const mapObject = imageMap['mapObjects'][mapObjectKey]
                const $mapObject = DOMElementsCreator.createMapObjectByMapObject(mapObject, $unitNodesImage_img)
                $mapObjectsWrapper.appendChild($mapObject)
            }
        }
    }

    static appendUnitNodesToModalList(unitParts, $unitsList) {
        for (let i = 0; i < unitParts.length; i++) {
            const unitPart = unitParts[i]
            const $unitsListItem = DOMElementsCreator.createUnitsListItem(unitPart)
            $unitsList.appendChild($unitsListItem)
        }
    }

    static highlightHoveredNodeOnListAndImage(unitNodesWindow, hoveredNode) {
        const oldHoveredItems = unitNodesWindow.querySelectorAll('.units-list-item.hovered, .map-object.hovered')
        oldHoveredItems.forEach(item => {
            item.classList.remove('hovered')
        })

        hoveredNode.classList.add('hovered')

        const hoveredNodeCode = hoveredNode.dataset.code
        const otherSideNodeClass = hoveredNode.classList.contains('units-list-item') ? 'map-object' : 'units-list-item'
        const $otherSideNodes = unitNodesWindow.querySelectorAll('.'+otherSideNodeClass+`[data-code="${hoveredNodeCode}"]`)

        $otherSideNodes.forEach($node => {
            $node.classList.add('hovered')
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