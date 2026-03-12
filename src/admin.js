/* global tinymce, wp */
import readabilityScores from "readability-scores";

const WORDS_PER_MINUTE = 238;

function debounce(fn, ms) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), ms);
  };
}

function stripHTML(html) {
  const doc = new DOMParser().parseFromString(html, "text/html");
  return doc.body.textContent || "";
}

function stripShortcodes(text) {
  return text.replace(/\[([A-Za-z0-9_-]+)([^[\]]*?)\](.*?)\[\/\1\]/g, (_, _tag, _attrs, content) => content);
}

function gradeToAge(grade) {
  return Math.min(22, Math.round(grade + 5));
}

function average(arr) {
  return arr.reduce((a, b) => a + b, 0) / arr.length;
}

function analyse(rawContent) {
  if (!rawContent) return null;

  const text = stripShortcodes(stripHTML(rawContent));
  const scores = readabilityScores(text);

  const readingAge = Math.round(average([
    gradeToAge(scores.daleChall),
    gradeToAge(scores.ari),
    gradeToAge(scores.colemanLiau),
    gradeToAge(scores.fleschKincaid),
    gradeToAge(scores.smog),
    gradeToAge(scores.gunningFog),
  ]));

  const readTime = Math.max(1, Math.ceil(scores.wordCount / WORDS_PER_MINUTE));

  return { readingAge, readTime, scores };
}

function updateUI(result) {
  if (!result) return;

  const { readingAge, readTime, scores } = result;

  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  const setValue = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.value = value;
  };

  setText("nm-readability-age", readingAge);
  setText("nm-read-time", readTime + " min");
  setText("nm-readability-words", scores.wordCount);
  setText("nm-readability-sentences", scores.sentenceCount);
  setText("nm-readability-polysyllabic", scores.polysyllabicWordCount);
  setText("nm-readability-dale-chall", scores.daleChallDifficultWordCount);

  const pct = scores.wordCount > 0
    ? Math.round((scores.polysyllabicWordCount / scores.wordCount) * 100)
    : 0;
  setText("nm-readability-polysyllabic-pct", pct);

  setValue("nm-readability-age-input", readingAge);
  setValue("nm-read-time-input", readTime);
}

function run(content) {
  updateUI(analyse(content));
}

const debouncedRun = debounce(run, 500);

document.addEventListener("DOMContentLoaded", () => {
  if (typeof wp !== "undefined" && wp.domReady) {
    wp.domReady(() => {
      if (!document.querySelector(".nm-readability")) return;

      const editorStore = wp.data && typeof wp.data.select === "function"
        ? wp.data.select("core/editor")
        : null;
      const hasBlockEditor = editorStore && typeof editorStore.getCurrentPost === "function";

      if (hasBlockEditor) {
        // Block editor
        const getContent = () => editorStore.getCurrentPost().content;
        run(getContent());
        wp.data.subscribe(() => debouncedRun(getContent()));
      } else if (typeof tinymce !== "undefined") {
        // Classic editor
        tinymce.on("AddEditor", () => {
          const editor = tinymce.get("content");
          if (!editor) return;
          run(editor.getContent());
          editor.on("change", () => debouncedRun(editor.getContent()));
        });
      }
    });
  }
});
