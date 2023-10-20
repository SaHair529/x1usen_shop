export default class DOMElementsCreator {
    static createLoader() {
        const loader = document.createElement('div')
        const spinner = document.createElement('div')
        const spinnerInner = document.createElement('span')

        loader.className = 'loader'
        spinner.className = 'spinner-border text-danger'
        spinnerInner.className = 'visually-hidden'
        spinner.setAttribute('role', 'status')
        spinnerInner.textContent = 'Загрузка...'

        spinner.appendChild(spinnerInner)
        loader.appendChild(spinner)

        return loader
    }

    /**
     * Создание бутстраповской таблички alert для отображения каких-либо сообщений клиенту
     * @param alertSubclass отвечает за класс алерта, от которого будет зависеть цвет алерта
     * @param alertText
     */
    static createAlert(alertSubclass, alertText) {
        const $alert = document.createElement('div')
        const $closeAlertBtn = document.createElement('button')

        $alert.className = `alert alert-${alertSubclass} alert-dismissible fade show`
        $closeAlertBtn.className = 'btn-close'

        $alert.setAttribute('role', 'alert')
        $closeAlertBtn.setAttribute('aria-label', 'Close')
        $closeAlertBtn.setAttribute('type', 'button')

        $closeAlertBtn.dataset.bsDismiss = 'alert'

        $alert.textContent = `${alertText}`
        $alert.append($closeAlertBtn)

        return $alert
    }
}