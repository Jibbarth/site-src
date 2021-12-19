import { Controller } from '@hotwired/stimulus';

import jsonp from "jsonp";

const gist_css = 'https://github.githubassets.com/assets/gist-embed-b4e1b64ab37d0cf7698bb0c5008263fe.css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        console.log(this.articleValue, this.fileValue, this.githubUserValue)
        const gistLink = `https://gist.github.com/${this.githubUserValue}/${this.articleValue}.json?file=${this.fileValue}`;

        jsonp(gistLink, { timeout: 20000 }, (err, response) => {
            if (err) {
                console.warn('unable to retrieve gist content');
                return;
            }
            this.element.innerHTML = `
                <link rel="stylesheet" href="${gist_css}">
                ${response.div}
            `;
        });
    }

    static values = { article: String, file: String, githubUser: String }
}
