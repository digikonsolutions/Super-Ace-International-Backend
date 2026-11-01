/* Super Ace Admin — small vanilla JS helpers (no frameworks). */
(function () {
    'use strict';

    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

    // Mobile sidebar
    $$('[data-nav-toggle]').forEach(btn => btn.addEventListener('click', () => document.body.classList.toggle('nav-open')));
    $$('.scrim').forEach(s => s.addEventListener('click', () => document.body.classList.remove('nav-open')));

    // Dismiss alerts
    $$('.alert__close').forEach(b => b.addEventListener('click', () => b.closest('.alert').remove()));

    // Confirm before destructive submits: <form data-confirm="Delete this?">
    $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
        if (!window.confirm(f.dataset.confirm)) e.preventDefault();
    }));

    // Image upload preview
    $$('.upload').forEach(box => {
        const input = $('input[type=file]', box);
        const img = $('.upload__preview', box);
        if (!input) return;
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file || !img) return;
            img.src = URL.createObjectURL(file);
            img.hidden = false;
            box.classList.add('has-image');
            const rm = box.parentElement.querySelector('[name="remove_' + input.name + '"]');
            if (rm) rm.checked = false;
        });
        ['dragenter', 'dragover'].forEach(ev => box.addEventListener(ev, () => box.classList.add('is-drag')));
        ['dragleave', 'drop'].forEach(ev => box.addEventListener(ev, () => box.classList.remove('is-drag')));
    });

    // Multi-file gallery preview (new uploads)
    $$('[data-gallery-input]').forEach(input => {
        const target = $(input.dataset.galleryInput);
        input.addEventListener('change', () => {
            if (!target) return;
            target.innerHTML = '';
            Array.from(input.files || []).forEach(file => {
                const item = document.createElement('div');
                item.className = 'gallery__item';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                item.appendChild(img);
                target.appendChild(item);
            });
        });
    });

    // Existing gallery: tick to remove
    $$('.gallery__remove input').forEach(cb => cb.addEventListener('change', () => {
        cb.closest('.gallery__item').classList.toggle('is-removed', cb.checked);
    }));

    // Slug preview: fills the slug placeholder from the name/title
    $$('[data-slug-from]').forEach(slug => {
        const src = $(slug.dataset.slugFrom);
        if (!src) return;
        const make = v => v.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        const sync = () => { slug.placeholder = make(src.value) || 'auto-generated-from-name'; };
        src.addEventListener('input', sync);
        sync();
    });

    // Character counters: <textarea data-maxchars="160">
    $$('textarea[data-maxchars], input[data-maxchars]').forEach(el => {
        const max = +el.dataset.maxchars;
        const out = document.createElement('span');
        out.className = 'hint';
        el.insertAdjacentElement('afterend', out);
        const upd = () => { out.textContent = el.value.length + ' / ' + max + ' characters'; out.style.color = el.value.length > max ? 'var(--danger)' : ''; };
        el.addEventListener('input', upd);
        upd();
    });

    // Rich text editor (contenteditable + hidden textarea)
    $$('[data-editor]').forEach(wrap => {
        const area = $('.editor__area', wrap);
        const source = $('.editor__source', wrap);
        const form = wrap.closest('form');
        const sync = () => { source.value = area.innerHTML.trim() === '<br>' ? '' : area.innerHTML; };

        area.innerHTML = source.value;

        $$('.editor__btn', wrap).forEach(btn => btn.addEventListener('click', e => {
            e.preventDefault();
            const cmd = btn.dataset.cmd;
            if (cmd === 'source') {
                if (wrap.classList.contains('is-source')) { area.innerHTML = source.value; }
                else { sync(); }
                wrap.classList.toggle('is-source');
                btn.classList.toggle('is-on');
                return;
            }
            area.focus();
            if (cmd === 'link') {
                const url = prompt('Link URL (https://...)');
                if (url) document.execCommand('createLink', false, url);
            } else if (cmd === 'image') {
                const url = prompt('Image URL (https://...)');
                if (url) document.execCommand('insertImage', false, url);
            } else if (btn.dataset.value) {
                document.execCommand(cmd, false, btn.dataset.value);
            } else {
                document.execCommand(cmd, false, null);
            }
            sync();
        }));

        area.addEventListener('input', sync);
        // Paste as plain text to keep markup clean
        area.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });
        if (form) form.addEventListener('submit', () => { if (!wrap.classList.contains('is-source')) sync(); });
    });

    // Bulk selection on tables
    $$('[data-bulk]').forEach(form => {
        const all = $('[data-check-all]', form);
        const boxes = () => $$('[data-check]', form);
        const bar = $('.bulkbar', form);
        const count = $('[data-bulk-count]', form);
        const refresh = () => {
            const n = boxes().filter(b => b.checked).length;
            if (bar) bar.classList.toggle('is-visible', n > 0);
            if (count) count.textContent = n;
            if (all) all.checked = n > 0 && n === boxes().length;
        };
        if (all) all.addEventListener('change', () => { boxes().forEach(b => (b.checked = all.checked)); refresh(); });
        form.addEventListener('change', e => { if (e.target.matches('[data-check]')) refresh(); });
        form.addEventListener('submit', e => {
            const action = $('[name=action]', form);
            if (action && action.value === 'delete' && !confirm('Delete the selected inquiries? This cannot be undone.')) e.preventDefault();
        });
    });

    // Live Google (SERP) preview for the SEO card
    $$('[data-seo-preview]').forEach(box => {
        const d = box.dataset;
        const q = sel => (sel ? document.querySelector(sel) : null);
        const titleIn = $('#meta_title'), descIn = $('#meta_description');
        const titleSrc = q(d.titleSource), descSrc = q(d.descSource), slugIn = $('#slug'), pathIn = $('#path');
        const strip = html => { const t = document.createElement('div'); t.innerHTML = html || ''; return (t.textContent || '').replace(/\s+/g, ' ').trim(); };
        const slugify = v => v.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        const cut = (s, n) => (s.length > n ? s.slice(0, n - 1).trim() + '…' : s);
        const render = () => {
            let raw = (titleIn && titleIn.value.trim()) || (titleSrc && titleSrc.value.trim()) || d.site;
            const hasSite = raw.toLowerCase().includes(d.site.toLowerCase());
            const title = (d.rawTitle && titleIn && titleIn.value.trim()) || hasSite ? raw : d.format.replace('{title}', raw).replace('{site}', d.site);
            let desc = (descIn && descIn.value.trim()) || (descSrc && strip(descSrc.value)) || d.defaultDesc || '';
            let path = d.path || '/';
            if (pathIn) path = pathIn.value.trim() || '/';
            const slug = (slugIn && slugIn.value.trim()) || (titleSrc ? slugify(titleSrc.value) : '') || 'your-page';
            path = path.replace('{slug}', slug);
            if (!path.startsWith('/')) path = '/' + path;
            $('[data-serp-title]', box).textContent = cut(title, 62);
            $('[data-serp-desc]', box).textContent = cut(desc, 160);
            $('[data-serp-url]', box).textContent = (d.base + (path === '/' ? '' : path)).replace(/^https?:\/\//, '').replace(/\//g, ' › ');
        };
        [titleIn, descIn, titleSrc, descSrc, slugIn, pathIn].forEach(el => el && el.addEventListener('input', render));
        render();
    });

    // Clickable table rows: <tr data-href="...">
    $$('tr[data-href]').forEach(tr => tr.addEventListener('click', e => {
        if (e.target.closest('a, button, input, label, form')) return;
        window.location = tr.dataset.href;
    }));
})();
