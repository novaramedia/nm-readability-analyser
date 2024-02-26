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

const labelScore = (score) => {
  if (score < 30) {
    return "Postgraduate";
  } else if (score < 50) {
    return "Undergraduate";
  } else if (score < 60) {
    return "Year 9-11 (ages 13-16)";
  } else if (score < 70) {
    return "Year 7-8 (ages 11-13)";
  } else if (score < 80) {
    return "Year 6 (ages 10-11)";
  } else if (score < 90) {
    return "Year 5 (ages 9-10)";
  } else {
    return "Year 4 (ages 8-9)";
  }
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

      let readabilityGradeSpan = document.querySelector("#readability-grade");

      readabilityGradeSpan.textContent = labelScore(analysis.score);
    });
  });
});
