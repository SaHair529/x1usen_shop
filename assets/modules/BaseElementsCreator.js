export default class BaseElementsCreator {

    static createFullscreenLoader() {
        const fullscreenLoader = this.createModal()
        const loader = this.createLoader()

        fullscreenLoader.classList.add('fullscreen-loader')
        fullscreenLoader.appendChild(loader)

        return fullscreenLoader
    }

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

    static createModal() {
        const modal = document.createElement('div')
        modal.className = 'custom-modal'

        return modal
    }
}