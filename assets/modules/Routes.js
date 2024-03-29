export default class Routes {
    static NotificationsController = {
        clear_status_changed_notifications: '/notification/ajax/clear_status_changed_notifications',
        clear_order_new_comments_notifications: '/notification/ajax/clear_new_comments_notifications'
    }

    static DetailsController = {
        details_list_details: '/details/ajax/details',
        detail_brands: '/details/ajax/brands',
        detail_brand_models: '/details/ajax/brand_models/',
        detail_info: '/details'
    }

    static MainController = {
        search: '/search'
    }

    static DellinApiController = {
        dellin_calculate_cost_and_delivery_time: '/thirdparty/dellin/calculate_cost_and_delivery_time',
        dellin_custom_calculate: '/thirdparty/dellin/ajax/custom_calculate'
    }
}