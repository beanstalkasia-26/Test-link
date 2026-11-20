import { Node, mergeAttributes } from '@tiptap/core';
import Image from '@tiptap/extension-image';
import { EditorContent, useEditor, useEditorState } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { useState } from 'react';
import { toEmbedUrl } from '../../lib/embed';
import { PickerDialog } from './MediaPicker';

const VideoEmbed = Node.create({
    name: 'videoEmbed',
    group: 'block',
    atom: true,
    addAttributes() {
        return { src: { default: null }, title: { default: 'Embedded video' } };
    },
    parseHTML() {
        return [{ tag: 'iframe[src]' }];
    },
    renderHTML({ HTMLAttributes }) {
        return ['iframe', mergeAttributes(HTMLAttributes, { width: '560', height: '315' })];
    },
});

function Button({ onClick, active, label, children, disabled }) {
    return (
        <button type="button" className={active ? 'is-active' : ''} onClick={onClick} aria-pressed={active} aria-label={label} title={label} disabled={disabled}>
            {children}
        </button>
    );
}

export default function RichTextEditor({ id, value, onChange, invalid, describedBy }) {
    const [picking, setPicking] = useState(false);
    const editor = useEditor({
        immediatelyRender: false,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4] },
                codeBlock: false,
                link: { openOnClick: false, autolink: true, protocols: ['mailto', 'tel'], HTMLAttributes: { rel: null, target: null } },
            }),
            VideoEmbed,
            Image.configure({ inline: false, allowBase64: false }),
        ],
        content: value || '',
        editorProps: {
            attributes: {
                id,
                role: 'textbox',
                'aria-multiline': 'true',
                ...(describedBy ? { 'aria-describedby': describedBy } : {}),
                ...(invalid ? { 'aria-invalid': 'true' } : {}),
            },
        },
        onUpdate: ({ editor: e }) => onChange(e.isEmpty ? '' : e.getHTML()),
    });

    const state = useEditorState({
        editor,
        selector: ({ editor: e }) =>
            e
                ? {
                      bold: e.isActive('bold'),
                      italic: e.isActive('italic'),
                      h2: e.isActive('heading', { level: 2 }),
                      h3: e.isActive('heading', { level: 3 }),
                      bullet: e.isActive('bulletList'),
                      ordered: e.isActive('orderedList'),
                      quote: e.isActive('blockquote'),
                      link: e.isActive('link'),
                  }
                : {},
    });

    if (!editor) {
        return <div className={`rte ${invalid ? 'is-invalid' : ''}`} style={{ minHeight: '11rem' }} />;
    }

    const chain = () => editor.chain().focus();

    const setLink = () => {
        const previous = editor.getAttributes('link').href ?? '';
        const url = window.prompt('Link address (https://…, /page, mailto: or tel:). Leave empty to remove.', previous);
        if (url === null) return;
        if (url === '') return chain().extendMarkRange('link').unsetLink().run();
        if (!/^(https?:\/\/|\/|#|mailto:|tel:)/i.test(url)) {
            window.alert('Links must start with https://, /, #, mailto: or tel:');
            return;
        }
        chain().extendMarkRange('link').setLink({ href: url }).run();
    };

    const addVideo = () => {
        const url = window.prompt('YouTube or Vimeo link');
        if (!url) return;
        const src = toEmbedUrl(url)?.replace(/[?&]autoplay=1/, '');
        if (!src) {
            window.alert('Only YouTube and Vimeo videos can be embedded.');
            return;
        }
        chain().insertContent({ type: 'videoEmbed', attrs: { src } }).run();
    };

    return (
        <div className={`rte ${invalid ? 'is-invalid' : ''}`}>
            <div className="rte__toolbar" role="toolbar" aria-label="Formatting">
                <Button label="Heading 2" active={state.h2} onClick={() => chain().toggleHeading({ level: 2 }).run()}>H2</Button>
                <Button label="Heading 3" active={state.h3} onClick={() => chain().toggleHeading({ level: 3 }).run()}>H3</Button>
                <Button label="Bold" active={state.bold} onClick={() => chain().toggleBold().run()}><strong>B</strong></Button>
                <Button label="Italic" active={state.italic} onClick={() => chain().toggleItalic().run()}><em>I</em></Button>
                <Button label="Bulleted list" active={state.bullet} onClick={() => chain().toggleBulletList().run()}>• List</Button>
                <Button label="Numbered list" active={state.ordered} onClick={() => chain().toggleOrderedList().run()}>1. List</Button>
                <Button label="Quote" active={state.quote} onClick={() => chain().toggleBlockquote().run()}>“ Quote</Button>
                <Button label="Link" active={state.link} onClick={setLink}>Link</Button>
                <Button label="Insert image from the media library" onClick={() => setPicking(true)}>Image</Button>
                <Button label="Embed video" onClick={addVideo}>Video</Button>
                <Button label="Undo" onClick={() => chain().undo().run()}>Undo</Button>
                <Button label="Redo" onClick={() => chain().redo().run()}>Redo</Button>
            </div>
            <EditorContent editor={editor} />
            {picking ? (
                <PickerDialog
                    onClose={() => setPicking(false)}
                    onSelect={(item) => {
                        setPicking(false);
                        if (item.url) chain().setImage({ src: item.url, alt: item.alt || '', width: item.width, height: item.height }).run();
                    }}
                />
            ) : null}
        </div>
    );
}
