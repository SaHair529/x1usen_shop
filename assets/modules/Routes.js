export default class Routes {
    static NotificationsController = {
        clear_status_changed_notifications: '/notification/ajax/clear_status_changed_notifications',
        clear_order_new_comments_notifications: '/notification/ajax/clear_new_comments_notifications'
    }

    static DetailsController = {
        details_list_details: '/details/ajax/details',
        detail_brands: '/details/ajax/brands'
    }
}