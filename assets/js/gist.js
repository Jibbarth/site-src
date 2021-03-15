import '../css/markdown.css'
import '../css/gist.css'

import Prism from 'prismjs'
import 'prism-themes/themes/prism-vsc-dark-plus.css'


Prism.highlightAll();

document.addEventListener('DOMContentLoaded', function() {
// Share button
if (navigator.share) {
    const shareBtn = document.getElementsByClassName('share');
    shareBtn.forEach(function (btn) {
        btn.classList.remove('hidden');
        const dataToShare = { title: btn.getAttribute('data-title')};
        let url = document.location.href;
        const canonicalElement = document.querySelector('link[rel=canonical]');
        if (canonicalElement !== null) {
            url = canonicalElement.href;
        }
        dataToShare.url = url;
        btn.onclick = function(e) {
            e.preventDefault();
            navigator.share(dataToShare)
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing', error));
        };
    })

}}, false);
