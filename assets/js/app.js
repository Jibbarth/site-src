// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';

import '@fortawesome/fontawesome-free/css/all.min.css'
import '@fortawesome/fontawesome-free/js/all.min'

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
import '../bootstrap'

window.addEventListener('load', () => twemoji.parse(document.body), false);

document.addEventListener("turbo:load", () => {
    // Google Analytics.
    // https://github.com/turbolinks/turbolinks/issues/73#issuecomment-460028854
    if(typeof(gtag) == "function") {
        gtag("config", window.gaIdentifier, {
            "page_title": document.title,
            "page_path": location.href.replace(location.origin, ""),
        })
    }
})
