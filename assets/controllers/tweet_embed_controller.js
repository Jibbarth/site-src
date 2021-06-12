import { Controller } from 'stimulus';

// @see https://developer.twitter.com/en/docs/twitter-for-websites/embedded-tweets/guides/embedded-tweet-parameter-reference
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        id: String,
        conv: Boolean, // enable conversation mode
    }

    connect() {
        console.log({'tweetId': this.idValue, 'conversation': this.convValue})
        if (!window.twttr) {
            // twitterscript not yet loaded. force load
            console.error('Twittter script is not yet loaded');
            return;
        }

        window.twttr.widgets.createTweet(
            this.idValue,
            this.element,
            {
                conversation: this.convValue ? 'yes': 'none',
                align: 'center',
                dnt: true // do not track
            }
        );
    }
}
