/* global tinymce */

import readabilityScores from "readability-scores";
// Big reference: https://github.com/wooorm/readability/blob/main/src/index.js
import _ from "lodash";

const strip = (html) => {
  let doc = new DOMParser().parseFromString(html, "text/html");
  return doc.body.textContent || "";
};

const replacer = (match, p1, p2, p3) => {
  // replace with the 3rd matching group which will be the contents inside the Wordpress shortcode
  return p3;
};

const gradeToAge = (grade) => {
  const age = Math.round(grade + 5); // us grade level to age
  const max = 22;

  return age > max ? max : age;
};

const averageOfArray = (array) => array.reduce((a, b) => a + b) / array.length;

const runAnalysis = (rawContent) => {
  if (!rawContent) {
    return;
  }

  const postContent = strip(rawContent).replace(
    /\[(\w+)([^[\]]*?)\](.*?)\[\/\1\]/g,
    replacer
  );

  const bigAnalysis = readabilityScores(postContent);

  const average = averageOfArray([
    gradeToAge(bigAnalysis.daleChall),
    gradeToAge(bigAnalysis.ari),
    gradeToAge(bigAnalysis.colemanLiau),
    gradeToAge(bigAnalysis.fleschKincaid),
    gradeToAge(bigAnalysis.smog),
    gradeToAge(bigAnalysis.gunningFog),
  ]);

	let readabilityAgeInput = document.querySelector("#nm_readability-age-input");

	readabilityAgeInput.value = average;

  let readabilityAgeSpan = document.querySelector("#nm_readability-age");

  readabilityAgeSpan.textContent = Math.round(average);

  let daleChallDifficultWordCountSpan = document.querySelector(
    "#nm_readability-dale-chall-difficult-word-count"
  );

  daleChallDifficultWordCountSpan.textContent =
    bigAnalysis.daleChallDifficultWordCount;

  let polysyllabicWordCountSpan = document.querySelector(
    "#nm_readability-polysyllabic-word-count"
  );

  polysyllabicWordCountSpan.textContent = bigAnalysis.polysyllabicWordCount;

  let polysyllabicWordPercentageSpan = document.querySelector(
    "#nm_readability-polysyllabic-word-percentage"
  );

  polysyllabicWordPercentageSpan.textContent = Math.round(
    (bigAnalysis.polysyllabicWordCount / bigAnalysis.wordCount) * 100
  );

  let wordCountSpan = document.querySelector("#nm_readability-word-count");

  wordCountSpan.textContent = bigAnalysis.wordCount;

  let sentenceCountSpan = document.querySelector(
    "#nm_readability-sentence-count"
  );

  sentenceCountSpan.textContent = bigAnalysis.sentenceCount;
};

const debouncedBlockEditor = _.debounce(() => {
  const currentPost = wp.data.select("core/editor").getCurrentPost();

  runAnalysis(currentPost.content);
}, 500);

const debouncedClassicChange = _.debounce(() => {
  let editor = tinymce.get("content");

  if (editor) {
    const currentPost = editor.getContent();

    runAnalysis(currentPost);
  }
}
, 3000);

document.addEventListener("DOMContentLoaded", () => {
  wp.domReady(() => {
    if (!document.querySelector(".nm_readability-plugin")) {
      return;
    }

    if (!wp.data) {
      // in this case we will use tinymce. we need to find how to get the content of the editor

      tinymce.on("AddEditor", function () {
        let editor = tinymce.get("content");

        if (editor) {
          const currentPost = editor.getContent();

          runAnalysis(currentPost);

          editor.on("change", () => {
            debouncedClassicChange();
          });
        }
      });
    } else {
      wp.data.subscribe(() => {
        debouncedBlockEditor();
      });
    }
  });
});
