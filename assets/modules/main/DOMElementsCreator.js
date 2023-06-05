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
}