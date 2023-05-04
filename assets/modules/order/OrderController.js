import HTMLElements from "./HTMLElements";
import ResponseHandler from "./ResponseHandler";

export default class OrderController {
    static init() {
        this.orderCardPressHandle()
    }

    // event handles -----------------------
    static orderCardPressHandle() {
        const ordersWrapper = document.getElementById('orders')
        if (ordersWrapper != null) {
            ordersWrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains(HTMLElements.orderCard.productLink.class)) {
                    e.preventDefault()
                    const productHref = e.target.getAttribute('href')
                    OrderController.showProductInfoModal(productHref)
                }
            })
        }
    }
    // ________________________________________

    // event handles actions --------------------
    static showProductInfoModal(productHref) {
        fetch(productHref).then(resp => {
            ResponseHandler.handleShowProductInfoModalResponse(resp)
        })
    }
    // __________________________________________
}