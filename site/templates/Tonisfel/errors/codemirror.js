import {foldNodeProp, foldInside, indentNodeProp} from "../../../../libs/codemirror/v6/@codemirror-language.js"
import {styleTags, tags as t} from "../../../../libs/codemirror/v6/@lezer-highlight.js";
import { EditorView, basicSetup } from "../../../../libs/codemirror/v6/codemirror.js";
import { phpLanguage, php } from "../../../../libs/codemirror/v6/php/@codemirror-lang-php.js";
import { parser } from "../../../../libs/codemirror/v6/php/@lezer-php.js";
import { EditorState } from "../../../../libs/codemirror/v6/@codemirror-state.js";
import { highlightActiveLine } from "../../../../libs/codemirror/v6/@codemirror-view.js";
import { StyleModule } from "../../../../libs/codemirror/v6/style-mod.js";

const eState = EditorState.create({
    selection: {anchor: 17, head: 0},
    parent: document.querySelector("#error-code-analyzator-textarea"),
    doc: document.querySelector("#error-code-textarea").textContent,
    extensions: [ basicSetup, php(), EditorView.editable.of(false), highlightActiveLine() ],

});

const editor = new EditorView({
    parent: document.querySelector("#error-code-analyzator-textarea"),
    state: eState
});

function changeActiveLine(num) {
    let startIndex = editor.state.doc.line(num).from;

    editor.dispatch({selection: {anchor: startIndex}});
}

export { changeActiveLine };