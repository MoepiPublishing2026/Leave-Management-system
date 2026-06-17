@once
<style>
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }
    .filter-select-wrap {
        position: relative;
        min-width: 168px;
    }
    .signup-container .filter-select-wrap,
    .leave-form .filter-select-wrap,
    .app-form-group .filter-select-wrap,
    .forward-modal .filter-select-wrap,
    .input-group .filter-select-wrap {
        width: 100%;
        min-width: 0;
    }
    .balance-year-form .filter-select-wrap {
        min-width: 148px;
        flex: 0 1 auto;
    }
    .filter-select__native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    .filter-select__trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-family: inherit;
        font-size: 0.92rem;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        text-align: left;
        line-height: 1.2;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .filter-select__trigger:hover {
        border-color: #94a3b8;
        background: #fefefe;
    }
    .filter-select__chevron {
        width: 8px;
        height: 8px;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: rotate(45deg) translateY(-2px);
        flex-shrink: 0;
        transition: transform 0.15s ease;
    }
    .filter-select-wrap.is-open .filter-select__chevron {
        transform: rotate(-135deg) translateY(1px);
    }
    .filter-select-wrap.is-open .filter-select__trigger {
        border-color: #b2cd16;
        box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.28);
    }
    .filter-select__menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 12050;
        margin: 0;
        padding: 6px;
        list-style: none;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16);
        max-height: 280px;
        overflow-y: auto;
        display: none;
    }
    .filter-select-wrap.is-open .filter-select__menu {
        display: block;
    }
    .filter-select__option {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: background-color 0.12s ease, color 0.12s ease;
    }
    .filter-select__option:hover,
    .filter-select__option.is-focused {
        background: rgba(178, 205, 22, 0.2);
        color: #0a1347;
    }
    .filter-select__option.is-selected {
        background: #b2cd16;
        color: #0a1347;
    }
    .filter-select__option.is-selected:hover {
        background: #9eb812;
    }
    .filter-form .filter-clear,
    .reports-filter-form .reports-btn--secondary {
        margin-left: 0;
    }
    @media (max-width: 768px) {
        .filter-form {
            width: 100%;
        }
        .filter-select-wrap {
            flex: 1 1 100%;
            min-width: 0;
        }
    }
</style>
<script>
    (function () {
        if (window.__filterSelectInit) return;
        window.__filterSelectInit = true;

        const ENHANCE_SELECTOR = 'select:not([data-filter-enhanced]):not(.filter-select__native):not([data-native-select])';

        const closeAllFilterSelects = () => {
            document.querySelectorAll('.filter-select-wrap.is-open').forEach((wrap) => {
                wrap.classList.remove('is-open');
                const trigger = wrap.querySelector('.filter-select__trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        };

        const syncOptions = (select, menu) => {
            menu.innerHTML = '';
            Array.from(select.options).forEach((option) => {
                const item = document.createElement('li');
                item.className = 'filter-select__option' + (option.selected ? ' is-selected' : '');
                item.textContent = option.textContent;
                item.dataset.value = option.value;
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', option.selected ? 'true' : 'false');
                item.addEventListener('click', (event) => {
                    event.stopPropagation();
                    select.value = option.value;
                    menu.querySelectorAll('.filter-select__option').forEach((node) => {
                        node.classList.remove('is-selected');
                        node.setAttribute('aria-selected', 'false');
                    });
                    item.classList.add('is-selected');
                    item.setAttribute('aria-selected', 'true');
                    const triggerLabel = select.closest('.filter-select-wrap')?.querySelector('.filter-select__label');
                    if (triggerLabel) {
                        triggerLabel.textContent = option.textContent;
                    }
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    closeAllFilterSelects();
                });
                menu.appendChild(item);
            });
        };

        const enhanceSelect = (select) => {
            if (select.dataset.filterEnhanced === '1') return;
            select.dataset.filterEnhanced = '1';

            const wrap = document.createElement('div');
            wrap.className = 'filter-select-wrap';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'filter-select__trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');

            const label = document.createElement('span');
            label.className = 'filter-select__label';
            label.textContent = select.options[select.selectedIndex]?.textContent || '';

            const chevron = document.createElement('span');
            chevron.className = 'filter-select__chevron';
            chevron.setAttribute('aria-hidden', 'true');

            trigger.appendChild(label);
            trigger.appendChild(chevron);

            const menu = document.createElement('ul');
            menu.className = 'filter-select__menu';
            menu.setAttribute('role', 'listbox');

            syncOptions(select, menu);

            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const isOpen = wrap.classList.contains('is-open');
                closeAllFilterSelects();
                if (!isOpen) {
                    wrap.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });

            menu.addEventListener('click', (event) => event.stopPropagation());

            select.classList.add('filter-select__native');
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(trigger);
            wrap.appendChild(menu);
            wrap.appendChild(select);

            select.addEventListener('change', () => {
                const selected = select.options[select.selectedIndex];
                if (selected && label) {
                    label.textContent = selected.textContent;
                }
                syncOptions(select, menu);
            });
        };

        window.initFilterSelects = function initFilterSelects(root) {
            (root || document).querySelectorAll(ENHANCE_SELECTOR).forEach(enhanceSelect);
        };

        document.addEventListener('click', (event) => {
            if (event.target.closest('.filter-select-wrap')) {
                return;
            }
            closeAllFilterSelects();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAllFilterSelects();
        });

        const boot = () => window.initFilterSelects();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>
@endonce
