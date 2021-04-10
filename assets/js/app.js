// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';

import '@fortawesome/fontawesome-free/css/all.min.css'
import '@fortawesome/fontawesome-free/js/all.min'

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
import '../bootstrap'

document.addEventListener('DOMContentLoaded', () => twemoji.parse(document.body), false);
