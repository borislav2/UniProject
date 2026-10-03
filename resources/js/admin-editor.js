// Article editor in the admin panel: Markdown under the hood, a toolbar for people who do not know Markdown.
import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

const icons = JSON.parse(document.getElementById('editor-icons')?.textContent || '{}');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const uploadUrl = document.getElementById('editor-icons')?.dataset.uploadUrl;

function uploadImage(file, onSuccess, onError) {
    const body = new FormData();
    body.append('image', file);
    fetch(uploadUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
        .then((r) => r.json().then((data) => (r.ok ? onSuccess(data.url) : onError(data.message || 'Снимката не можа да се качи.'))))
        .catch(() => onError('Снимката не можа да се качи.'));
}

const button = (name, action, title, icon) => ({ name, action, title, icon: `<span class="editor-icon">${icons[icon] || title}</span>` });

document.querySelectorAll('textarea[data-markdown]').forEach((element) => {
    new EasyMDE({
        element,
        autoDownloadFontAwesome: false,
        spellChecker: false,
        status: false,
        forceSync: true,
        minHeight: '320px',
        uploadImage: true,
        imageUploadFunction: uploadImage,
        imageAccept: 'image/png, image/jpeg, image/webp, image/gif',
        placeholder: element.getAttribute('placeholder') || '',
        toolbar: [
            button('heading-2', EasyMDE.toggleHeading2, 'Подзаглавие', 'heading'),
            button('bold', EasyMDE.toggleBold, 'Удебелен', 'bold'),
            button('italic', EasyMDE.toggleItalic, 'Курсив', 'italic'),
            '|',
            button('unordered-list', EasyMDE.toggleUnorderedList, 'Списък', 'list-ul'),
            button('ordered-list', EasyMDE.toggleOrderedList, 'Номериран списък', 'list-ol'),
            button('quote', EasyMDE.toggleBlockquote, 'Цитат', 'quote-left'),
            '|',
            button('link', EasyMDE.drawLink, 'Линк', 'link'),
            button('upload-image', EasyMDE.drawUploadedImage, 'Снимка', 'image'),
            button('horizontal-rule', EasyMDE.drawHorizontalRule, 'Разделителна линия', 'minus'),
            '|',
            button('preview', EasyMDE.togglePreview, 'Преглед', 'eye'),
        ],
    });
});

// Language tabs (BG / EN) in the article form. Without JavaScript both languages are simply shown one after another.
document.querySelectorAll('[data-tabs]').forEach((tabs) => {
    const buttons = tabs.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('[data-panel]');
    const show = (name) => {
        buttons.forEach((b) => b.setAttribute('aria-selected', b.dataset.tab === name ? 'true' : 'false'));
        panels.forEach((p) => p.classList.toggle('hidden', p.dataset.panel !== name));
        // CodeMirror needs a refresh after being hidden.
        window.dispatchEvent(new Event('resize'));
        document.querySelectorAll(`[data-panel="${name}"] .CodeMirror`).forEach((cm) => cm.CodeMirror?.refresh());
    };
    tabs.classList.remove('hidden');
    buttons.forEach((b) => b.addEventListener('click', () => show(b.dataset.tab)));
    show(buttons[0].dataset.tab);
});
