import { Controller } from 'stimulus';
import Prism from 'prismjs'

import 'prism-themes/themes/prism-vsc-dark-plus.css'
import '../css/gist.css'
import '../css/markdown.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        // This controller highlight code with prismJS
        Prism.highlightAll();
    }
}
