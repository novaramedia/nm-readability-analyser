/**
 * Run scripts on document ready
 * No jQuery here sorry
 */

import readabilityScores from "readability-scores";
// Big reference: https://github.com/wooorm/readability/blob/main/src/index.js
import _ from "lodash";

const strip = (html) => {
  let doc = new DOMParser().parseFromString(html, "text/html");
  return doc.body.textContent || "";
}

const replacer = (match, p1, p2, p3) => { // replace with the 3rd matching group which will be the contents inside the Wordpress shortcode
  return p3;
}

const gradeToAge = (grade) => {
  const age = Math.round(grade + 5); // us grade level to age
  const max = 22;

  return age > max ? max : age;
}

const averageOfArray = (array) => array.reduce((a, b) => a + b) / array.length;

const runAnalysis = () => {
  const currentPost = wp.data.select("core/editor").getCurrentPost();
  const postContent = strip(currentPost.content).replace(
    /\[(\w+)([^[\]]*?)\](.*?)\[\/\1\]/g,
    replacer
  );

  const bigAnalysis = readabilityScores(postContent);

  console.log(bigAnalysis);

  const average = averageOfArray([
    gradeToAge(bigAnalysis.daleChall),
    gradeToAge(bigAnalysis.ari),
    gradeToAge(bigAnalysis.colemanLiau),
    gradeToAge(bigAnalysis.fleschKincaid),
    gradeToAge(bigAnalysis.smog),
    gradeToAge(bigAnalysis.gunningFog),
  ]);

  let readabilityAgeSpan = document.querySelector("#nm_readability-age");

  readabilityAgeSpan.textContent = Math.round(average);

  // let readabilityGradeSpan = document.querySelector("#readability-grade");

  // readabilityGradeSpan.textContent = labelScore(analysis.score);

  let daleChallDifficultWordCountSpan = document.querySelector("#nm_readability-dale-chall-difficult-word-count");

  daleChallDifficultWordCountSpan.textContent = bigAnalysis.daleChallDifficultWordCount;

  let polysyllabicWordCountSpan = document.querySelector(
    "#nm_readability-polysyllabic-word-count"
  );

  polysyllabicWordCountSpan.textContent = bigAnalysis.polysyllabicWordCount;

  let polysyllabicWordPercentageSpan = document.querySelector(
    "#nm_readability-polysyllabic-word-percentage"
  );

  polysyllabicWordPercentageSpan.textContent =
    Math.round(bigAnalysis.polysyllabicWordCount / bigAnalysis.wordCount * 100);

  let wordCountSpan = document.querySelector("#nm_readability-word-count");

  wordCountSpan.textContent = bigAnalysis.wordCount;

  let sentenceCountSpan = document.querySelector(
    "#nm_readability-sentence-count"
  );

  sentenceCountSpan.textContent = bigAnalysis.sentenceCount;
};

const debouncedRunAnalysis = _.debounce(() => {
  console.log("Running analysis");
  runAnalysis();
}, 500);

document.addEventListener("DOMContentLoaded", () => {
  wp.domReady(() => {
    if (!document.querySelector(".nm_readability-plugin")) {
      return;
    }

    wp.data.subscribe(() => {
      debouncedRunAnalysis();
    });
  });
});
