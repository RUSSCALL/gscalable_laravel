document.addEventListener('DOMContentLoaded', function () {
    initPickers();
    initEditors();
});

function initPickers() {
    // Without the library the plain selects still work with the existing options.
    if (typeof TomSelect === 'undefined') {
        return;
    }

    document.querySelectorAll('select.js-creatable').forEach(function (select) {
        var pattern = select.dataset.createPattern ? new RegExp(select.dataset.createPattern) : null;

        new TomSelect(select, {
            allowEmptyOption: true,
            placeholder: select.dataset.placeholder,
            maxOptions: null,
            createFilter: pattern,
            create: function (input) {
                // The "new:" prefix tells the server to create this option when the job is saved.
                return { value: 'new:' + input.trim(), text: input.trim() + ' (new)' };
            },
            render: {
                option_create: function (data, escape) {
                    return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
                },
                no_results: function () {
                    return pattern
                        ? '<div class="no-results">No match. Type it as "City, Country" to add it.</div>'
                        : '<div class="no-results">No match.</div>';
                }
            }
        });
    });
}

function initEditors() {
    // Without Trix the textareas stay visible and accept plain text.
    if (typeof Trix === 'undefined') {
        return;
    }

    // Must be set before any editor exists; <h3> sits under the job page's section headings.
    Trix.config.blockAttributes.heading1.tagName = 'h3';
    Trix.config.textAttributes.underline = { tagName: 'u', inheritable: true };

    document.addEventListener('trix-initialize', function (e) {
        var toolbar = e.target.toolbarElement;

        var fileTools = toolbar.querySelector('.trix-button-group--file-tools');
        if (fileTools) {
            fileTools.remove();
        }

        var italic = toolbar.querySelector('[data-trix-attribute="italic"]');
        if (italic && !toolbar.querySelector('[data-trix-attribute="underline"]')) {
            italic.insertAdjacentHTML('afterend',
                '<button type="button" class="trix-button trix-button--underline" data-trix-attribute="underline"'
                + ' data-trix-key="u" title="Underline" tabindex="-1">U</button>');
        }
    });

    document.querySelectorAll('textarea.js-rich-text').forEach(function (textarea) {
        var editor = document.createElement('trix-editor');
        editor.id = textarea.id + '_editor';
        editor.setAttribute('input', textarea.id);
        editor.setAttribute('aria-label', textarea.getAttribute('aria-label') || '');
        editor.classList.add('form-control');
        if (textarea.classList.contains('is-invalid')) {
            editor.classList.add('is-invalid');
        }

        var label = document.querySelector('label[for="' + textarea.id + '"]');
        if (label) {
            label.htmlFor = editor.id;
        }

        // The hidden textarea still submits the HTML; the server enforces "required".
        textarea.removeAttribute('required');
        textarea.hidden = true;
        textarea.after(editor);
    });

    document.addEventListener('trix-file-accept', function (e) {
        e.preventDefault();
    });
}
