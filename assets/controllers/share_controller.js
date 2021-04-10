import { Controller } from 'stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        if (!navigator.share) {
            return;
        }
        this.element.classList.remove('hidden');
        const dataToShare = { title: this.element.getAttribute('data-title')};

        let url = document.location.href;

        const canonicalElement = document.querySelector('link[rel=canonical]');
        if (canonicalElement !== null) {
            url = canonicalElement.href;
        }
        dataToShare.url = url;

        this.element.onclick = function(e) {
            e.preventDefault();
            console.log(dataToShare);
            navigator.share(dataToShare)
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing', error));
        };
    }
}
