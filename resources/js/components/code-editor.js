import { basicSetup } from "codemirror";
import { EditorState } from '@codemirror/state';
import { EditorView, keymap } from "@codemirror/view";

import { indentWithTab } from "@codemirror/commands"

import { Compartment } from '@codemirror/state';

import { basicLight } from '@fsegurai/codemirror-theme-basic-light'
import { basicDark } from '@fsegurai/codemirror-theme-basic-dark'

import { javascript } from "@codemirror/lang-javascript";
import { php } from "@codemirror/lang-php";
import { css } from "@codemirror/lang-css";
import { html } from "@codemirror/lang-html";
import { json } from "@codemirror/lang-json";

export default function codeEditorFormComponent({
    state, 
    isDarkMode, 
    isReadOnly, 
}) {
    return {
        state,
        isDarkMode,
        isReadOnly,

        editor: undefined,

        themeConfig: undefined,

        init() {
            this.configureThemeConfig();
            this.configureEditor();
        },
        getTheme() {
            return this.isDarkMode ? basicDark : basicLight;
        },
        toggleTheme(isDarkMode) {
            this.isDarkMode = isDarkMode;
        
            if (this.editor) {
                this.editor.dispatch({
                    effects: this.themeConfig.reconfigure(this.getTheme())
                });
            }
        },
        configureThemeConfig() {
            if (this.themeConfig === undefined) {
                this.themeConfig = new Compartment();
            }
        },
        configureEditor() {
            this.configureThemeConfig();

            const themeExtension = this.themeConfig.of(this.getTheme());

            this.editor = new EditorView({
                state: EditorState.create({
                    autofocus: false,
                    indentWithTabs: true,
                    smartIndent: true,
                    lineNumbers: true,
                    matchBrackets: true,
                    lineWrapping: true,
                    styleSelectedText: true,
                    indentUnit: 6,
                    tabSize: 4,
                    extensions: [

                        basicSetup,

                        keymap.of([
                            indentWithTab,
                        ]),

                        html({
                            matchClosingTags: true,
                            selfClosingTags: true,
                            autoCloseTags: true,
                        }),
                        javascript(),
                        css(),
                        php(),
                        json(),

                        themeExtension,

                        EditorView.updateListener.of((v) => {
                            if (v.docChanged) {
                                this.state = v.state.doc.toString();
                            }
                        }),

                        EditorState.readOnly.of(this.isReadOnly),
                        EditorView.editable.of(!this.isReadOnly),
                        
                    ],

                    doc: this.state
                }),

                parent: this.$refs.codeEditor,
            });
        },
    }
};