@extends((auth()->user()->role ?? null) === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Messages')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --msg-bg: #FFFFFF;
        --msg-text: #1A2B3A;
        --msg-lime: #CDE800;
        --msg-blue: #2FB5F0;
        --msg-search: #D3DCE3;
        --msg-selected: #C2CBD3;
        --msg-input: #E9F4F6;
        --msg-voice: #EDF7F0;
        --msg-banner: #FFF6E0;
        --msg-divider: #D0D5DB;
    }

    /* Fill the fixed main area; scroll happens inside the chat list / message stack. */
    html body:has(.messages-page) main.content {
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        max-height: none !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        padding-top: 16px !important;
        padding-bottom: 8px !important;
    }

    html body .messages-page {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 0% !important;
        height: 0 !important; /* flex + height:0 forces remaining viewport height */
        min-height: 0 !important;
        overflow: hidden !important;
        background: var(--msg-bg);
        color: var(--msg-text);
        padding-bottom: 0;
        border-bottom: none;
        margin-bottom: 0;
        width: 100% !important;
        max-width: none !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        box-sizing: border-box !important;
    }

    html body .messages-page,
    html body .messages-page * {
        font-family: 'Poppins', sans-serif;
    }

    html body main .messages-page .messages-page-title,
    .messages-page-title {
        flex: 0 0 auto !important;
        font-weight: 800 !important;
        font-size: 31px !important;
        color: var(--msg-text) !important;
        margin: 0 0 8px 0 !important;
        padding: 0 !important;
        text-align: left !important;
        letter-spacing: 0 !important;
        font-family: 'Poppins', sans-serif !important;
        width: 100% !important;
        max-width: none !important;
        box-sizing: border-box;
        line-height: 1.15 !important;
    }

    html body main .messages-page .messages-wrap,
    .messages-wrap {
        flex: 1 1 0% !important;
        width: 1040px !important;
        max-width: 100% !important;
        height: 0 !important; /* take leftover space under title, then inner scroll */
        min-height: 0 !important;
        max-height: none !important;
        margin: 0 auto !important;
        background: var(--msg-bg);
        border-radius: 0;
        box-shadow: none !important;
        overflow: hidden !important;
        display: grid !important;
        box-sizing: border-box;
        grid-template-columns: 35% 65% !important;
        grid-template-rows: minmax(0, 1fr) !important;
        border: none;
        color: var(--msg-text);
    }

    /* LEFT SIDEBAR */

    .messages-sidebar {
        background: var(--msg-bg);
        border-right: 3px solid #6F6F6F;
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        max-height: 100% !important;
        min-height: 0 !important;
        min-width: 0;
        overflow: hidden !important;
        box-sizing: border-box;
        align-self: stretch;
    }

    .messages-sidebar-head {
        flex: 0 0 auto;
        padding: 6px 14px 8px;
        font-size: 20px !important;
        font-weight: 700 !important;
        color: var(--msg-text) !important;
        text-align: left;
        border-bottom: none;
        background: var(--msg-bg);
        letter-spacing: 0;
        font-family: 'Poppins', sans-serif !important;
    }

    .messages-search {
        flex: 0 0 auto;
        margin: 0 0 10px;
        /* Match thread padding so the bar starts where avatars start */
        padding: 0 12px 0 14px;
        width: 100%;
        max-width: none;
        box-sizing: border-box;
    }

    .messages-search-field {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        height: 30px;
        padding: 4px 12px;
        background: var(--msg-search) !important;
        border-radius: 10px !important;
        color: #5B6B7A;
        border: none !important;
        box-shadow: none !important;
        box-sizing: border-box;
    }

    .messages-search-field svg {
        width: 14px;
        height: 14px;
        flex: none;
        stroke: var(--msg-text);
        stroke-width: 2;
    }

    .messages-search-field input {
        width: 100%;
        border: 0 !important;
        outline: none !important;
        background: transparent !important;
        color: var(--msg-text) !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        font-family: 'Poppins', sans-serif !important;
        box-shadow: none !important;
    }

    .messages-search-field input::placeholder {
        color: #5B6B7A !important;
        opacity: 1;
        font-family: 'Poppins', sans-serif !important;
        font-size: 12px !important;
    }

    .messages-search-empty {
        padding: 18px 20px;
        color: #5B6B7A;
        font-size: 0.95rem;
    }

    .thread-list {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        /* Explicit viewport cap so the list always scrolls like WhatsApp */
        max-height: calc(100vh - var(--navbar-height, 90px) - var(--footer-height, 76px) - 150px) !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        touch-action: pan-y;
    }

    .thread-list .thread[hidden] {
        display: none !important;
    }

    .thread {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px 10px 14px;
        text-decoration: none;
        color: inherit;
        border: none !important;
        border-left: 0 !important;
        border-bottom: none;
        transition: background-color 0.12s ease;
        box-sizing: border-box;
        background: var(--msg-bg);
    }

    .thread:hover {
        background: #F3F5F7;
    }

    .thread.active {
        background: var(--msg-selected) !important;
        box-shadow: none !important;
        border-left: 0 !important;
    }

    .thread-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        flex-shrink: 0;
        overflow: hidden;
        background: var(--msg-lime);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: none;
    }

    .thread-avatar.tone-blue {
        background: var(--msg-lime);
    }

    /* Selected chat always uses the blue profile icon. */
    .thread.active .thread-avatar,
    .thread.active .thread-avatar.tone-blue,
    .thread.active .thread-avatar.tone-lime {
        background: var(--msg-blue) !important;
    }

    .thread-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thread-avatar-fallback {
        font-size: 14px;
        font-weight: 500;
        color: #fff;
        letter-spacing: 0;
    }

    .thread-body {
        flex: 1;
        min-width: 0;
    }

    .thread-row-top {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }

    .thread-name {
        font-weight: 400;
        font-size: 13px;
        color: var(--msg-text);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        letter-spacing: 0;
    }

    .thread.active .thread-name {
        font-weight: 700;
    }

    .thread-time {
        font-size: 11px;
        color: var(--msg-text);
        flex-shrink: 0;
        font-weight: 400;
    }

    .thread.active .thread-time {
        font-weight: 700;
        color: var(--msg-text);
    }

    .thread-row-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .thread-preview {
        font-size: 11px;
        color: #3D4F5F;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
        font-weight: 400;
    }

    .thread.active .thread-preview {
        font-weight: 700;
        color: var(--msg-text);
    }

    .thread-unread {
        flex-shrink: 0;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 999px;
        background: var(--msg-blue);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* RIGHT CONVERSATION PANEL */

    .messages-main {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0;
        min-height: 0 !important;
        height: auto !important;
        max-height: 100% !important;
        overflow: hidden !important;
        background: var(--msg-bg);
        align-self: stretch;
    }

    /* Header: sender avatar + name on top, leave requester info below */
    .messages-header {
        flex: 0 0 auto;
        border-bottom: 1px solid #6F6F6F;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--msg-bg);
        box-shadow: none;
        min-height: 64px;
        box-sizing: border-box;
        width: 100%;
    }

    .messages-header-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        flex-shrink: 0;
        overflow: hidden;
        background: var(--msg-lime);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .messages-header-avatar.tone-blue {
        background: var(--msg-blue);
    }

    .messages-header-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .messages-header-avatar span {
        font-size: 14px;
        font-weight: 500;
        color: #fff;
    }

    .messages-header-text {
        min-width: 0;
    }

    .messages-header-text h3 {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        color: var(--msg-text);
        letter-spacing: 0;
        line-height: 1.3;
        font-family: 'Poppins', sans-serif;
    }

    .messages-header-text .meta {
        margin-top: 4px;
        color: #5B6B7A;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.4;
        white-space: normal;
        font-family: 'Poppins', sans-serif;
    }

    /* Hide scrollbars but keep scrolling */
    .thread-list,
    .messages-stack {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .thread-list::-webkit-scrollbar,
    .messages-stack::-webkit-scrollbar {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
        background: transparent !important;
    }

    html body:has(.messages-page) main.content {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    html body:has(.messages-page) main.content::-webkit-scrollbar {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
    }

    /* MESSAGE AREA */

    .messages-body {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        padding: 10px 14px 0;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch;
        gap: 10px;
        background: var(--msg-bg);
    }

    /* Do NOT use justify-content:flex-end here -- it clips older messages and blocks scroll. */
    .messages-stack {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        max-height: calc(100vh - var(--navbar-height, 90px) - var(--footer-height, 76px) - 280px) !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        gap: 12px;
        padding-bottom: 8px;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        touch-action: pan-y;
    }

    /* Keep short threads pinned near the bottom like WhatsApp, while still allowing scroll. */
    .messages-stack::before {
        content: '';
        flex: 1 0 auto;
        min-height: 0;
    }

    .bubble-row {
        display: flex;
        width: 100%;
        align-items: flex-end;
    }

    .bubble-row.me {
        justify-content: flex-end;
    }

    .bubble-row:not(.me) {
        justify-content: flex-start;
    }

    .bubble-wrap {
        display: flex;
        flex-direction: column;
        width: max-content;
        max-width: min(28rem, 78%);
        min-width: 0;
    }

    .bubble-row.me .bubble-wrap {
        align-items: stretch;
        margin-left: auto;
    }

    .bubble-row:not(.me) .bubble-wrap {
        align-items: stretch;
        margin-right: auto;
    }

    .bubble {
        display: block;
        position: relative;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 8px 12px;
        border-radius: 20px;
        background: var(--msg-lime);
        color: var(--msg-text);
        font-size: 12px;
        font-weight: 400;
        font-family: 'Poppins', sans-serif;
        line-height: 1.35;
        overflow-wrap: anywhere;
        word-break: break-word;
        box-shadow: none;
        cursor: pointer;
        transition: outline-color 0.12s ease;
    }

    .bubble-row:not(.me) .bubble {
        border-radius: 20px 20px 20px 4px;
    }

    .bubble-row:not(.me) .bubble::after {
        content: '';
        position: absolute;
        left: -5px;
        bottom: 2px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 0 10px 8px;
        border-color: transparent transparent var(--msg-lime) transparent;
    }

    .bubble-line {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
        min-width: 0;
        max-width: 100%;
    }

    .bubble-text {
        display: block;
        min-width: 0;
        max-width: 100%;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .bubble-row.me .bubble {
        background: var(--msg-blue);
        color: #fff;
        border-radius: 10px;
    }

    .bubble-row.me .bubble::after {
        display: none;
    }

    .bubble-meta {
        align-self: flex-end;
        font-size: 10px;
        font-weight: 500;
        color: var(--msg-text);
        white-space: nowrap;
        line-height: 1.3;
        padding-top: 2px;
    }

    .bubble-row.me .bubble-meta {
        color: #fff;
    }

    .ticks.gray {
        color: rgba(255, 255, 255, 0.75);
    }

    .ticks.blue {
        color: #fff;
    }

    .bubble:has(audio) {
        min-width: 248px;
    }

    .bubble audio {
        width: 220px;
        max-width: 100%;
        height: 36px;
        margin-bottom: 4px;
        display: block;
    }

    .bubble-voice-meta {
        display: flex;
        justify-content: flex-end;
        margin-top: 4px;
        padding-top: 2px;
    }

    .bubble.is-deleted .bubble-text {
        font-style: italic;
        opacity: 0.85;
    }

    .bubble-reply {
        border-left: 3px solid #0f766e;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.06);
        max-width: 100%;
    }

    .bubble-row.me .bubble-reply {
        border-left-color: rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.16);
    }

    .bubble-reply-name {
        display: block;
        font-size: 11px;
        font-weight: 800;
        color: #0f766e;
        margin-bottom: 2px;
    }

    .bubble-row.me .bubble-reply-name {
        color: rgba(255, 255, 255, 0.95);
    }

    .bubble-reply-text {
        display: block;
        font-size: 12px;
        opacity: 0.9;
        min-width: 0;
        max-width: 100%;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .bubble:hover {
        box-shadow: none;
    }

    .bubble.is-selected {
        outline: 2px solid #a0b51b;
        outline-offset: 2px;
    }

    /* MESSAGE OPTIONS */

    .messages-options-hint {
        flex: 0 0 auto;
        margin: 0;
        padding: 8px 12px;
        border-radius: 8px;
        background: var(--msg-banner);
        color: var(--msg-text);
        font-size: 11px;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
        text-align: center;
        letter-spacing: 0;
    }

    .message-actions-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
        border-radius: 10px;
        background: #233138;
        color: #e9edef;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
    }

    .message-actions-bar[hidden] {
        display: none !important;
    }

    .message-actions-label {
        flex: 1 1 100%;
        font-size: 12px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 2px;
    }

    .message-actions-bar button {
        padding: 9px 14px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .message-actions-bar button:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    .message-actions-bar button.danger {
        background: rgba(220, 38, 38, 0.35);
    }

    .message-actions-bar button.danger:hover {
        background: rgba(220, 38, 38, 0.5);
    }

    .message-actions-bar button.close-btn {
        margin-left: auto;
        background: transparent;
        font-size: 18px;
        padding: 6px 10px;
    }

    .reply-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        margin-bottom: 8px;
        border-left: 4px solid #0f766e;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.75);
    }

    .reply-bar-text {
        flex: 1;
        min-width: 0;
    }

    .reply-bar-text strong {
        display: block;
        font-size: 12px;
        color: #0f766e;
        margin-bottom: 2px;
    }

    .reply-bar-text span {
        display: block;
        font-size: 13px;
        color: #334155;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .reply-bar-cancel {
        border: none;
        background: transparent;
        font-size: 20px;
        line-height: 1;
        color: #64748b;
        cursor: pointer;
        padding: 4px;
    }

    .forward-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 10040;
        background: rgba(2, 10, 14, 0.88);
        border: 3px solid #0f766e;
        box-shadow: inset 0 0 0 2px #0f766e;
        box-sizing: border-box;
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .forward-modal {
        width: min(420px, 100%);
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        border: none;
        outline: none;
        box-shadow: 0 28px 70px rgba(0, 0, 0, 0.58);
    }

    .forward-modal h4 {
        margin: 0 0 12px;
        font-size: 17px;
        color: #0a1347;
    }

    .forward-modal select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font: inherit;
        margin-bottom: 14px;
    }

    .forward-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .forward-modal-actions button {
        border-radius: 8px;
        padding: 8px 14px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid #d1d5db;
        background: #fff;
    }

    .forward-modal-actions button.primary {
        background: #d4e21a;
        border-color: #c3d010;
        color: #fff;
    }

    .messages-compose {
        display: block;
        flex: 0 0 auto;
        width: 100%;
        box-sizing: border-box;
        border-top: none;
        padding: 6px 10px 10px;
        background: var(--msg-bg);
        position: relative;
        z-index: 2;
    }

    .compose-form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .compose-form textarea {
        flex: 1;
        min-width: 0;
        min-height: 36px;
        max-height: 100px;
        resize: none;
        padding: 8px 14px;
        border-radius: 999px;
        border: none;
        background: var(--msg-input) !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 12px;
        color: var(--msg-text);
        outline: none;
        box-shadow: none !important;
        box-sizing: border-box;
        text-align: left;
    }

    .compose-form textarea::placeholder {
        color: #5B6B7A;
        opacity: 1;
        text-align: center;
    }

    .compose-form textarea:focus {
        border-color: transparent;
        box-shadow: none;
    }

    .compose-form button[type="submit"] {
        width: 36px !important;
        min-width: 36px !important;
        max-width: 36px !important;
        height: 36px !important;
        padding: 0 !important;
        border-radius: 50% !important;
        border: none !important;
        flex-shrink: 0;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        line-height: 1;
        background: var(--msg-lime) !important;
        color: #fff !important;
        cursor: pointer;
        box-shadow: none !important;
    }

    .compose-form button[type="submit"]:hover {
        background: #b8d100 !important;
    }

    .compose-voice-btn {
        width: auto;
        min-width: 52px;
        height: 34px;
        padding: 0 10px;
        border-radius: 999px;
        border: none;
        background: var(--msg-voice);
        color: var(--msg-lime);
        font-size: 11px;
        font-weight: 700;
        font-family: 'Poppins', sans-serif !important;
        letter-spacing: 0;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
        box-shadow: none;
    }

    .compose-voice-btn.is-recording {
        background: #dc2626;
        border-color: #b91c1c;
        color: #fff;
        animation: voice-pulse 1s ease-in-out infinite;
    }

    .voice-status {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #0f766e;
    }

    .voice-status.is-recording {
        color: #dc2626;
    }

    @keyframes voice-pulse {
        0%, 100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.06);
        }
    }

    .compose-error {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #b91c1c;
    }

    .messages-sidebar .messages-empty {
        padding: 48px 20px;
        text-align: center;
        color: #555;
        font-weight: 600;
        font-size: 13px;
    }

    .messages-body .messages-empty {
        padding: 48px 20px;
        text-align: center;
        color: #0f766e;
        font-weight: 600;
        font-size: 13px;
    }

    .messages-empty-pane {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #555;
        font-size: 13px;
        font-weight: 600;
    }

    .mobile-back {
        display: none;
        padding: 8px 10px;
        border-radius: 8px;
        border: none;
        background: #e7f1f2;
        color: #273548;
        font-weight: 800;
        font-size: 19px;
        text-decoration: none;
        line-height: 1;
    }

    /* Keep Messages layout identical on all screen sizes (no mobile restyle). */
    @media (max-width: 1200px) {
        html body .messages-page .messages-page-title {
            font-size: 31px !important;
            color: #1A2B3A !important;
            margin-left: 0 !important;
            text-align: left !important;
        }

        html body .messages-page .messages-wrap {
            width: min(100%, 1040px) !important;
            max-width: 1040px !important;
            height: 0 !important;
            min-height: 0 !important;
            grid-template-columns: 35% 65% !important;
            grid-template-rows: minmax(0, 1fr) !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        html body .messages-page .thread-list {
            max-height: calc(100vh - var(--navbar-height, 90px) - var(--footer-height, 76px) - 150px) !important;
            overflow-y: auto !important;
            min-height: 0 !important;
        }

        html body .messages-page .messages-search {
            max-width: none !important;
            margin: 0 0 10px !important;
            padding: 0 12px 0 14px !important;
        }

        html body .messages-page .messages-sidebar,
        html body .messages-page .messages-main {
            display: flex !important;
        }

        html body .messages-page .messages-search-field {
            width: 100% !important;
            height: 30px !important;
            border-radius: 10px !important;
            background: #D3DCE3 !important;
        }
    }
</style>


@php
    $isAdmin = (auth()->user()->role ?? null) === 'admin';
    $meId = auth()->id();

    $threadTime = function ($date) {
        if (!$date) {
            return '';
        }
        $c = \Carbon\Carbon::parse($date);
        if ($c->isToday()) {
            return $c->format('H:i');
        }
        if ($c->isYesterday()) {
            return 'Yesterday';
        }

        return $c->format('d M Y');
    };

    $userInitials = function ($user) {
        if (!$user) {
            return '?';
        }
        $a = strtoupper(substr((string) ($user->first_name ?? ''), 0, 1));
        $b = strtoupper(substr((string) ($user->last_name ?? ''), 0, 1));
        $init = trim($a . $b);
        return $init !== '' ? $init : '?';
    };

    $userDisplayName = function ($user, $fallback = 'User') {
        if (!$user) {
            return $fallback;
        }
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name !== '' ? $name : $fallback;
    };

    // Chat is manager <-> senior manager. Leave applicant is only for leave info.
    $chatPeer = function ($conversation) use ($isAdmin) {
        if ($isAdmin) {
            return $conversation->manager;
        }

        return $conversation->admin ?: $conversation->manager?->manager;
    };
@endphp

<div class="messages-page">
<h1 class="messages-page-title">Messages</h1>

<div class="messages-wrap {{ $activeConversation ? 'is-mobile-thread' : '' }}">
    <aside class="messages-sidebar" aria-label="Chats">
        <div class="messages-sidebar-head">Chats</div>
        <form class="messages-search" role="search" action="{{ route('messages.index') }}" method="GET">
            @if($activeConversation)
                <input type="hidden" name="conversation" value="{{ $activeConversation->id }}">
            @endif
            <label class="messages-search-field">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input type="search" id="messagesSearch" name="search" placeholder="Search conversations..." aria-label="Search conversations" autocomplete="off">
            </label>
        </form>
        <div class="thread-list">
            @forelse($conversations as $c)
                @php
                    $peer = $chatPeer($c);
                    $listName = $userDisplayName($peer, $isAdmin ? 'Manager' : 'Senior Manager');
                    $applicant = $c->leaveRequest?->user;
                    $lastMessage = $c->messages->first();
                    if ($lastMessage) {
                        if ($lastMessage->deleted_for_everyone_at) {
                            $label = 'This message was deleted';
                        } elseif (!empty($lastMessage->voice_path)) {
                            $label = 'Voice message';
                        } else {
                            $label = $lastMessage->body;
                        }
                        $preview = ($lastMessage->sender_id === $meId ? 'You: ' : '') . $label;
                    } else {
                        $preview = 'No messages yet';
                    }
                    $unreadCount = (int) ($c->unread_count ?? 0);
                    $avatarTone = ((int) ($peer->id ?? 0) % 2 === 0) ? 'tone-blue' : 'tone-lime';
                    $isActiveThread = (int) ($activeConversation?->id) === (int) $c->id
                        || (
                            $activeConversation
                            && (int) ($activeConversation->leaveRequest?->user_id ?? 0) > 0
                            && (int) ($activeConversation->leaveRequest?->user_id) === (int) ($applicant->id ?? 0)
                        );
                @endphp
                <a class="thread {{ $isActiveThread ? 'active' : '' }} {{ $unreadCount > 0 ? 'has-unread' : '' }}"
                   href="{{ route('messages.index', ['conversation' => $c->id]) }}">
                    <div class="thread-avatar {{ $avatarTone }}" aria-hidden="true">
                        @if(!empty($peer?->profile_picture))
                            <img src="{{ route('users.profile_picture', ['user' => $peer->id]) }}?v={{ optional($peer->updated_at)->timestamp }}" alt="">
                        @else
                            <span class="thread-avatar-fallback">{{ $userInitials($peer) }}</span>
                        @endif
                    </div>
                    <div class="thread-body">
                        <div class="thread-row-top">
                            <span class="thread-name">{{ $listName }}</span>
                            <span class="thread-time">{{ $threadTime($lastMessage?->created_at ?? $c->updated_at) }}</span>
                        </div>
                        <div class="thread-row-bottom">
                            <span class="thread-preview">{{ $preview }}</span>
                            @if($unreadCount > 0)
                                <span class="thread-unread">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="messages-empty">No chats yet</div>
            @endforelse
            <div class="messages-search-empty" hidden>No conversations found</div>
        </div>
    </aside>

    <section class="messages-main" aria-label="Conversation">
        @if(!$activeConversation)
            <div class="messages-empty-pane">Select a chat to view messages</div>
        @else
            @php
                $peer = $chatPeer($activeConversation);
                $peerName = $userDisplayName($peer, $isAdmin ? 'Manager' : 'Senior Manager');
                $peerTone = ((int) ($peer->id ?? 0) % 2 === 0) ? 'tone-blue' : 'tone-lime';
                $employee = $activeConversation->leaveRequest?->user;
                $employeeName = $userDisplayName($employee, 'Employee');
                $leave = $activeConversation->leaveRequest;
            @endphp
            <header class="messages-header" aria-label="Conversation">
                <a class="mobile-back" href="{{ route('messages.index') }}" aria-label="Back">&larr;</a>
                <div class="messages-header-avatar {{ $peerTone }}" aria-hidden="true">
                    @if(!empty($peer?->profile_picture))
                        <img src="{{ route('users.profile_picture', ['user' => $peer->id]) }}?v={{ optional($peer->updated_at)->timestamp }}" alt="">
                    @else
                        <span>{{ $userInitials($peer) }}</span>
                    @endif
                </div>
                <div class="messages-header-text">
                    <h3>{{ $peerName }}</h3>
                    <div class="meta">
                        Leave #{{ $activeConversation->leave_request_id }} &middot; {{ $employeeName }} &middot; {{ $leave?->leave_type ?? 'Leave' }}
                        @if($leave?->start_date && $leave?->end_date) &middot; {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        @endif
                        @if($leave?->status) &middot; {{ ucfirst($leave->status) }}
                        @endif
                    </div>
                </div>
            </header>

            <div class="messages-body" id="messagesBody">
                @if($activeMessages->isNotEmpty())
                    <p class="messages-options-hint">Click any message &mdash; options appear below</p>
                @endif
                <div class="messages-stack">
                @forelse($activeMessages as $m)
                    @php
                        $isMe = $m->sender_id === $meId;
                        $isDeleted = $m->isDeletedForEveryone();
                        $replySender = $m->replyTo?->sender;
                        $replySenderName = $replySender
                            ? (trim(($replySender->first_name ?? '') . ' ' . ($replySender->last_name ?? '')) ?: 'User')
                            : 'User';
                        $copyText = $m->copyableText();
                        $senderName = trim(($m->sender->first_name ?? '') . ' ' . ($m->sender->last_name ?? '')) ?: 'User';
                    @endphp
                    <div class="bubble-row {{ $isMe ? 'me' : '' }}">
                        <div class="bubble-wrap">
                            <div class="bubble {{ $isDeleted ? 'is-deleted' : '' }}"
                                 data-message-id="{{ $m->id }}"
                                 data-copy-text="{{ $copyText }}"
                                 data-is-mine="{{ $isMe ? '1' : '0' }}"
                                 data-is-deleted="{{ $isDeleted ? '1' : '0' }}"
                                 data-sender-name="{{ $senderName }}"
                                 title="Click for message options">
                            @if($m->replyTo)
                                <div class="bubble-reply">
                                    <span class="bubble-reply-name">{{ $replySenderName }}</span>
                                    <span class="bubble-reply-text">{{ $m->replyTo->displayBody() }}</span>
                                </div>
                            @endif
                            @if(!empty($m->voice_path) && !$isDeleted)
                                <audio controls preload="metadata" src="{{ route('messages.voice', ['message' => $m->id]) }}"></audio>
                                @if(trim((string) $m->body) !== '' && strcasecmp(trim((string) $m->body), 'Voice message') !== 0)
                                    <div class="bubble-line">
                                        <span class="bubble-text">{{ $m->body }}</span>
                                    </div>
                                @endif
                                @if ($isMe)
                                    <div class="bubble-voice-meta">
                                        <span class="bubble-meta">{{ optional($m->created_at)->format('H:i') }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="bubble-line">
                                    <span class="bubble-text">{{ $m->displayBody() }}</span>
                                    @if ($isMe)
                                        <span class="bubble-meta">{{ optional($m->created_at)->format('H:i') }}</span>
                                    @endif
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="messages-empty">No messages yet</div>
                @endforelse
                </div>
            </div>

            <div class="messages-compose">
                <div class="message-actions-bar" id="messageActionsBar" hidden>
                    <span class="message-actions-label">Message options</span>
                    <button type="button" data-action="reply">Reply</button>
                    <button type="button" data-action="copy">Copy</button>
                    <button type="button" data-action="forward">Forward</button>
                    <button type="button" data-action="delete-me" class="danger">Delete for me</button>
                    <button type="button" data-action="delete-everyone" class="danger" hidden>Delete for everyone</button>
                    <button type="button" class="close-btn" id="clearMessageSelection" aria-label="Close">?</button>
                </div>
                <div class="reply-bar" id="replyBar" hidden>
                    <div class="reply-bar-text">
                        <strong id="replyBarName"></strong>
                        <span id="replyBarPreview"></span>
                    </div>
                    <button type="button" class="reply-bar-cancel" id="cancelReply" aria-label="Cancel reply">�</button>
                </div>
                <form class="compose-form" id="composeForm" method="POST" action="{{ route('messages.send', ['conversation' => $activeConversation->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="reply_to_message_id" id="replyToInput" value="">
                    <input type="file" name="voice" id="voiceInput" accept="audio/*" hidden>
                    <button type="button" class="compose-voice-btn" id="voiceRecordBtn" aria-label="Record voice note" title="Tap to record a voice note"><span id="voiceRecordLabel">Voice</span></button>
                    <textarea name="body" id="composeBody" placeholder="Type a message.." rows="1">{{ old('body') }}</textarea>
                    <button type="submit" aria-label="Send" title="Send">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 2L11 13"></path>
                            <path d="M22 2L15 22l-4-9-9-4 20-7z"></path>
                        </svg>
                    </button>
                </form>
                <div class="voice-status" id="voiceStatus" hidden></div>
                @error('body')
                    <div class="compose-error">{{ $message }}</div>
                @enderror
                @error('voice')
                    <div class="compose-error">{{ $message }}</div>
                @enderror
            </div>
        @endif
    </section>
</div>
</div>

@if($activeConversation)
    <form id="deleteMessageForm" method="POST" action="" hidden>
        @csrf
        @method('DELETE')
        <input type="hidden" name="scope" id="deleteScopeInput" value="me">
    </form>

    <div id="forwardModalBackdrop" class="forward-modal-backdrop" hidden>
        <div class="forward-modal" role="dialog" aria-labelledby="forwardModalTitle">
            <h4 id="forwardModalTitle">Forward message</h4>
            <form id="forwardForm" method="POST" action="">
                @csrf
                <select name="target_conversation_id" id="forwardTargetSelect" required>
                    <option value="">Choose a chat</option>
                    @foreach($forwardConversations as $fc)
                        @php
                            $fcOther = $chatPeer($fc);
                            $fcOtherName = $userDisplayName($fcOther, $isAdmin ? 'Manager' : 'Senior Manager');
                            $fcEmployeeName = $userDisplayName($fc->leaveRequest?->user, 'Employee');
                        @endphp
                        <option value="{{ $fc->id }}">{{ $fcOtherName }} &middot; Leave #{{ $fc->leave_request_id }} &middot; {{ $fcEmployeeName }}</option>
                    @endforeach
                </select>
                <div class="forward-modal-actions">
                    <button type="button" id="forwardCancelBtn">Cancel</button>
                    <button type="submit" class="primary">Forward</button>
                </div>
            </form>
            @if($forwardConversations->isEmpty())
                <p style="margin:0;font-size:0.88rem;color:#64748b;">You have no other chats to forward to.</p>
            @endif
        </div>
    </div>
@endif

<script>
        const messagesSearch = document.getElementById('messagesSearch');
        const messageThreads = document.querySelectorAll('.thread-list .thread');
        const messagesSearchEmpty = document.querySelector('.messages-search-empty');
        messagesSearch?.addEventListener('input', () => {
            const query = messagesSearch.value.trim().toLowerCase();
            let shown = 0;
            messageThreads.forEach((thread) => {
                const matches = query === '' || thread.textContent.toLowerCase().includes(query);
                thread.hidden = !matches;
                if (matches) shown += 1;
            });
            if (messagesSearchEmpty) {
                messagesSearchEmpty.hidden = query === '' || shown > 0;
            }
        });
        messagesSearch?.form?.addEventListener('submit', (event) => {
            event.preventDefault();
        });
    document.addEventListener('DOMContentLoaded', () => {
        const box = document.querySelector('.messages-stack') || document.getElementById('messagesBody');
        if (box) box.scrollTop = box.scrollHeight;

        const form = document.getElementById('composeForm');
        const voiceBtn = document.getElementById('voiceRecordBtn');
        const voiceInput = document.getElementById('voiceInput');
        const voiceStatus = document.getElementById('voiceStatus');
        const voiceRecordLabel = document.getElementById('voiceRecordLabel');
        const messageActionsBar = document.getElementById('messageActionsBar');
        const deleteMessageForm = document.getElementById('deleteMessageForm');
        const forwardModalBackdrop = document.getElementById('forwardModalBackdrop');
        const forwardForm = document.getElementById('forwardForm');
        const replyBar = document.getElementById('replyBar');
        const replyBarName = document.getElementById('replyBarName');
        const replyBarPreview = document.getElementById('replyBarPreview');
        const replyToInput = document.getElementById('replyToInput');
        const cancelReply = document.getElementById('cancelReply');
        const composeBody = document.getElementById('composeBody');
        const clearMessageSelection = document.getElementById('clearMessageSelection');

        let activeBubble = null;

        const closeActions = () => {
            if (messageActionsBar) messageActionsBar.hidden = true;
            document.querySelectorAll('.bubble.is-selected').forEach((el) => el.classList.remove('is-selected'));
            activeBubble = null;
        };

        const openActions = (bubble) => {
            if (!messageActionsBar || !bubble) return;
            document.querySelectorAll('.bubble.is-selected').forEach((el) => el.classList.remove('is-selected'));
            activeBubble = bubble;
            bubble.classList.add('is-selected');

            const isMine = bubble.dataset.isMine === '1';
            const isDeleted = bubble.dataset.isDeleted === '1';
            const deleteEveryoneBtn = messageActionsBar.querySelector('[data-action="delete-everyone"]');
            const forwardBtn = messageActionsBar.querySelector('[data-action="forward"]');
            const copyBtn = messageActionsBar.querySelector('[data-action="copy"]');
            const replyBtn = messageActionsBar.querySelector('[data-action="reply"]');

            if (deleteEveryoneBtn) deleteEveryoneBtn.hidden = !isMine || isDeleted;
            if (forwardBtn) forwardBtn.hidden = isDeleted;
            if (copyBtn) copyBtn.hidden = isDeleted || !bubble.dataset.copyText;
            if (replyBtn) replyBtn.hidden = isDeleted;

            messageActionsBar.hidden = false;
            messageActionsBar.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        };

        const clearReply = () => {
            if (replyBar) replyBar.hidden = true;
            if (replyToInput) replyToInput.value = '';
            if (replyBarName) replyBarName.textContent = '';
            if (replyBarPreview) replyBarPreview.textContent = '';
        };

        const startReply = (bubble) => {
            if (!bubble || bubble.dataset.isDeleted === '1') return;
            const preview = bubble.querySelector('.bubble-text')?.textContent?.trim()
                || (bubble.querySelector('audio') ? 'Voice message' : '');
            if (replyBar) replyBar.hidden = false;
            if (replyBarName) replyBarName.textContent = bubble.dataset.senderName || 'User';
            if (replyBarPreview) replyBarPreview.textContent = preview;
            if (replyToInput) replyToInput.value = bubble.dataset.messageId || '';
            composeBody?.focus();
            closeActions();
        };

        document.querySelectorAll('.bubble').forEach((bubble) => {
            bubble.addEventListener('click', (event) => {
                if (event.target.closest('audio')) return;
                event.preventDefault();
                if (activeBubble === bubble && !messageActionsBar?.hidden) {
                    closeActions();
                    return;
                }
                openActions(bubble);
            });

            bubble.addEventListener('contextmenu', (event) => {
                if (event.target.closest('audio')) return;
                event.preventDefault();
                openActions(bubble);
            });
        });

        messageActionsBar?.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button || !activeBubble) return;

            const action = button.dataset.action;
            const messageId = activeBubble.dataset.messageId;
            const copyText = activeBubble.dataset.copyText || '';

            if (action === 'reply') {
                startReply(activeBubble);
                return;
            }

            if (action === 'copy') {
                try {
                    await navigator.clipboard.writeText(copyText);
                } catch (error) {
                    const helper = document.createElement('textarea');
                    helper.value = copyText;
                    document.body.appendChild(helper);
                    helper.select();
                    document.execCommand('copy');
                    helper.remove();
                }
                closeActions();
                return;
            }

            if (action === 'forward') {
                if (forwardForm && forwardModalBackdrop) {
                    forwardForm.action = `{{ url('/messages') }}/${messageId}/forward`;
                    forwardModalBackdrop.hidden = false;
                }
                closeActions();
                return;
            }

            if (action === 'delete-me' || action === 'delete-everyone') {
                if (deleteMessageForm) {
                    deleteMessageForm.action = `{{ url('/messages') }}/${messageId}`;
                    const scopeInput = document.getElementById('deleteScopeInput');
                    if (scopeInput) {
                        scopeInput.value = action === 'delete-everyone' ? 'everyone' : 'me';
                    }
                    deleteMessageForm.submit();
                }
                closeActions();
            }
        });

        clearMessageSelection?.addEventListener('click', closeActions);

        cancelReply?.addEventListener('click', clearReply);

        document.getElementById('forwardCancelBtn')?.addEventListener('click', () => {
            if (forwardModalBackdrop) forwardModalBackdrop.hidden = true;
        });

        forwardModalBackdrop?.addEventListener('click', (event) => {
            if (event.target === forwardModalBackdrop) {
                forwardModalBackdrop.hidden = true;
            }
        });

        if (!form || !voiceBtn || !voiceInput) return;

        let mediaRecorder = null;
        let mediaStream = null;
        let chunks = [];
        let isRecording = false;

        const stopStream = () => {
            if (mediaStream) {
                mediaStream.getTracks().forEach((track) => track.stop());
                mediaStream = null;
            }
        };

        voiceBtn.addEventListener('click', async () => {
            if (isRecording) {
                mediaRecorder?.stop();
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia) {
                alert('Voice notes are not supported in this browser.');
                return;
            }

            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                    ? 'audio/webm;codecs=opus'
                    : (MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : '');

                mediaRecorder = mimeType
                    ? new MediaRecorder(mediaStream, { mimeType })
                    : new MediaRecorder(mediaStream);

                chunks = [];
                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        chunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    stopStream();
                    voiceBtn.classList.remove('is-recording');
                    if (voiceRecordLabel) voiceRecordLabel.textContent = 'Voice';
                    voiceStatus.hidden = true;
                    voiceStatus.classList.remove('is-recording');
                    isRecording = false;

                    if (!chunks.length) {
                        return;
                    }

                    const type = mediaRecorder.mimeType || 'audio/webm';
                    const blob = new Blob(chunks, { type });
                    const ext = type.includes('ogg') ? 'ogg' : 'webm';
                    const file = new File([blob], `voice-note.${ext}`, { type });

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    voiceInput.files = transfer.files;
                    form.submit();
                };

                mediaRecorder.start();
                isRecording = true;
                voiceBtn.classList.add('is-recording');
                if (voiceRecordLabel) voiceRecordLabel.textContent = 'Stop';
                voiceStatus.hidden = false;
                voiceStatus.classList.add('is-recording');
                voiceStatus.textContent = 'Recording&hellip; Tap the microphone again to stop and send';
            } catch (error) {
                stopStream();
                alert('Microphone access was denied or unavailable.');
            }
        });
    });
</script>
@endsection
