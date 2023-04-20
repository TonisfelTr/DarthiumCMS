import {foldNodeProp, foldInside, indentNodeProp} from "../../../../libs/codemirror/v6/@codemirror-language.js"
import {styleTags, tags as t} from "../../../../libs/codemirror/v6/@lezer-highlight.js";
import {EditorView, basicSetup} from "../../../../libs/codemirror/v6/codemirror.js";
import {phpLanguage as phpLang, php} from "../../../../libs/codemirror/v6/php/@codemirror-lang-php.js";
import {htmlLanguage, html} from "../../../../libs/codemirror/v6/@codemirror-lang-html.js";
import {parser} from "../../../../libs/codemirror/v6/php/@lezer-php.js";
import {EditorState} from "../../../../libs/codemirror/v6/@codemirror-state.js";
import {highlightActiveLine} from "../../../../libs/codemirror/v6/@codemirror-view.js";
import {StyleModule} from "../../../../libs/codemirror/v6/style-mod.js";

const eState = EditorState.create({
    selection: {anchor: 17, head: 0},
    parent: document.querySelector("#error-code-analyzator-textarea"),
    doc: document.querySelector("#error-code-textarea").textContent,
    extensions: [
        basicSetup,
        html(),
        php(),
        htmlLanguage,
        phpLang,
        EditorView.editable.of(false),
        highlightActiveLine()],
});

const editor = new EditorView({
    parent: document.querySelector("#error-code-analyzator-textarea"),
    state: eState
});

function changeActiveLineInDOM(num) {
    let startIndex = editor.state.doc.line(num).from;

    editor.dispatch({
        scrollIntoView: true,
        selection: {
            anchor: startIndex
        }
    });
}

function changeText(code) {
    editor.dispatch({newDoc: code});
}

export {changeActiveLineInDOM, changeText};