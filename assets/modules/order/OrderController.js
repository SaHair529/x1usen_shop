import HTMLElements from "./HTMLElements";
import ResponseHandler from "./ResponseHandler";
import Routes from "../Routes";

export default class OrderController {
    static init() {
        this.orderTablePressHandle()
        this.clearOrderStatusChangedNotifications()
        this.clearOrderNewCommentNotifications()
    }

    // event handles -----------------------
    static orderTablePressHandle() {
        const orderTable = document.querySelector('.order-table')
        if (orderTable != null) {
            orderTable.addEventListener('click', function (e) {
                if (e.target.classList.contains(HTMLElements.orderTable.productLink.class)) {
                    e.preventDefault()
                    const productHref = e.target.getAttribute('href')
                    OrderController.showProductInfoModal(productHref)
                }
            })
        }
    }
    // ________________________________________

    // actions --------------------
    static showProductInfoModal(productHref) {
        fetch(productHref).then(resp => {
            ResponseHandler.handleShowProductInfoModalResponse(resp)
        })
    }

    static clearOrderStatusChangedNotifications() {
        if (!window.location.href.includes('/order/my_orders')) {
            return
        }

        const orderIdsWithNotifications = []
        const ordersWithNotifications = document.querySelectorAll('.order-with-status-changed-notifications')
        for (let i = 0; i < ordersWithNotifications.length; i++) {
            orderIdsWithNotifications.push(ordersWithNotifications[i].dataset.orderId)
        }

        if (orderIdsWithNotifications.length > 0) {
            fetch(Routes.NotificationsController.clear_status_changed_notifications, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({order_ids_with_notifications: orderIdsWithNotifications})
            }).then(resp => {
                ResponseHandler.handleClearOrderNotificationsResponse(resp)
            })
        }
    }

    static clearOrderNewCommentNotifications() {
        if (!window.location.href.includes('/order/item')) {
            return
        }

        const hasNewCommentNotification = document.querySelector('.has-new-comment')
        if (hasNewCommentNotification) {
            const orderId = document.querySelector('.order-id').textContent.trim()
            fetch(Routes.NotificationsController.clear_order_new_comments_notifications, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ order_id: orderId })
            })

        }
    }

    // __________________________________________
}