import HTMLElements from "./HTMLElements";
import ResponseHandler from "./ResponseHandler";
import Renderer from "./Renderer";
import CartRenderer from "./../cart/Renderer";
import Routes from "../Routes";
import CartController from "../cart/CartController";

export default class MainController {
    static init() {
        this.searchFormContainerPressHandle()
        this.detailsTreePressHandle()
        this.closeModalButtonPressHandle()
        this.handleDynamicButtonsClicks()
        this.brandsModalPressHandle()
        this.mainPageKeysPressHandle()
        this.headerKeysPressHandle()
        this.headerPressHandle()
        this.tableProductCardPressHandle()
        this.productFullInfoModalPressHandle()
    }

    static productFullInfoModalPressHandle() {
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('js-put-to-image-wrapper') || e.target.parentElement.classList.contains('js-put-to-image-wrapper')) {
                const clickedGalleryItem = e.target.classList.contains('js-put-to-image-wrapper') ? e.target : e.target.parentElement
                MainController.putGalleryItemToImageWrapper(clickedGalleryItem)
            }
        })
    }

    static tableProductCardPressHandle() {
        document.querySelector('body').addEventListener('click', function (e) {
            const clickedItemParentProductCard = CartController.findParentElementByClass(e.target, 'table-product-card')
            if (clickedItemParentProductCard === null)
                return

            let productInfo = JSON.parse(clickedItemParentProductCard.dataset.product)

            if (e.target.classList.contains('js-show-details')) {
                MainController.showProductFullInfoModal(productInfo.id)
            }
        })
    }

    static headerKeysPressHandle() {
        document.addEventListener('keydown', function (e) {
            const queryInput = document.getElementById('search_form_query_string_mini')
            if (e.key === 'Enter' && queryInput === document.activeElement) {
                e.preventDefault()
                document.getElementById('search-form-mini').submit()
            }
        })
    }

    static headerPressHandle() {
        const queryInput = document.getElementById('search_form_query_string_mini')
        document.querySelector('header').addEventListener('click', function (e) {
            if (e.target['classList'].contains('search-form-mini-submit') && queryInput.value !== '') {
                e.preventDefault()
                document.getElementById('search-form-mini').submit()
            }
            else if (e.target['classList'].contains('js-show-brands-modal')) {
                MainController.showBrandsModal()
            }
        })
    }

    // button handlers
    static searchFormContainerPressHandle() {
        const searchFormContainer = document.querySelector('.search-form-container')
        const queryInput = document.getElementById('search_form_query_string')
        if (searchFormContainer != null) {
            searchFormContainer.addEventListener('click', function (e) {
                if (e.target['classList'].contains('js-show-brands-modal')) {
                    MainController.showBrandsModal()
                }
                else if (e.target['classList'].contains('search-form-submit') && queryInput.value !== '') {
                    e.preventDefault()
                    document.getElementById('search-form').submit()
                }
            })
        }
    }

    static mainPageKeysPressHandle() {
        document.addEventListener('keydown', function (e) {
            const queryInput = document.getElementById('search_form_query_string')
            if (e.key === 'Enter' && queryInput === document.activeElement && queryInput.value !== '') {
                e.preventDefault()
                document.getElementById('search-form').submit()
            }
        })
    }

    static brandsModalPressHandle() {
        document.addEventListener('click', function (e) {
            if (e.target['classList'].contains('js-show-models-modal')) {
                e.preventDefault()
                MainController.showModelsModal(e.target.getAttribute('href'))
            }
        })
    }

    static detailsTreePressHandle() {
        const detailsTree = document.querySelector('.'+HTMLElements.detailsTree.class)
        if (detailsTree != null) {
            detailsTree.addEventListener('click', function(e) {
                if (e.target['classList'].contains(HTMLElements.detailsTree.actionClasses.openItem))
                    MainController.toggleTreeItem(e.target)
                else if (e.target['classList'].contains(HTMLElements.detailsTree.parentItem.detailLink.class)) {
                    e.preventDefault()
                    MainController.renderUnits(e.target)
                }
            })
        }
    }

    static closeModalButtonPressHandle() {
        const closeModalButtons = document.getElementsByClassName(HTMLElements.GLOBAL_ACTION_BUTTONS.closeModal.class)
        for (let i = 0; i < closeModalButtons.length; i++) {
            closeModalButtons[i].addEventListener('click', function (e) {
                if (e.target != null)
                    e.target.classList.add('hidden')
            })
        }
    }

    static handleDynamicButtonsClicks() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains(HTMLElements.GLOBAL_ACTION_BUTTONS.deleteModal.class)) // Удалить модалку
                e.target.remove()
        })
    }
    // _________________________


    // handlers actions
    static putGalleryItemToImageWrapper(clickedGalleryItem) {
        document.querySelectorAll('.detail-full-info__gallery-item')
            .forEach(galleryItem => galleryItem.classList.remove('active'))
        clickedGalleryItem.classList.add('active')

        const clickedGalleryImageHref = clickedGalleryItem.querySelector('img').getAttribute('src')
        document.querySelector('.detail-full-info__img').setAttribute('src', clickedGalleryImageHref)
    }
    static toggleTreeItem(item) {
        // открытие/закрытие родительского элемента дерева
        if (item.classList.contains(HTMLElements.detailsTree.parentItem.class)) {
            if (item.classList.contains('opened'))
                item.classList.remove('opened')
            else
                item.classList.add('opened')
        }
        else {
            // поиск и открытие/закрытие родительского элемента дерева
            const parentItem = item.closest('.'+HTMLElements.detailsTree.parentItem.class)
            if (parentItem.classList.contains('opened'))
                parentItem.classList.remove('opened')
            else
                parentItem.classList.add('opened')
        }
    }
    static renderUnits(detailLinkTag) {
        Renderer.renderLoaderInDetailsWindow()
        fetch(detailLinkTag.getAttribute('href')).then(resp => {
            ResponseHandler.handleGetUnits(resp)
        })
    }
    static showBrandsModal() {
        fetch(Routes.DetailsController.detail_brands).then(resp => {
            ResponseHandler.handleShowBrandsModalResponse(resp)
        })
    }
    static showModelsModal(href) {
        fetch(href).then(resp => {
            ResponseHandler.handleShowModelsModalResponse(resp)
        })
    }
    static showProductFullInfoModal(productId) {
        fetch(Routes.DetailsController.detail_info+'/'+productId)
            .then(resp => {
                resp.text().then(productInfoTemplate => {
                    CartRenderer.showProductFullInfoModal(productInfoTemplate)
                })
            })
    }
}