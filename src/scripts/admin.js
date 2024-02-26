/**
 * Run scripts on document ready
 * No jQuery here sorry
 */

const readability = require("readability-meter");

const strip = (html) => {
  let doc = new DOMParser().parseFromString(html, "text/html");
  return doc.body.textContent || "";
}

const replacer = (match, p1, p2, p3) => { // replace with the 3rd matching group which will be the contents inside the shortcode
  return p3;
}

document.addEventListener("DOMContentLoaded", () => {
  wp.domReady(() => {
    if (!document.querySelector("#readability-score")) {
      return;
    }

    wp.data.subscribe(() => {
      const currentPost = wp.data.select("core/editor").getCurrentPost();
      const postContent = strip(currentPost.content).replace(
        /\[(\w+)([^[\]]*?)\](.*?)\[\/\1\]/g,
        replacer
      );

      const analysis = readability.ease(postContent);

      let readabilityScoreSpan = document.querySelector("#readability-score");

      readabilityScoreSpan.textContent = Math.round(analysis.score);
    });
  });
});
