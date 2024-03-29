import AttributesNaming from './HTMLAttributesNaming'
import ResponseHandler from "./ResponseHandler";
import DOMElementsCreator from "./DOMElementsCreator";
import Routes from "../Routes";
import BaseRenderer from "../BaseRenderer";
import Renderer from "./Renderer";
import MainRenderer from "../main/Renderer";
import MainController from "../main/MainController";
import Inputmask from "inputmask/lib/inputmask";

const ADDRESS_INPUT_ID = 'create_order_form_address'

export default class CartController {
    static init() {
        this.productCardPressHandle()
        this.productInfoModalPressHandle()
        this.cartItemCardPressHandle()
        this.imagesModalPressHandle()
        this.unitCardPressHandle()
        this.orderFormButtonsPressHandle()
        this.calculationModalHandle()
        this.addPhoneInputMask()
        this.putYandexSuggestOnAddressInput()
        this.addSubmitFormQuestion()
        this.wayToGetInputChangeHandle()
        this.ymapsReadyHandle()
    }

    // Handles------------------------
    static calculationModalHandle() {
        document.addEventListener('click', e => {
            if (e.target.classList.contains('js-submit-calculate-form')) {
                const $calculateForm = document.getElementById('calculate-form')
                if ($calculateForm.checkValidity()) {
                    e.preventDefault()

                    MainRenderer.setButtonToLoadingState(e.target)
                    CartController.submitCalculateForm($calculateForm)
                }
            }
        })
    }

    static ymapsReadyHandle() {
        /* global ymaps */
        if (typeof ymaps === 'undefined')
            return

        ymaps.ready(function () {
            document.querySelectorAll('#way_to_get_inputs > input').forEach(wayToGetInput => {
                if (wayToGetInput.checked) {
                    if (wayToGetInput.getAttribute('id') === 'create_order_form_way_to_get_1') {
                        CartController.swapFormState('spbdelivery-state-item')
                        CartController.setSaintPetersburgAsDefaultCityForAddressInputSuggests()
                    }
                    else if (wayToGetInput.getAttribute('id') === 'create_order_form_way_to_get_2') {
                        CartController.swapFormState('rfdelivery-state-item')
                        CartController.setCityFromInputAsDefaultCityForAddressInputSuggests()
                    }
                }
            })
        })
    }

    static wayToGetInputChangeHandle() {
        const wayToGetInput = document.querySelector('.way_to_get')
        if (!wayToGetInput)
            return

        wayToGetInput.addEventListener('change', e => {
            if (e.target.getAttribute('id') === 'create_order_form_way_to_get_0')
                CartController.swapFormState('pickup-state-item')
            else if (e.target.getAttribute('id') === 'create_order_form_way_to_get_1') {
                CartController.swapFormState('spbdelivery-state-item')
                CartController.setSaintPetersburgAsDefaultCityForAddressInputSuggests()
            }
            else if (e.target.getAttribute('id') === 'create_order_form_way_to_get_2') {
                CartController.swapFormState('rfdelivery-state-item')
                CartController.setCityFromInputAsDefaultCityForAddressInputSuggests()
            }
        })
    }

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
                let imagesUrls = productCard.dataset.productAdditionalImgLinks.split(',')
                if (!Array.isArray(imagesUrls))
                    imagesUrls = []
                imagesUrls.unshift(imageTag.getAttribute('src'))

                CartController.showProductImageModal(imagesUrls)
            }
            else {
                const productCard = e.target.classList.contains('product-card') ? e.target : e.target.closest('.product-card')
                if (productCard !== null && !productCard.classList.contains('table-product-card')) {
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

        unitNodesWindow.addEventListener('mouseover', CartController.unitNodesModalMouseOverCallback.bind(null, unitNodesWindow))

        unitNodesWindow.addEventListener('click', CartController.unitNodesModalClickCallback.bind(null, unitNodesWindow))

        document.addEventListener('keydown', CartController.unitNodesModalKeydownCallback)
    }

    static removeUnitNodesModalEventsListeners() {
        const unitNodesWindow = document.querySelector('.unit-nodes-window')

        unitNodesWindow.removeEventListener('mouseover', CartController.unitNodesModalMouseOverCallback)

        unitNodesWindow.removeEventListener('click', CartController.unitNodesModalClickCallback)

        document.removeEventListener('keydown', CartController.unitNodesModalKeydownCallback)
    }

    static unitNodesModalKeydownCallback(e) {
        if (e.key === 'Escape') {
            CartController.removeUnitNodesModalEventsListeners()
            document.querySelector('.custom-modal').remove()
        }
    }

    static unitNodesModalClickCallback(unitNodesWindow, e) {
        if (e.target.dataset.code)
            CartController.toggleActiveStateOnListAndImageNodes(unitNodesWindow, e.target)
        else if (e.target.classList.contains('js-close-units-modal')) {
            CartController.removeUnitNodesModalEventsListeners()
            document.querySelector('.custom-modal').remove()
        }
    }

    static unitNodesModalMouseOverCallback(unitNodesWindow, e) {
        if (e.target.dataset.code) {
            CartController.highlightHoveredNodeOnListAndImage(unitNodesWindow, e.target)
        }
        else {
            CartController.clearAllUnitNodesHoverStates()
        }
    }

    static productInfoModalPressHandle() {
        document.querySelector('body').addEventListener('click', function (e) {
            const productInfoModal = document.querySelector('#product-info-modal')

            if (productInfoModal != null) {
                if (e.target.classList.contains(AttributesNaming.BUTTONS.INCREASE_CART_ITEM.CLASS)) {
                    CartController.addToCart(JSON.parse(productInfoModal.dataset.productInfo)).then(resp => {
                        ResponseHandler.handleAddToCartResponse(resp)
                    })
                }
                else if (e.target.classList.contains('detail-link')) {
                    e.preventDefault()
                    MainController.showProductFullInfoModal(e.target.getAttribute('href'))
                }
                else if (e.target.classList.contains(AttributesNaming.BUTTONS.REMOVE_FROM_CART.CLASS)) {
                    CartController.decreaseCartItemQuantity(JSON.parse(productInfoModal.dataset.productInfo)).then(resp => {
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
                const articleItem = JSON.parse(e.target.closest('.'+AttributesNaming.cartItemCard.class).dataset.articleItem)
                const cartItemCard = e.target.closest('.cart-item-card')

                if (e.target.classList.contains(AttributesNaming.cartItemCard.increaseBtn.class) &&
                    !e.target.classList.contains('disabled'))
                {
                    CartController.addToCart(articleItem).then(resp => {
                        ResponseHandler.handleCartItemCardIncreaseCartItemQuantityResponse(resp, cartItemCard)
                    })
                }
                else if (e.target.classList.contains(AttributesNaming.cartItemCard.decreaseBtn.class) &&
                        !e.target.classList.contains('disabled'))
                {
                    const cartItemsAmount = e.target.nextElementSibling.textContent

                    if (parseInt(cartItemsAmount) === 1) {
                        const deleteUserConfirm = confirm('Вы уверены, что хотите убрать товар из корзины?')
                        if (deleteUserConfirm)
                            CartController.decreaseCartItemQuantity(articleItem).then(resp => {
                                ResponseHandler.handleCartItemCardDecreaseCartItemQuantityResponse(resp, cartItemCard)
                            })
                    }
                    else
                        CartController.decreaseCartItemQuantity(articleItem).then(resp => {
                            ResponseHandler.handleCartItemCardDecreaseCartItemQuantityResponse(resp, cartItemCard)
                        })
                }
                else if (e.target.classList.contains(AttributesNaming.cartItemCard.delButton.class)) {
                    const deleteConfirm = window.confirm('Вы уверены, что хотите убрать товар из корзины?')
                    if (deleteConfirm) {
                        const cartItemId = e.target.closest('.'+AttributesNaming.cartItemCard.class).dataset.cartItemId
                        CartController.deleteCartItem(cartItemId).then(resp => {
                            ResponseHandler.handleCartItemCardRemoveCartItemResponse(resp, cartItemCard)
                        })
                    }
                }
                else if (e.target.classList.contains(AttributesNaming.cartItemCard.showDetails.class)) {
                    MainController.showProductFullInfoModal(e.target.dataset.productRoute)
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

    static orderFormButtonsPressHandle() {
        const orderForm = document.getElementsByName('create_order_form')[0]
        if (!orderForm)
            return

        orderForm.addEventListener('click', e => {
            if (e.target.classList.contains('js-calculate-shipping-cost')) {
                CartController.calculateShippingCost()
            }
            else if (e.target.classList.contains('js-show-calculation-modal')) {
                CartController.showCalculationModal()
            }
        })

    }

    static addSubmitFormQuestion() {
        const createOrderForm = document.querySelector('form[name="create_order_form"]')

        if (!createOrderForm)
            return

        createOrderForm.querySelector('button[type="submit"]')
            .addEventListener('click', e => {
                const requiredFields = createOrderForm.querySelectorAll('input[type="text"][required]')
                let hasEmptyRequiredFields = false
                for (let i = 0; i < requiredFields.length; i++) {
                    if (!requiredFields[i].value) {
                        hasEmptyRequiredFields = true
                        break
                    }
                }

                if (!hasEmptyRequiredFields) {
                    e.preventDefault()
                    const userResponse = confirm('Вы уверены, что хотите оформить заказ?')
                    if (userResponse === true) {
                        const cartItemsCheckboxes = document.getElementById('cart-items').querySelectorAll('input[type="checkbox"]')
                        let checkedCartItemsIds = ''
                        for (let i = 0; i < cartItemsCheckboxes.length; i++) {
                            if(cartItemsCheckboxes[i].checked) {
                                checkedCartItemsIds += ' '+cartItemsCheckboxes[i].getAttribute('value')
                            }
                        }
                        document.getElementById('cart_items_ids').setAttribute('value', checkedCartItemsIds)
                        createOrderForm.submit()
                    }
                }
            })
    }
    // ______________________________________

    // ----------actions--------

    static submitCalculateForm(calculateForm) {
        const requestData = getFormData(calculateForm)

        fetch(Routes.DellinApiController.dellin_custom_calculate, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        }).then(resp => {
            ResponseHandler.handleUserCalculateResponse(resp)
        })

        function getFormData(form) {
            const result = {}

            form.querySelectorAll('input').forEach((input) => {
                result[input.name] = input.value
            })

            return result
        }
    }

    /** Переход формы на состояние, соответствующее классу указанному в параметре stateClass */
    static swapFormState(stateClass) {
        const waytogetStateItems = document.getElementsByClassName('waytoget-state-item')
        for (let i = 0; i < waytogetStateItems.length; i++) {
            const stateItem = waytogetStateItems[i]
            if (!stateItem.classList.contains(stateClass))
                removeStateItemFromState(stateItem)
            else
                showStateItemInState(stateItem)
        }

        function removeStateItemFromState(stateItem) {
            if (!stateItem.classList.contains('hidden'))
                stateItem.classList.add('hidden')

            if (stateItem.tagName === 'INPUT' && stateItem.hasAttribute('required'))
                stateItem.removeAttribute('required')
        }

        function showStateItemInState(stateItem) {
            stateItem.classList.remove('hidden')

            if (stateItem.tagName === 'INPUT' && !stateItem.hasAttribute('required'))
                stateItem.setAttribute('required', 'required')
        }
    }

    static setSaintPetersburgAsDefaultCityForAddressInputSuggests() {
        document.querySelectorAll('ymaps')
            .forEach(oldSuggestView => oldSuggestView.remove())

        new ymaps.SuggestView(ADDRESS_INPUT_ID, {
            provider: {
                suggest: (function (req) {
                    return ymaps.suggest('Санкт-Петербург, '+req)
                })
            }
        })
    }

    static setCityFromInputAsDefaultCityForAddressInputSuggests() {
        document.querySelectorAll('ymaps')
            .forEach(oldSuggestView => oldSuggestView.remove())

        new ymaps.SuggestView(ADDRESS_INPUT_ID, {
            provider: {
                suggest: (function (req) {
                    return ymaps.suggest(document.getElementById('create_order_form_city').value+', '+req)
                })
            }
        })
    }

    static addPhoneInputMask() {
        const phoneInput = document.getElementById('create_order_form_phone_number')
        if (!phoneInput)
            return

        Inputmask({mask: '+7 (999)-999-99-99'}).mask(phoneInput)
    }

    static putYandexSuggestOnAddressInput() {
        /* global ymaps */
        const ADDRESS_INPUT_ID = 'create_order_form_address'
        const $addressInput = document.getElementById(ADDRESS_INPUT_ID)
        if (!$addressInput || typeof ymaps === 'undefined')
            return

        ymaps.ready(() => {
            /**
             * Получение координат по введённому адресу, если он точный.
             * Очистка инпута адреса, если он неточный
             */
            $addressInput.addEventListener('blur', e => {
                if (e.target.value.length === 0)
                    return

                // Очистка инпута с адресом, если адрес некорректный. Получение координат, если корректный
                setTimeout(() => {
                    isAddressExact(e.target.value)
                        .then(isAddressExactResponse => {
                            if(!isAddressExactResponse)
                                clearAddressInput()
                            isAddressCorrect(e.target.value)
                                .then(isAddressCorrectResponse => {
                                    if (!isAddressCorrectResponse) {
                                        clearAddressInput()
                                        return
                                    }
                                    getAddressCoords(e.target.value)
                                        .then(coordsObj => putAddressCoordsIntoCoordsInput(coordsObj))
                                })
                        })
                }, 250)
            })

            /**
             * Проверка точности адреса
             * Нужно для того, чтобы при неточном адресе инпут адреса очищался
             * @returns {Promise}
             */
            function isAddressExact(address) {
                return ymaps.geocode(address).then(res => {
                    const geoObj = res.geoObjects.get(0)
                    if (!geoObj)
                        return false
                    return geoObj.properties.get('metaDataProperty.GeocoderMetaData.precision') === 'exact';
                })
            }
            /**
             * Проверка корректности адреса путём сравнения с получаемыми корректными адресами из yandex
             * @param address
             * @returns {Promise}
             */
            function isAddressCorrect(address) {
                return ymaps.suggest(address).then(foundGeoObjects => {
                    for (let foundGeoObj of foundGeoObjects)
                        if (address === foundGeoObj.value)
                            return true
                    return false
                })
            }
            function clearAddressInput() {
                document.getElementById(ADDRESS_INPUT_ID).value = ''
            }
            /**
             * Получение широты и долготы параметра address
             * @returns {Promise}
             */
            function getAddressCoords(address) {
                return ymaps.geocode(address).then(res => {
                    const geoObjCoords = res.geoObjects.get(0).geometry.getCoordinates()
                    return {
                        latitude: geoObjCoords[0],
                        longitude: geoObjCoords[1]
                    }
                })
            }
            function putAddressCoordsIntoCoordsInput(coordsObj) {
                document.getElementById('create_order_form_addressGeocoords')
                    .value = `${coordsObj.latitude}:${coordsObj.longitude}`
            }
        })
    }

    static calculateShippingCost() {
        const requestData = {}

        requestData.wayToGet = getWayToGet()
        requestData.checkedCartItemsIds = getCheckedCartItemsIds()
        requestData.address = document.querySelector('#create_order_form_address').value

        const validationErrors = validateRequestData(requestData)
        if (Object.keys(validationErrors).length > 0)
            alertErrors(validationErrors)
        else
            calculateShippingCost(requestData)


        function calculateShippingCost() {
            Renderer.renderLoaderBeforeCalculating()

            fetch(Routes.DellinApiController.dellin_calculate_cost_and_delivery_time, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            }).then(resp => {
                ResponseHandler.handleCalculateResponse(resp)
            })
        }
        function alertErrors(validationErrors) {
            let validationErrorsString = ''
            for (let key in validationErrors) {
                const error = validationErrors[key]
                validationErrorsString += '- '+error+"\n"
            }
            alert(validationErrorsString)
        }
        function validateRequestData(requestData) {
            const validationErrors = {}

            if (requestData.checkedCartItemsIds === '')
                validationErrors.checkedCartItemsIds = 'Выделите необходимые товары'
            if (requestData.city === '')
                validationErrors.checkedCartItemsIds = 'Укажите город'
            if (requestData.address === '')
                validationErrors.address = 'Укажите адрес'

            return validationErrors
        }
        function getWayToGet() {
            let wayToGet = ''
            document.querySelectorAll('#way_to_get_inputs input')
                .forEach(item => {
                    if (item.checked)
                        wayToGet = item.getAttribute('id')
                })

            return wayToGet
        }
        function getCheckedCartItemsIds() {
            let checkedCartItemsIds = ''
            const cartItemsCheckboxes = document.getElementById('cart-items').querySelectorAll('input[type="checkbox"]')
            for (let i = 0; i < cartItemsCheckboxes.length; i++)
                if(cartItemsCheckboxes[i].checked)
                    checkedCartItemsIds +=' '+cartItemsCheckboxes[i].getAttribute('value')

            return checkedCartItemsIds
        }
    }

    static addToCart(item) {
        return fetch(`/cart/add_item`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(item)
        })
    }

    static decreaseCartItemQuantity(item) {
        return fetch(`/cart/decrease_quantity`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(item)
        })
    }

    static deleteCartItem(cartItemId) {
        return fetch(`/cart/remove_item?item_id=${cartItemId}`)
    }

    static showProductModal(productInfo) {
        fetch(`/cart/get_product_cart_item?product_id=${productInfo['id']}`).then(resp => {
            ResponseHandler.handleShowProductModalResponse(resp, productInfo)
        })
    }

    static showCalculationModal() {
        Renderer.renderCalculationModal()
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
        const $linkedNodes = unitNodesWindow.querySelectorAll(`[data-code="${hoveredNodeCode}"]`)

        $linkedNodes.forEach($node => {
            $node.classList.add('hovered')
        })
    }

    static toggleActiveStateOnListAndImageNodes($unitNodesWindow, $clickedNode) {
        const $clickedNodeCode = $clickedNode.dataset.code

        const $linkedNodes = $unitNodesWindow.querySelectorAll(`[data-code="${$clickedNodeCode}"]`)
        $linkedNodes.forEach($node => {
            $node.classList.toggle('active')
        })
    }

    static clearAllUnitNodesHoverStates() {
        const $nodes = document.querySelectorAll('.units-list-item, .map-object')

        $nodes.forEach($node => $node.classList.remove('hovered'))
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