import HTMLElements from "./HTMLElements";
import ResponseHandler from "./ResponseHandler";
import Renderer from "./Renderer";

export default class MainController {
    static init() {
        this.detailsTreePressHandle()
        this.userMenuHoverHandle()
        this.closeModalButtonPressHandle()
        this.handleDynamicButtonsClicks()
    }

    // button handlers
    static detailsTreePressHandle() {
        const detailsTree = document.querySelector('.'+HTMLElements.detailsTree.class)
        if (detailsTree != null) {
            detailsTree.addEventListener('click', function(e) {
                if (e.target['classList'].contains(HTMLElements.detailsTree.actionClasses.openItem))
                    MainController.toggleTreeItem(e.target)
                else if (e.target['classList'].contains(HTMLElements.detailsTree.parentItem.detailLink.class)) {
                    e.preventDefault()
                    MainController.renderDetailItems(e.target)
                }
            })
        }
    }

    static userMenuHoverHandle() {
        const userIcon = document.querySelector('.'+HTMLElements.userIcon.class)
        if (userIcon != null)
            userIcon.onmouseover = userIcon.onmouseout = this.toggleUserMenuVisibility
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
    static renderDetailItems(detailLinkTag) {
        Renderer.renderLoaderInDetailsWindow()
        fetch(detailLinkTag.getAttribute('href')).then(resp => {
            ResponseHandler.handleGetDetailItemsResponse(resp)
        })
    }

    static toggleUserMenuVisibility(e) {
        const userMenu = document.querySelector('.'+HTMLElements.userMenu.class)
        if (e.type === 'mouseover') {
            userMenu.classList.remove('hidden')
        }
        else if (e.type === 'mouseout') {
            userMenu.classList.add('hidden')
        }
    }
}