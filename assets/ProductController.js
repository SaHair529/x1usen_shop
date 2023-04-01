
export default class ProductController {
    static addToCart(productId) {
        fetch(`/cart/add_item?item_id=${productId}`).then(resp => {
            switch (resp.status) {
                case 200:
                    resp.text().then(responseText => {
                        if (responseText === 'ok') {
                            alert('Успешно')
                        }
                    })
                    break
                case 403:
                    resp.text().then(responseText => {
                        if (responseText === 'not authorized') {
                            alert('Требуется авторизация')
                        }
                    })
            }
        })
    }
}