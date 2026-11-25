@php
    $reference = file_get_contents(base_path('docs/references/itflow-prototype.html'));
    $workspace = substr($reference, strpos($reference, '<!-- ==================== MAIN APP WORKSPACE'), strpos($reference, '<!-- ==================== JAVASCRIPT LOGIC') - strpos($reference, '<!-- ==================== MAIN APP WORKSPACE'));
    $prototypeScript = substr($reference, strpos($reference, '// Role-based PIC visibility helper:'), strpos($reference, '    </script>', strpos($reference, '// Role-based PIC visibility helper:')) - strpos($reference, '// Role-based PIC visibility helper:'));
    $prototypeScript = preg_replace('/        function quickFillAccount\(nip, pass\).*?        function applyAuthenticatedUserUI\(user\)/s', '        function applyAuthenticatedUserUI(user)', $prototypeScript);
    $prototypeScript = str_replace('Math.max(Date.now(), new Date(2026, 10, 21, 23, 59).getTime())', 'Date.now()', $prototypeScript);
    $prototypeScript = str_replace("selectedTimeFilter === '1d' && diffDays > 1.5", "selectedTimeFilter === '1d' && diffDays > 1", $prototypeScript);
    $prototypeScript = preg_replace('/        function exportRecapToExcel\(periodKey = .*?        \/\/ Initialize icons on page load/s', "        function exportRecapToExcel(periodKey, boardScope) { confirmExportExcel(); }\n\n        // Initialize icons on page load", $prototypeScript);
    $prototypeStyle = substr($reference, strpos($reference, '<style>'), strpos($reference, '</style>') + 8 - strpos($reference, '<style>'));
@endphp
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IT SNSac — Internal IT Kanban & Task Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('itflow/board.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>tailwind.config = {darkMode: 'class', theme: {extend: {fontFamily: {sans: ['Plus Jakarta Sans', 'sans-serif'], mono: ['JetBrains Mono', 'monospace']}, colors: {brand: {50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 900: '#1e3a8a', 950: '#172554'}}}}};</script>
    {!! $prototypeStyle !!}

    {{-- ================================================================
         IT SNSac VISUAL THEME — Refined, Balanced & Full Dark Mode Support
    ================================================================= --}}
    <style>
        /* ═══════════════════════════════════════════════════════════════
           THEME TOKENS (LIGHT & DARK)
        ═══════════════════════════════════════════════════════════════ */
        :root {
            --sns-sidebar-bg: #ffffff;
            --sns-sidebar-brand: #ffffff;
            --sns-sidebar-border: #e2e8f0;
            --sns-sidebar-text: #334155;
            --sns-sidebar-text-active: #ffffff;
            --sns-sidebar-hover: #f1f5f9;
            --sns-sidebar-active: #2563eb;

            --sns-main-bg: #f8fafc;
            --sns-header-bg: #ffffff;
            --sns-header-border: #e2e8f0;
            --sns-toolbar-bg: #ffffff;
            --sns-toolbar-border: #e2e8f0;

            --sns-col-bg: #f1f5f9;
            --sns-col-border: #e2e8f0;
            --sns-col-header-border: #e2e8f0;
            --sns-col-title: #334155;

            --sns-card-bg: #ffffff;
            --sns-card-border: #e2e8f0;
            --sns-card-border-hover: #cbd5e1;
            --sns-card-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            --sns-card-shadow-hover: 0 6px 16px -2px rgba(15, 23, 42, 0.08), 0 2px 4px -1px rgba(15, 23, 42, 0.04);

            --sns-input-bg: #f8fafc;
            --sns-input-border: #e2e8f0;
            --sns-input-focus-border: #3b82f6;

            --sns-modal-bg: #ffffff;
            --sns-modal-border: #e2e8f0;
            --sns-modal-header-bg: #f8fafc;
            --sns-backdrop: rgba(15, 23, 42, 0.45);
        }

        html.dark {
            --sns-sidebar-bg: #080d1a;
            --sns-sidebar-brand: #050811;
            --sns-sidebar-border: rgba(255, 255, 255, 0.04);
            --sns-sidebar-text: #64748b;
            --sns-sidebar-text-active: #f1f5f9;
            --sns-sidebar-hover: rgba(255, 255, 255, 0.04);
            --sns-sidebar-active: #1d4ed8;

            --sns-main-bg: #090d16;
            --sns-header-bg: #0f172a;
            --sns-header-border: #1e293b;
            --sns-toolbar-bg: #0f172a;
            --sns-toolbar-border: #1e293b;

            --sns-col-bg: #0f172a;
            --sns-col-border: #1e293b;
            --sns-col-header-border: #1e293b;
            --sns-col-title: #94a3b8;

            --sns-card-bg: #182234;
            --sns-card-border: #223049;
            --sns-card-border-hover: #3b82f6;
            --sns-card-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
            --sns-card-shadow-hover: 0 8px 20px -3px rgba(0, 0, 0, 0.45);

            --sns-input-bg: #090d16;
            --sns-input-border: #1e293b;
            --sns-input-focus-border: #60a5fa;

            --sns-modal-bg: #0f172a;
            --sns-modal-border: #1e293b;
            --sns-modal-header-bg: #141f36;
            --sns-backdrop: rgba(0, 0, 0, 0.7);
        }

        /* Sidebar: keep selection and disclosure as separate controls. */
        #appSidebar {
            background-color: var(--sns-sidebar-bg) !important;
            border-right: 1px solid var(--sns-sidebar-border) !important;
            box-shadow: 2px 0 12px rgba(15, 23, 42, 0.03) !important;
        }
        html.dark #appSidebar {
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.12) !important;
        }

        #appSidebar .h-14 {
            background-color: var(--sns-sidebar-brand) !important;
            border-bottom: 1px solid var(--sns-sidebar-border) !important;
        }

        #appSidebar .font-bold.text-base,
        #appSidebar .font-bold.text-base * {
            color: #0f172a !important;
            letter-spacing: -0.015em !important;
        }
        #appSidebar .font-bold.text-base .text-brand-600 { color: #2563eb !important; }
        html.dark #appSidebar .font-bold.text-base,
        html.dark #appSidebar .font-bold.text-base * { color: #ffffff !important; }
        html.dark #appSidebar .font-bold.text-base .text-brand-600 { color: #60a5fa !important; }

        #appSidebar .block.text-\[10px\],
        #appSidebar [class*="text-\[10px\]"][class*="uppercase"] {
            color: #64748b !important;
            letter-spacing: 0.08em !important;
        }
        html.dark #appSidebar .block.text-\[10px\],
        html.dark #appSidebar [class*="text-\[10px\]"][class*="uppercase"] {
            color: rgba(148, 163, 184, 0.6) !important;
        }

        /* + Project button */
        #appSidebar button[onclick="openNewBoardModal()"] {
            background-color: #eef2ff !important;
            border: 1px solid #c7d2fe !important;
            color: #3730a3 !important;
            border-radius: 10px !important;
            transition: all 0.15s ease !important;
        }
        #appSidebar button[onclick="openNewBoardModal()"]:hover {
            background-color: #e0e7ff !important;
            color: #312e81 !important;
            border-color: #a5b4fc !important;
        }
        html.dark #appSidebar button[onclick="openNewBoardModal()"] {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: rgba(226, 232, 240, 0.85) !important;
        }
        html.dark #appSidebar button[onclick="openNewBoardModal()"]:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        /* Nav "Semua Board IT" */
        #navAllBoardsBtn {
            background-color: transparent !important;
            color: var(--sns-sidebar-text) !important;
            border-radius: 10px !important;
            transition: all 0.15s ease !important;
        }
        #navAllBoardsBtn:hover {
            background-color: var(--sns-sidebar-hover) !important;
            color: #0f172a !important;
        }
        #navAllBoardsBtn.active-scope {
            background-color: var(--sns-sidebar-active) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35) !important;
        }
        #navAllBoardsBtn[class*="bg-brand-600"] {
            background-color: var(--sns-sidebar-active) !important;
            color: #ffffff !important;
        }
        #navAllBoardsBtn[class*="bg-brand-600"]:hover { background-color: #1d4ed8 !important; color: #ffffff !important; }
        html.dark #navAllBoardsBtn { background-color: rgba(255, 255, 255, 0.04) !important; color: rgba(226, 232, 240, 0.85) !important; }
        html.dark #navAllBoardsBtn:hover { color: #ffffff !important; }
        html.dark #navAllBoardsBtn[class*="bg-brand-600"] { background-color: var(--sns-sidebar-active) !important; color: #ffffff !important; }

        #sidebarProjectTree [onclick^="selectSidebarScope"],
        #sidebarProjectTree button[onclick^="toggleBoardDropdown"] {
            border-radius: 9px !important;
            transition: all 0.15s ease !important;
        }
        #sidebarProjectTree [onclick^="selectSidebarScope"] {
            color: var(--sns-sidebar-text) !important;
        }
        #sidebarProjectTree [onclick^="selectSidebarScope"]:hover {
            background-color: var(--sns-sidebar-hover) !important;
            color: #0f172a !important;
        }
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] {
            background-color: var(--sns-sidebar-active) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3) !important;
        }
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]:hover {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
        }
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-50"] {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            box-shadow: none !important;
        }
        #sidebarProjectTree button[onclick^="selectSidebarScope"][class*="bg-emerald-600"] {
            background-color: #059669 !important;
            color: #ffffff !important;
        }
        #sidebarProjectTree button[onclick^="selectSidebarScope"][class*="bg-emerald-600"]:hover {
            background-color: #047857 !important;
            color: #ffffff !important;
        }
        #sidebarProjectTree button[onclick^="toggleBoardDropdown"] {
            color: inherit !important;
        }
        #sidebarProjectTree button[onclick^="toggleBoardDropdown"]:hover {
            background-color: rgba(37, 99, 235, 0.12) !important;
        }
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"] { color: rgba(203, 213, 225, 0.75) !important; }
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"]:hover { color: #ffffff !important; }
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] { color: #ffffff !important; }
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-50"] {
            background-color: rgba(37, 99, 235, 0.16) !important;
            color: #93c5fd !important;
        }
        html.dark #sidebarProjectTree button[onclick^="selectSidebarScope"][class*="bg-emerald-600"] { color: #ffffff !important; }

        #sidebarProjectTree .sns-subproject-row { display: flex; align-items: center; gap: 2px; }
        #sidebarProjectTree .sns-subproject-row > button:first-child { flex: 1; min-width: 0; }
        #sidebarProjectTree .sns-sidebar-delete {
            display: inline-grid; place-items: center; flex: 0 0 auto;
            width: 22px; height: 22px; padding: 0; border-radius: 6px;
            color: #94a3b8 !important; background: transparent !important;
            font-size: 16px; line-height: 1;
        }
        #sidebarProjectTree .sns-sidebar-delete:hover,
        #sidebarProjectTree .sns-sidebar-delete:focus-visible {
            color: #be123c !important; background: #fff1f2 !important;
        }
        html.dark #sidebarProjectTree .sns-sidebar-delete:hover,
        html.dark #sidebarProjectTree .sns-sidebar-delete:focus-visible {
            color: #fda4af !important; background: #4c0519 !important;
        }

        #appSidebar ::-webkit-scrollbar { width: 3px; }
        #appSidebar ::-webkit-scrollbar-track { background: transparent; }
        #appSidebar ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }
        html.dark #appSidebar ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); }

        /* ═══════════════════════════════════════════════════════════════
           MAIN WRAPPER & TOP NAVBAR
        ═══════════════════════════════════════════════════════════════ */
        body,
        #mainAppContainer,
        .flex-1.flex.flex-col.min-w-0 {
            background-color: var(--sns-main-bg) !important;
        }

        header.h-14 {
            background-color: var(--sns-header-bg) !important;
            border-bottom: 1px solid var(--sns-header-border) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           FILTER TOOLBAR
        ═══════════════════════════════════════════════════════════════ */
        .shrink-0.bg-white\/80,
        div[class*="bg-white/80"][class*="border-b"],
        div[class*="dark:bg-slate-900/80"][class*="border-b"] {
            background-color: var(--sns-toolbar-bg) !important;
            border-bottom: 1px solid var(--sns-toolbar-border) !important;
        }

        #searchInput {
            background-color: var(--sns-input-bg) !important;
            border-color: var(--sns-input-border) !important;
            border-radius: 10px !important;
            transition: all 0.15s ease !important;
        }
        #searchInput:focus {
            border-color: var(--sns-input-focus-border) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
        }

        /* Filter pill pill containers */
        div[class*="bg-slate-100"][class*="p-1"][class*="rounded-xl"] {
            background-color: var(--sns-col-bg) !important;
            border-color: var(--sns-col-border) !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           KANBAN COLUMNS (CLEAN, SOPHISTICATED, NOT CLOWNISH)
        ═══════════════════════════════════════════════════════════════ */
        main .grid > div[class*="rounded-2xl"] {
            background-color: var(--sns-col-bg) !important;
            border: 1px solid var(--sns-col-border) !important;
            border-radius: 16px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
        }

        main .grid > div[class*="rounded-2xl"] > div:first-child {
            background-color: transparent !important;
            border-bottom: 1px solid var(--sns-col-header-border) !important;
        }

        main .grid > div[class*="rounded-2xl"] h2 {
            color: var(--sns-col-title) !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
        }

        /* Status indicators - Subtle & professional */
        /* To Do */
        div:has(> div > #col-todo) span.w-2\.5 {
            background-color: #94a3b8 !important;
            box-shadow: none !important;
        }
        #count-todo {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: inherit !important;
            font-weight: 700 !important;
        }

        /* In Progress */
        div:has(> div > #col-in_progress) span.w-2\.5 {
            background-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        }
        #count-in_progress {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #3b82f6 !important;
            font-weight: 700 !important;
        }

        /* Done */
        div:has(> div > #col-done) span.w-2\.5 {
            background-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
        }
        [id^="count-done"] {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #10b981 !important;
            font-weight: 700 !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           TASK CARDS (CLEAN ELEVATED SURFACE)
        ═══════════════════════════════════════════════════════════════ */
        .kanban-task-card,
        [class*="kanban-task-card"] {
            background-color: var(--sns-card-bg) !important;
            border: 1px solid var(--sns-card-border) !important;
            border-radius: 12px !important;
            box-shadow: var(--sns-card-shadow) !important;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease !important;
            cursor: pointer !important;
        }

        .kanban-task-card:hover {
            border-color: var(--sns-card-border-hover) !important;
            box-shadow: var(--sns-card-shadow-hover) !important;
            transform: translateY(-2px) !important;
        }

        .kanban-task-card:active {
            transform: translateY(0) scale(0.99) !important;
        }

        /* Done tasks slightly dimmed to reduce visual clutter */
        #col-done .kanban-task-card {
            opacity: 0.85;
        }
        #col-done .kanban-task-card:hover {
            opacity: 1;
        }

        /* Sortable ghost / drag */
        .sortable-ghost .kanban-task-card {
            opacity: 0.35 !important;
            border: 2px dashed #3b82f6 !important;
            box-shadow: none !important;
        }
        .sortable-drag .kanban-task-card {
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.22) !important;
            transform: rotate(1.5deg) scale(1.02) !important;
            border-color: #3b82f6 !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           MODAL DIALOGS
        ═══════════════════════════════════════════════════════════════ */
        [class*="fixed"][class*="inset-0"][class*="bg-black"] {
            background-color: var(--sns-backdrop) !important;
            backdrop-filter: blur(4px) !important;
        }

        [class*="bg-white"][class*="rounded-2xl"][class*="shadow-2xl"],
        html.dark [class*="bg-slate-900"][class*="rounded-2xl"][class*="shadow-2xl"] {
            background-color: var(--sns-modal-bg) !important;
            border: 1px solid var(--sns-modal-border) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35) !important;
            border-radius: 18px !important;
        }

        [class*="bg-white"][class*="rounded-2xl"] > div:first-child[class*="border-b"],
        html.dark [class*="bg-slate-900"][class*="rounded-2xl"] > div:first-child[class*="border-b"] {
            background-color: var(--sns-modal-header-bg) !important;
            border-bottom: 1px solid var(--sns-modal-border) !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           SCROLLBARS
        ═══════════════════════════════════════════════════════════════ */
        .kanban-column::-webkit-scrollbar,
        .kanban-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .kanban-column::-webkit-scrollbar-track,
        .kanban-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .kanban-column::-webkit-scrollbar-thumb,
        .kanban-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.25);
            border-radius: 99px;
        }
        .kanban-column::-webkit-scrollbar-thumb:hover,
        .kanban-scroll::-webkit-scrollbar-thumb:hover {
            background-color: rgba(148, 163, 184, 0.45);
        }

        /* Focus outline */
        :focus-visible {
            outline: 2px solid #3b82f6 !important;
            outline-offset: 2px !important;
        }
    </style>

    {{-- ================================================================
         SNS FLUENT LAYER — glass/mica, reveal highlight, aurora, motion
         Ditaruh setelah tema di atas, jadi hanya menimpa/menambah.
         Hapus blok <style id="sns-fluent"> + 2 script di bawah untuk kembali ke tampilan awal.
    ================================================================= --}}
    <script>
        document.documentElement.classList.add('sns-boot');
        setTimeout(function () { document.documentElement.classList.remove('sns-boot'); }, 1800);
    </script>
    <style id="sns-fluent">
        /* ═══ 1. TOKEN: permukaan kaca (acrylic) ═══ */
        :root {
            --sns-ease: cubic-bezier(.2, .8, .2, 1);
            --sns-spring: cubic-bezier(.34, 1.56, .64, 1);
            --sns-header-bg: rgba(255, 255, 255, .72);
            --sns-toolbar-bg: rgba(255, 255, 255, .6);
            --sns-col-bg: rgba(241, 245, 249, .62);
            --sns-col-border: rgba(203, 213, 225, .75);
            --sns-card-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 2px 6px -2px rgba(15, 23, 42, .06), inset 0 1px 0 rgba(255, 255, 255, .95);
            --sns-card-shadow-hover: 0 18px 32px -14px rgba(37, 99, 235, .38), 0 4px 10px -4px rgba(15, 23, 42, .1), inset 0 1px 0 rgba(255, 255, 255, .95);
            --sns-spot: rgba(59, 130, 246, .16);
        }
        html.dark {
            --sns-header-bg: rgba(15, 23, 42, .68);
            --sns-toolbar-bg: rgba(15, 23, 42, .55);
            --sns-col-bg: rgba(15, 23, 42, .58);
            --sns-col-border: rgba(51, 65, 85, .6);
            --sns-card-shadow: 0 1px 3px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .05);
            --sns-card-shadow-hover: 0 18px 34px -12px rgba(0, 0, 0, .65), 0 0 0 1px rgba(96, 165, 250, .35), 0 0 28px -6px rgba(59, 130, 246, .35), inset 0 1px 0 rgba(255, 255, 255, .07);
            --sns-spot: rgba(96, 165, 250, .2);
        }

        /* ═══ 2. LATAR: aurora bergerak + grid titik yang "menyala" di dekat kursor ═══ */
        #mainAppContainer,
        .flex-1.flex.flex-col.min-w-0 { background-color: transparent !important; }
        html { scroll-behavior: smooth; }

        body::before {
            content: "";
            position: fixed;
            inset: -25%;
            z-index: -2;
            pointer-events: none;
            background:
                radial-gradient(40% 36% at 18% 24%, rgba(59, 130, 246, .17), transparent 70%),
                radial-gradient(34% 34% at 82% 18%, rgba(139, 92, 246, .13), transparent 70%),
                radial-gradient(40% 40% at 64% 86%, rgba(6, 182, 212, .12), transparent 70%);
            animation: sns-aurora 30s ease-in-out infinite alternate;
            will-change: transform;
        }
        html.dark body::before {
            background:
                radial-gradient(40% 36% at 18% 24%, rgba(37, 99, 235, .26), transparent 70%),
                radial-gradient(34% 34% at 82% 18%, rgba(124, 58, 237, .2), transparent 70%),
                radial-gradient(40% 40% at 64% 86%, rgba(8, 145, 178, .16), transparent 70%);
        }
        @keyframes sns-aurora { to { transform: translate3d(4%, 3%, 0) rotate(6deg) scale(1.08); } }

        body::after {   /* aura mengikuti kursor */
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background: radial-gradient(560px circle at var(--px, 72%) var(--py, 8%), rgba(59, 130, 246, .09), transparent 65%);
        }
        html::before {  /* grid titik, hanya terlihat di sekitar kursor */
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background-image: radial-gradient(circle, rgba(71, 85, 105, .35) 1px, transparent 1.6px);
            background-size: 24px 24px;
            -webkit-mask-image: radial-gradient(380px circle at var(--px, 72%) var(--py, 8%), #000, transparent 70%);
            mask-image: radial-gradient(380px circle at var(--px, 72%) var(--py, 8%), #000, transparent 70%);
            opacity: .55;
        }
        html.dark::before { background-image: radial-gradient(circle, rgba(148, 163, 184, .4) 1px, transparent 1.6px); }

        /* ═══ 3. HEADER & TOOLBAR: kaca + garis cahaya bergerak ═══ */
        header.h-14 { backdrop-filter: blur(16px) saturate(1.5); -webkit-backdrop-filter: blur(16px) saturate(1.5); }
        header.h-14:not(.sticky):not(.fixed):not(.absolute):not(.relative) { position: relative; }
        header.h-14::after {
            content: "";
            position: absolute;
            left: 0; right: 0; bottom: -1px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, #8b5cf6, #06b6d4, transparent);
            background-size: 220% 100%;
            opacity: .75;
            pointer-events: none;
            animation: sns-flow 7s linear infinite;
        }
        @keyframes sns-flow { from { background-position: 120% 0; } to { background-position: -120% 0; } }

        div[class*="bg-white/80"][class*="border-b"],
        div[class*="dark:bg-slate-900/80"][class*="border-b"] { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }

        #searchInput { transition: border-color .2s, box-shadow .25s, background-color .2s, scale .25s var(--sns-ease) !important; }
        #searchInput:focus { scale: 1.015; box-shadow: 0 0 0 4px rgba(59, 130, 246, .16), 0 10px 24px -12px rgba(59, 130, 246, .5) !important; }

        div[class*="bg-slate-100"][class*="p-1"][class*="rounded-xl"] { backdrop-filter: blur(8px); box-shadow: inset 0 1px 2px rgba(15, 23, 42, .05); }
        div[class*="bg-slate-100"][class*="p-1"][class*="rounded-xl"] button { transition: all .2s var(--sns-ease); }

        /* Tombol: efek tekan + kilau lewat saat hover */
        header button, main button { transition: scale .14s var(--sns-ease), background-color .2s, box-shadow .25s, border-color .2s, color .2s; }
        header button:active, main button:active { scale: .96; }
        header button[class*="bg-brand-"], main button[class*="bg-brand-"] {
            background-image: linear-gradient(115deg, transparent 35%, rgba(255, 255, 255, .38) 50%, transparent 65%);
            background-size: 260% 100%;
            background-position: 130% 0;
            background-repeat: no-repeat;
            transition: background-position .75s var(--sns-ease), scale .14s var(--sns-ease), box-shadow .25s;
        }
        header button[class*="bg-brand-"]:hover, main button[class*="bg-brand-"]:hover {
            background-position: -30% 0;
            box-shadow: 0 10px 22px -8px rgba(37, 99, 235, .6);
        }

        /* ═══ 4. SIDEBAR: gradien, logo hidup, item aktif menyala, reveal highlight ═══ */
        #appSidebar { background-image: linear-gradient(180deg, rgba(59, 130, 246, .07), transparent 260px); }
        html.dark #appSidebar { background-image: linear-gradient(180deg, rgba(59, 130, 246, .12), transparent 280px); }

        #appSidebar .h-14 img { transition: rotate .45s var(--sns-spring), scale .45s var(--sns-spring); filter: drop-shadow(0 3px 6px rgba(37, 99, 235, .28)); }
        #appSidebar .h-14:hover img { rotate: -8deg; scale: 1.1; }
        @supports (background-clip: text) or (-webkit-background-clip: text) {
            #appSidebar .font-bold.text-base {
                background: linear-gradient(90deg, #0f172a, #2563eb 70%);
                -webkit-background-clip: text; background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            html.dark #appSidebar .font-bold.text-base { background-image: linear-gradient(90deg, #fff, #93c5fd 75%); }
        }

        #navAllBoardsBtn.active-scope,
        #navAllBoardsBtn[class*="bg-brand-600"],
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] {
            background-image: linear-gradient(135deg, #3b82f6, #2563eb 55%, #4f46e5) !important;
            box-shadow: 0 8px 18px -6px rgba(37, 99, 235, .6), inset 0 1px 0 rgba(255, 255, 255, .28) !important;
        }

        /* Reveal highlight (Fluent): lingkaran cahaya mengikuti kursor di item */
        #navAllBoardsBtn:not(.active-scope):not([class*="bg-brand-600"]):hover,
        #sidebarProjectTree [onclick^="selectSidebarScope"]:not([class*="bg-brand-600"]):hover,
        #appSidebar button[onclick="openNewBoardModal()"]:hover {
            background-image: radial-gradient(130px circle at var(--mx, 50%) var(--my, 50%), var(--sns-spot), transparent 75%) !important;
        }
        #navAllBoardsBtn svg:first-child { transition: transform .25s var(--sns-spring); }
        #navAllBoardsBtn:hover svg:first-child { transform: translateX(2px) scale(1.12) rotate(-4deg); }
        #sidebarProjectTree [onclick^="selectSidebarScope"]:not([class*="bg-brand-600"]):hover { transform: translateX(3px); }
        #appSidebar button[onclick="openNewBoardModal()"]:hover { transform: translateY(-1px); box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .45) !important; }

        /* ═══ 5. KOLOM: kaca, aksen status bercahaya, highlight saat drag ═══ */
        main .grid > div[class*="rounded-2xl"] {
            --col-accent: #94a3b8;
            position: relative;
            backdrop-filter: blur(14px) saturate(1.35);
            -webkit-backdrop-filter: blur(14px) saturate(1.35);
            transition: border-color .25s, box-shadow .3s, background-color .25s !important;
        }
        main .grid > div[class*="rounded-2xl"]:hover { box-shadow: 0 14px 34px -18px rgba(15, 23, 42, .3) !important; }
        div:has(> div > #col-in_progress) { --col-accent: #3b82f6 !important; }
        div:has(> div > #col-done)        { --col-accent: #10b981 !important; }
        main .grid > div[class*="rounded-2xl"]::before {
            content: "";
            position: absolute;
            top: -1px; left: 14px; right: 14px;
            height: 3px;
            border-radius: 0 0 4px 4px;
            background: linear-gradient(90deg, transparent, var(--col-accent), transparent);
            box-shadow: 0 3px 16px var(--col-accent);
            opacity: .9;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, var(--col-accent), transparent) !important;
        }
        /* kolom tujuan menyala saat kartu diseret ke atasnya */
        main .grid > div[class*="rounded-2xl"]:has(.sortable-ghost) {
            border-color: color-mix(in srgb, var(--col-accent) 65%, transparent) !important;
            background-color: color-mix(in srgb, var(--col-accent) 8%, var(--sns-col-bg)) !important;
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--col-accent) 14%, transparent), 0 24px 44px -22px var(--col-accent) !important;
        }

        main .grid > div[class*="rounded-2xl"] h2 { transition: letter-spacing .3s var(--sns-ease); }
        main .grid > div[class*="rounded-2xl"]:hover h2 { letter-spacing: .08em !important; }

        div:has(> div > #col-in_progress) span.w-2\.5,
        div:has(> div > #col-todo) span.w-2\.5,
        div:has(> div > #col-done) span.w-2\.5 { position: relative; }
        div:has(> div > #col-in_progress) span.w-2\.5::after {
            content: ""; position: absolute; inset: 0; border-radius: inherit; background: #3b82f6;
            animation: sns-ping 2.4s ease-out infinite; pointer-events: none;
        }
        @keyframes sns-ping { 0% { transform: scale(1); opacity: .6; } 70%, 100% { transform: scale(3); opacity: 0; } }

        [id^="count-"] { font-variant-numeric: tabular-nums; display: inline-block; }
        [id^="count-"].sns-bump { animation: sns-bump .45s var(--sns-spring); }
        @keyframes sns-bump { 0% { transform: scale(1); } 40% { transform: scale(1.35); } 100% { transform: scale(1); } }

        /* ═══ 6. KARTU TUGAS: reveal, aksen, kedalaman, tint status ═══ */
        .kanban-task-card { position: relative; transition: transform .25s var(--sns-ease), box-shadow .3s, border-color .2s !important; }
        .kanban-task-card:hover { transform: translateY(-4px) !important; }
        .kanban-task-card:active { transform: translateY(-1px) scale(.985) !important; }
        .kanban-task-card::before {
            content: ""; position: absolute; left: -1px; top: 12px; bottom: 12px; width: 4px;
            border-radius: 0 4px 4px 0;
            background: linear-gradient(180deg, #60a5fa, #6366f1);
            box-shadow: 0 0 12px rgba(96, 165, 250, .7);
            opacity: 0; transform: scaleY(.2);
            transition: transform .3s var(--sns-spring), opacity .2s; pointer-events: none;
        }
        .kanban-task-card:hover::before { opacity: 1; transform: scaleY(1); }
        .kanban-task-card::after {   /* reveal highlight */
            content: ""; position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            background: radial-gradient(190px circle at var(--mx, 50%) var(--my, 50%), var(--sns-spot), transparent 72%);
            opacity: 0; transition: opacity .3s;
        }
        .kanban-task-card:hover::after { opacity: 1; }
        #col-in_progress .kanban-task-card { background-image: linear-gradient(135deg, rgba(59, 130, 246, .06), transparent 45%); }
        #col-done .kanban-task-card { background-image: linear-gradient(135deg, rgba(16, 185, 129, .08), transparent 45%); }

        .sortable-chosen .kanban-task-card { box-shadow: 0 0 0 2px rgba(59, 130, 246, .5) !important; }
        .sortable-drag .kanban-task-card {
            box-shadow: 0 28px 50px -12px rgba(0, 0, 0, .35), 0 0 36px -4px rgba(59, 130, 246, .55) !important;
        }
        .sortable-ghost .kanban-task-card { animation: sns-ghost 1.2s ease-in-out infinite alternate; }
        .sortable-ghost .kanban-task-card::before, .sortable-ghost .kanban-task-card::after,
        .sortable-drag .kanban-task-card::before { opacity: 0 !important; }
        @keyframes sns-ghost { to { border-color: rgba(99, 102, 241, .9); } }

        @keyframes sns-rise {
            from { opacity: 0; translate: 0 14px; scale: .97; filter: blur(4px); }
        }
        .kanban-task-card.sns-in { animation: sns-rise .5s var(--sns-ease) backwards; animation-delay: calc(var(--i, 0) * 45ms); }

        /* ═══ 7. MODAL: kaca + pop dengan pegas ═══ */
        @keyframes sns-fade { from { opacity: 0; } }
        @keyframes sns-pop  { from { opacity: 0; translate: 0 18px; scale: .94; } }
        [class*="fixed"][class*="inset-0"][class*="bg-black"] { backdrop-filter: blur(8px) saturate(1.2) !important; animation: sns-fade .2s ease backwards; }
        [class*="bg-white"][class*="rounded-2xl"][class*="shadow-2xl"],
        html.dark [class*="bg-slate-900"][class*="rounded-2xl"][class*="shadow-2xl"] {
            animation: sns-pop .38s var(--sns-spring) backwards;
            background-image: linear-gradient(180deg, rgba(59, 130, 246, .07), transparent 140px);
        }
        dialog.board-modal[open] { animation: sns-pop .34s var(--sns-spring) backwards; }
        dialog.board-modal[open]::backdrop { backdrop-filter: blur(8px); animation: sns-fade .2s ease backwards; }
        [data-modal-close] { transition: rotate .3s var(--sns-spring), background-color .2s; }
        [data-modal-close]:hover { rotate: 90deg; }

        :is(input, select, textarea) { transition: border-color .2s, box-shadow .25s, background-color .2s; }
        :is(input, select, textarea):focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, .15); }

        /* ═══ 8. ANIMASI MASUK HALAMAN (sekali saat dibuka) ═══ */
        @keyframes sns-slide-x { from { opacity: 0; translate: -16px 0; } }
        @keyframes sns-drop    { from { opacity: 0; translate: 0 -12px; } }
        .sns-boot #appSidebar { animation: sns-slide-x .6s var(--sns-ease) backwards; }
        .sns-boot header.h-14 { animation: sns-drop .6s var(--sns-ease) .08s backwards; }
        .sns-boot main .grid > div[class*="rounded-2xl"] { animation: sns-rise .7s var(--sns-ease) backwards; }
        .sns-boot main .grid > div:nth-child(1) { animation-delay: .15s; }
        .sns-boot main .grid > div:nth-child(2) { animation-delay: .26s; }
        .sns-boot main .grid > div:nth-child(3) { animation-delay: .37s; }
        .sns-boot main .grid > div:nth-child(4) { animation-delay: .48s; }
        .sns-boot .kanban-task-card.sns-in { animation-delay: calc(.4s + var(--i, 0) * 45ms); }

        /* Transisi halus saat ganti light/dark */
        html.sns-theme-fade *, html.sns-theme-fade *::before, html.sns-theme-fade *::after {
            transition: background-color .4s, border-color .4s, color .3s, box-shadow .4s !important;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; }
            .kanban-task-card, header button, main button { transition: none !important; }
            html { scroll-behavior: auto; }
        }
    </style>

    {{-- ================================================================
         SNS CHROME — penyempurnaan sidebar & navbar (menimpa Fluent layer di atasnya)
    ================================================================= --}}
    <style id="sns-chrome">
        /* ═══ NAVBAR ═══ */
        #appSidebar .h-14, header.h-14 { height: 64px !important; }   /* sejajar & lebih lega */
        header.h-14 {
            padding-inline: 1.25rem;
            box-shadow: 0 1px 0 var(--sns-header-border), 0 14px 34px -22px rgba(37, 99, 235, .5) !important;
            transition: box-shadow .3s ease;
        }
        html.sns-scrolled header.h-14 { box-shadow: 0 1px 0 var(--sns-header-border), 0 18px 36px -16px rgba(15, 23, 42, .3) !important; }

        header.h-14::before {   /* kilau halus lewat tiap beberapa detik */
            content: "";
            position: absolute; inset: 0;
            pointer-events: none;
            background: linear-gradient(105deg, transparent 40%, rgba(96, 165, 250, .16) 50%, transparent 60%);
            background-size: 300% 100%;
            background-position: 150% 0;
            animation: sns-sheen 10s ease-in-out infinite;
        }
        @keyframes sns-sheen { 0%, 62% { background-position: 150% 0; } 100% { background-position: -50% 0; } }

        @supports (background-clip: text) or (-webkit-background-clip: text) {
            header.h-14 :is(h1, h2) {
                font-weight: 800; letter-spacing: -.015em;
                background: linear-gradient(90deg, #0f172a, #1d4ed8 80%);
                -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            }
            html.dark header.h-14 :is(h1, h2) { background-image: linear-gradient(90deg, #fff, #93c5fd 85%); }
        }

        /* Tombol aksi: kaca bulat yang menyala saat di-hover */
        header.h-14 button:not([class*="bg-brand-"]):not([class*="bg-emerald-"]) { border-radius: 12px; }
        header.h-14 button:not([class*="bg-brand-"]):not([class*="bg-emerald-"]):hover {
            background-color: rgba(59, 130, 246, .1);
            box-shadow: 0 0 0 1px rgba(59, 130, 246, .24), 0 10px 20px -12px rgba(37, 99, 235, .65);
            translate: 0 -1px;
        }
        header.h-14 button svg { transition: scale .25s var(--sns-spring), rotate .35s var(--sns-spring); }
        header.h-14 button:hover svg { scale: 1.14; }
        header.h-14 button[onclick*="heme"]:hover svg,
        header.h-14 button[onclick*="ark"]:hover svg { rotate: 28deg; }

        /* Avatar / inisial user: cincin gradasi */
        header.h-14 [class*="rounded-full"][class*="w-8"]:not(button),
        header.h-14 [class*="rounded-full"][class*="w-9"]:not(button) {
            box-shadow: 0 0 0 2px var(--sns-header-bg), 0 0 0 4px rgba(99, 102, 241, .4), 0 6px 14px -4px rgba(79, 70, 229, .5);
            transition: scale .25s var(--sns-spring), box-shadow .25s;
        }
        header.h-14 [class*="rounded-full"][class*="w-8"]:not(button):hover,
        header.h-14 [class*="rounded-full"][class*="w-9"]:not(button):hover { scale: 1.08; }

        /* ═══ SIDEBAR ═══ */
        #appSidebar {   /* tepi kanan bergradasi */
            border-image: linear-gradient(180deg, transparent, rgba(59, 130, 246, .55) 28%, rgba(139, 92, 246, .45) 72%, transparent) 1 !important;
        }

        #appSidebar .h-14 div:has(> img) { animation: sns-breathe 4.5s ease-in-out infinite; }
        @keyframes sns-breathe {
            0%, 100% { filter: drop-shadow(0 0 3px rgba(59, 130, 246, .25)); }
            50%      { filter: drop-shadow(0 0 12px rgba(59, 130, 246, .6)); }
        }

        /* Label seksi dengan garis pemisah memudar */
        #appSidebar .block.text-\[10px\],
        #appSidebar [class*="text-\[10px\]"][class*="uppercase"] {
            display: flex !important; align-items: center; gap: .6rem; font-weight: 800 !important;
        }
        #appSidebar .block.text-\[10px\]::after,
        #appSidebar [class*="text-\[10px\]"][class*="uppercase"]::after {
            content: ""; flex: 1; height: 1px;
            background: linear-gradient(90deg, rgba(148, 163, 184, .4), transparent);
        }

        /* Item: lebih lega, tebal, dan punya bar penanda saat aktif */
        #navAllBoardsBtn,
        #sidebarProjectTree [onclick^="selectSidebarScope"] { position: relative; font-weight: 600; min-height: 38px; }
        #navAllBoardsBtn { border-radius: 12px !important; }
        #navAllBoardsBtn.active-scope::before,
        #navAllBoardsBtn[class*="bg-brand-600"]::before,
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]::before,
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-emerald-600"]::before {
            content: ""; position: absolute; left: 5px; top: 50%; width: 3px; height: 52%;
            border-radius: 3px; translate: 0 -50%;
            background: rgba(255, 255, 255, .95); box-shadow: 0 0 10px rgba(255, 255, 255, .85);
            animation: sns-bar .45s var(--sns-spring) backwards;
        }
        @keyframes sns-bar { from { scale: 1 0; opacity: 0; } }

        #navAllBoardsBtn:active,
        #sidebarProjectTree [onclick^="selectSidebarScope"]:not([class*="bg-brand-600"]):active {
            background-image: radial-gradient(220px circle at var(--mx, 50%) var(--my, 50%), rgba(59, 130, 246, .32), transparent 70%) !important;
        }

        /* Garis pemandu pohon sub-project */
        #sidebarProjectTree .sns-subproject-row { position: relative; }
        #sidebarProjectTree .sns-subproject-row::before {
            content: ""; position: absolute; left: -7px; top: 0; bottom: 0;
            border-left: 1px dashed rgba(148, 163, 184, .4);
        }
        #sidebarProjectTree .sns-subproject-row::after {
            content: ""; position: absolute; left: -7px; top: 50%; width: 6px;
            border-top: 1px dashed rgba(148, 163, 184, .4);
        }

        /* Area scroll memudar di ujung atas/bawah */
        #appSidebar .overflow-y-auto {
            -webkit-mask-image: linear-gradient(to bottom, transparent 0, #000 10px, #000 calc(100% - 18px), transparent 100%);
            mask-image: linear-gradient(to bottom, transparent 0, #000 10px, #000 calc(100% - 18px), transparent 100%);
        }

        /* Kartu status di dasar sidebar (jam WIB live) */
        .sns-side-foot {
            display: flex; align-items: center; gap: .7rem;
            margin: .5rem .75rem .85rem; padding: .65rem .85rem;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(59, 130, 246, .1), rgba(139, 92, 246, .08));
            border: 1px solid rgba(99, 102, 241, .2);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .5);
            font-size: 11.5px; line-height: 1.3; color: #64748b;
            animation: sns-rise .6s var(--sns-ease) .7s backwards;
        }
        .sns-side-foot b { display: block; font-size: 14px; font-weight: 800; color: #0f172a; font-variant-numeric: tabular-nums; letter-spacing: -.01em; }
        html.dark .sns-side-foot { color: #94a3b8; box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06); }
        html.dark .sns-side-foot b { color: #f1f5f9; }
        .sns-dot { flex: none; width: 9px; height: 9px; border-radius: 50%; background: #10b981; animation: sns-live 2.2s ease-out infinite; }
        @keyframes sns-live { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, .55); } 70%, 100% { box-shadow: 0 0 0 9px rgba(16, 185, 129, 0); } }
    </style>

    {{-- SNS FIX — perbaikan hasil screenshot: ikon item aktif tak terlihat, bar terlalu tebal, glow terlalu besar, logo gelap di dark mode --}}
    <style id="sns-fix">
        /* 1) Ikon "Semua Board IT": dulu hampir menyatu dengan latar biru → paksa putih penuh + beri tile kaca */
        #navAllBoardsBtn svg:first-child {
            box-sizing: content-box;
            padding: 4px;
            border-radius: 8px;
            background: rgba(59, 130, 246, .1);
            transition: transform .25s var(--sns-spring), background-color .2s;
        }
        html.dark #navAllBoardsBtn svg:first-child { background: rgba(148, 163, 184, .12); }

        #navAllBoardsBtn.active-scope svg,
        #navAllBoardsBtn[class*="bg-brand-600"] svg,
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] svg,
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-emerald-600"] svg {
            color: #fff !important;
            opacity: 1 !important;
        }
        #navAllBoardsBtn.active-scope svg:first-child,
        #navAllBoardsBtn[class*="bg-brand-600"] svg:first-child {
            background: rgba(255, 255, 255, .2) !important;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
        }

        /* 2) Bar penanda aktif: lebih pendek & tipis, tidak menempel ke ikon */
        #navAllBoardsBtn.active-scope::before,
        #navAllBoardsBtn[class*="bg-brand-600"]::before,
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]::before,
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-emerald-600"]::before {
            left: 4px; height: 36%; width: 3px;
            box-shadow: 0 0 6px rgba(255, 255, 255, .6);
        }

        /* 3) Glow item aktif dikecilkan (sebelumnya melebar dan tampak kotor) */
        #navAllBoardsBtn.active-scope,
        #navAllBoardsBtn[class*="bg-brand-600"],
        #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] {
            box-shadow: 0 6px 12px -8px rgba(37, 99, 235, .65), inset 0 1px 0 rgba(255, 255, 255, .22) !important;
        }

        /* 4) Logo: tile kaca di belakang + terangkan warna biru gelapnya di dark mode */
        #appSidebar .h-14 div:has(> img) { position: relative; isolation: isolate; }
        #appSidebar .h-14 div:has(> img)::before {
            content: "";
            position: absolute; inset: -5px; z-index: -1;
            border-radius: 14px;
            background: linear-gradient(135deg, #fff, #eef4ff);
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, .2), 0 6px 14px -8px rgba(37, 99, 235, .5);
        }
        html.dark #appSidebar .h-14 div:has(> img)::before {
            background: linear-gradient(135deg, rgba(255, 255, 255, .1), rgba(255, 255, 255, .03));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12), 0 8px 18px -10px rgba(59, 130, 246, .7);
        }
        html.dark #appSidebar .h-14 img { filter: brightness(1.55) saturate(1.2) drop-shadow(0 0 8px rgba(96, 165, 250, .5)); }

        /* 5) Subjudul "INTERNAL IT": kontras lebih baik */
        html.dark #appSidebar .h-14 [class*="uppercase"] { color: #9fb0cf !important; letter-spacing: .12em; font-weight: 700; }

        /* 6) Tombol + Project: ikon ikut hidup saat di-hover */
        #appSidebar button[onclick="openNewBoardModal()"] svg { transition: scale .25s var(--sns-spring), rotate .3s var(--sns-spring); }
        #appSidebar button[onclick="openNewBoardModal()"]:hover svg { scale: 1.15; rotate: -6deg; }
    </style>

    {{-- SNS SOFT — melembutkan kontras sidebar di dark mode (item aktif jadi kaca berwarna, brand menyatu dengan sidebar) --}}
    <style id="sns-soft">
        /* Sidebar sedikit lebih terang & brand tidak lagi band hitam */
        html.dark #appSidebar { background-color: #0c1426 !important; }
        html.dark #appSidebar .h-14 {
            background-color: transparent !important;
            border-bottom: 1px solid rgba(148, 163, 184, .1) !important;
        }
        html.dark #appSidebar .h-14 img { filter: brightness(1.25) saturate(1.1) drop-shadow(0 0 6px rgba(96, 165, 250, .35)); }
        html.dark #appSidebar .h-14 div:has(> img)::before {
            background: rgba(148, 163, 184, .08);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .14);
        }

        /* Item aktif: kaca biru lembut + garis tipis, bukan blok biru solid */
        html.dark #navAllBoardsBtn.active-scope,
        html.dark #navAllBoardsBtn[class*="bg-brand-600"],
        html.dark #navAllBoardsBtn[class*="bg-brand-600"]:hover,
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"],
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]:hover {
            background-color: rgba(59, 130, 246, .12) !important;
            background-image: linear-gradient(135deg, rgba(59, 130, 246, .26), rgba(99, 102, 241, .12)) !important;
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, .28) !important;
            color: #dbeafe !important;
        }
        html.dark #navAllBoardsBtn[class*="bg-brand-600"]:hover,
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]:hover {
            background-color: rgba(59, 130, 246, .18) !important;
        }
        html.dark #navAllBoardsBtn.active-scope svg,
        html.dark #navAllBoardsBtn[class*="bg-brand-600"] svg,
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] svg { color: #93c5fd !important; }
        html.dark #navAllBoardsBtn.active-scope svg:first-child,
        html.dark #navAllBoardsBtn[class*="bg-brand-600"] svg:first-child {
            background: rgba(96, 165, 250, .16) !important;
            box-shadow: none;
        }
        html.dark #navAllBoardsBtn.active-scope::before,
        html.dark #navAllBoardsBtn[class*="bg-brand-600"]::before,
        html.dark #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]::before {
            background: #60a5fa;
            box-shadow: 0 0 8px rgba(96, 165, 250, .6);
        }

        /* Tombol + Project: teks & garis tidak terlalu terang */
        html.dark #appSidebar button[onclick="openNewBoardModal()"] {
            color: #c4cfe3 !important;
            border-color: rgba(148, 163, 184, .18) !important;
            background-color: rgba(255, 255, 255, .035) !important;
        }
        html.dark #appSidebar button[onclick="openNewBoardModal()"]:hover {
            color: #fff !important;
            border-color: rgba(96, 165, 250, .4) !important;
        }
    </style>

    {{-- ================================================================
         SNS LIGHT — tema mode terang disamakan dengan halaman login
         (navy #16255c, aksen #3d55c9, garis #c9d5f2, latar biru muda).
         SEMUA aturan diawali html:not(.dark) → mode gelap tidak tersentuh.
         Hapus blok <style id="sns-light"> ini untuk kembali ke light lama.
    ================================================================= --}}
    <style id="sns-light">
        /* ═══ 1. TOKEN: lapisan jelas → latar < kolom < kartu ═══ */
        html:not(.dark) {
            --sns-sidebar-bg: #c6defe;
            --sns-sidebar-border: #b4c3ea;
            --sns-sidebar-text: #3a4a7a;
            --sns-sidebar-hover: rgba(255, 255, 255, .72);
            --sns-sidebar-active: #3d55c9;

            --sns-main-bg: #dbe9ff;
            --sns-header-bg: rgba(255, 255, 255, .94);
            --sns-header-border: #c9d5f2;
            --sns-toolbar-bg: rgba(255, 255, 255, .84);
            --sns-toolbar-border: #c9d5f2;

            --sns-col-bg: rgba(184, 207, 248, .96);
            --sns-col-border: #8fb0ec;
            --sns-col-header-border: #8fb0ec;
            --sns-col-title: #1f3277;

            --sns-card-bg: #ffffff;
            --sns-card-border: #b0c6f0;
            --sns-card-border-hover: #7f93dc;
            --sns-card-shadow: 0 1px 2px rgba(22, 37, 92, .10), 0 6px 12px -6px rgba(50, 90, 160, .25), inset 0 1px 0 #fff;
            --sns-card-shadow-hover: 0 18px 32px -14px rgba(61, 85, 201, .45), 0 4px 10px -4px rgba(22, 37, 92, .14), inset 0 1px 0 #fff;
            --sns-spot: rgba(61, 85, 201, .12);

            --sns-input-bg: #f1f5ff;
            --sns-input-border: #c9d5f2;
            --sns-input-focus-border: #3d55c9;

            --sns-modal-bg: #ffffff;
            --sns-modal-border: #b4c3ea;
            --sns-modal-header-bg: #eef4ff;
            --sns-backdrop: rgba(22, 37, 92, .5);
        }

        /* ═══ 2. LATAR HALAMAN: gradien + blob seperti login ═══ */
        html:not(.dark) body {
            background-image: linear-gradient(135deg, #d6e7ff 0%, #eef5ff 45%, #cfe6ff 100%);
            background-attachment: fixed;
        }
        html:not(.dark) body::before {
            background:
                radial-gradient(40% 36% at 14% 18%, rgba(110, 155, 232, .5), transparent 70%),
                radial-gradient(34% 34% at 88% 12%, rgba(90, 150, 230, .4), transparent 70%),
                radial-gradient(40% 40% at 66% 90%, rgba(120, 200, 245, .55), transparent 70%);
        }
        html:not(.dark)::before { background-image: radial-gradient(circle, rgba(61, 85, 201, .3) 1px, transparent 1.6px); }

        /* ═══ 3. SIDEBAR: panel biru muda seperti sisi kiri login ═══ */
        html:not(.dark) #appSidebar {
            background-color: #c6defe !important;
            background-image: linear-gradient(160deg, #c6defe 0%, #b6d4fd 50%, #aee3fd 100%) !important;
            box-shadow: 4px 0 18px -8px rgba(50, 90, 160, .3) !important;
        }
        html:not(.dark) #appSidebar .h-14 {
            background-color: rgba(255, 255, 255, .35) !important;
            border-bottom: 1px solid rgba(61, 85, 201, .18) !important;
        }
        @supports (background-clip: text) or (-webkit-background-clip: text) {
            html:not(.dark) #appSidebar .font-bold.text-base { background-image: linear-gradient(90deg, #16255c, #3d55c9 80%); }
            html:not(.dark) header.h-14 :is(h1, h2) { background-image: linear-gradient(90deg, #16255c, #3d55c9 85%); }
        }
        html:not(.dark) #appSidebar .block.text-\[10px\],
        html:not(.dark) #appSidebar [class*="text-\[10px\]"][class*="uppercase"] { color: #4a5a8c !important; }

        /* item biasa */
        html:not(.dark) #navAllBoardsBtn:hover,
        html:not(.dark) #sidebarProjectTree [onclick^="selectSidebarScope"]:hover { color: #16255c !important; }
        html:not(.dark) #navAllBoardsBtn svg:first-child { background: rgba(255, 255, 255, .7); }

        /* item aktif: biru aksen login */
        html:not(.dark) #navAllBoardsBtn.active-scope,
        html:not(.dark) #navAllBoardsBtn[class*="bg-brand-600"],
        html:not(.dark) #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"] {
            background-color: #3d55c9 !important;
            background-image: linear-gradient(180deg, #4a63d6, #3d55c9) !important;
            box-shadow: 0 6px 14px -8px rgba(61, 85, 201, .75), inset 0 1px 0 rgba(255, 255, 255, .22) !important;
            color: #fff !important;
        }
        html:not(.dark) #navAllBoardsBtn[class*="bg-brand-600"]:hover,
        html:not(.dark) #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-600"]:hover {
            background-color: #2f44ad !important;
        }
        html:not(.dark) #sidebarProjectTree [onclick^="selectSidebarScope"][class*="bg-brand-50"] {
            background-color: rgba(255, 255, 255, .85) !important;
            color: #2f44ad !important;
        }

        /* tombol + Project: pil putih transparan seperti chip role di login */
        html:not(.dark) #appSidebar button[onclick="openNewBoardModal()"] {
            background-color: rgba(255, 255, 255, .78) !important;
            border: 1px solid rgba(61, 85, 201, .28) !important;
            color: #1f3277 !important;
        }
        html:not(.dark) #appSidebar button[onclick="openNewBoardModal()"]:hover {
            background-color: #fff !important;
            border-color: #3d55c9 !important;
            color: #16255c !important;
        }

        html:not(.dark) #appSidebar ::-webkit-scrollbar-thumb { background: rgba(61, 85, 201, .3); }
        html:not(.dark) .sns-side-foot {
            background: rgba(255, 255, 255, .72);
            border-color: rgba(61, 85, 201, .2);
            color: #5d6c99;
        }
        html:not(.dark) .sns-side-foot b { color: #16255c; }

        /* ═══ 4. HEADER & TOOLBAR: putih tegas, garis biru-abu jelas ═══ */
        html:not(.dark) header.h-14 {
            box-shadow: 0 1px 0 #c9d5f2, 0 14px 30px -22px rgba(50, 90, 160, .55) !important;
        }
        html:not(.dark) header.h-14:not([class*="bg-brand-"]):not([class*="bg-emerald-"]) { background-color: var(--sns-header-bg) !important; }
        html:not(.dark) header.h-14 button:not([class*="bg-brand-"]):not([class*="bg-emerald-"]):hover {
            background-color: rgba(61, 85, 201, .1);
            box-shadow: 0 0 0 1px rgba(61, 85, 201, .28), 0 10px 20px -12px rgba(61, 85, 201, .6);
        }
        html:not(.dark) #searchInput { border-width: 2px !important; }

        /* ═══ 5. KOLOM: biru pucat solid, kontras terhadap latar & kartu ═══ */
        html:not(.dark) main .grid > div[class*="rounded-2xl"] {
            --col-accent: #5f7fd8;
            box-shadow: 0 12px 28px -16px rgba(40, 80, 170, .55), 0 1px 3px rgba(40, 80, 170, .16) !important;
        }
        html:not(.dark) main .grid > div[class*="rounded-2xl"]:hover {
            box-shadow: 0 16px 34px -18px rgba(50, 90, 160, .55), 0 1px 3px rgba(50, 90, 160, .14) !important;
        }
        html:not(.dark) div:has(> div > #col-todo) span.w-2\.5 { background-color: #5f7fd8 !important; }
        html:not(.dark) #col-done .kanban-task-card { opacity: .94; }

        /* ═══ 6. MODAL ═══ */
        html:not(.dark) [class*="bg-white"][class*="rounded-2xl"][class*="shadow-2xl"] {
            box-shadow: 0 30px 70px rgba(50, 90, 160, .35), 0 2px 8px rgba(50, 90, 160, .12) !important;
        }

        /* ═══ 7. TEKS, GARIS & PERMUKAAN UTILITY (slate → palet navy login) ═══ */
        html:not(.dark) .text-slate-400 { color: #7886b3; }
        html:not(.dark) .text-slate-500 { color: #5d6c99; }
        html:not(.dark) .text-slate-600 { color: #3a4a7a; }
        html:not(.dark) .text-slate-700 { color: #1f3277; }
        html:not(.dark) .text-slate-800,
        html:not(.dark) .text-slate-900 { color: #16255c; }
        html:not(.dark) .text-brand-600,
        html:not(.dark) .text-brand-700 { color: #3d55c9; }

        html:not(.dark) .bg-slate-50 { background-color: #eef4ff; }
        html:not(.dark) .bg-slate-100,
        html:not(.dark) .hover\:bg-slate-100:hover { background-color: #e1eafc; }
        html:not(.dark) .hover\:bg-slate-50:hover { background-color: #e9f0ff; }
        html:not(.dark) .bg-brand-50 { background-color: #e6ecff; }

        html:not(.dark) [class*="border-slate-100"]:not([class*="border-l-"]) { border-color: #dbe5fa; }
        html:not(.dark) [class*="border-slate-200"]:not([class*="border-l-"]) { border-color: #c9d5f2; }
        html:not(.dark) [class*="border-slate-300"]:not([class*="border-l-"]) { border-color: #b4c3ea; }

        /* tombol utama = aksen login */
        html:not(.dark) :is(header, main) button.bg-brand-600 { background-color: #3d55c9; }
        html:not(.dark) :is(header, main) button.bg-brand-600:hover { background-color: #2f44ad; }

        /* ═══ 8. FORM: gaya field login ═══ */
        html:not(.dark) :is(input, select, textarea):not([type="checkbox"]):not([type="radio"]):not([type="range"]) {
            background-color: #f1f5ff;
            border-color: #c9d5f2;
            color: #16255c;
        }
        html:not(.dark) :is(input, select, textarea)::placeholder { color: #8795c0; }
        html:not(.dark) :is(input, select, textarea):not([type="checkbox"]):not([type="radio"]):hover { border-color: #aebcea; }
        html:not(.dark) :is(input, select, textarea):not([type="checkbox"]):not([type="radio"]):focus {
            background-color: #fff;
            border-color: #3d55c9;
            box-shadow: 0 0 0 4px rgba(61, 85, 201, .16);
        }
        html:not(.dark) :focus-visible { outline-color: #3d55c9 !important; }

        html:not(.dark) .kanban-column::-webkit-scrollbar-thumb,
        html:not(.dark) .kanban-scroll::-webkit-scrollbar-thumb { background-color: rgba(61, 85, 201, .28); }
    </style>

    {{-- ================================================================
         SNS TOOLBAR — susunan filter dikunci (grid) supaya tidak geser saat
         ganti filter Semua / Maint-Support / Development. Berlaku di light & dark
         (hanya tata letak, tanpa warna). Hapus blok ini untuk kembali ke awal.
    ================================================================= --}}
    <style id="sns-toolbar">
        /* ═══ TOOLBAR 2 BARIS (layar ≥1280px) — posisi dikunci, tidak geser antar mode ═══
           Baris 1: pencarian | waktu + urutan ........ kategori | Rekap Excel (ATL)
           Baris 2: Level ............................ Create Task | avatar | My Tasks
           Progres + Critical dipindah ke header (di sebelah judul board).            */
        @media (min-width: 1280px) {
            div:has(> div > #searchInput) {
                flex-wrap: nowrap;
                justify-content: flex-start;
                align-items: center;
                gap: .75rem;
            }
            div:has(> #searchInput)    { flex: 0 1 256px; min-width: 150px; }
            div:has(> #btnSortOrder)   { flex: none; flex-wrap: nowrap; }
            div:has(> .cat-filter-btn) { flex: none; margin-left: auto; }
            #btnExportExcel            { flex: none; }

            div:has(> #priorityFilterContainer) {
                flex-wrap: nowrap;
                justify-content: flex-start;
                align-items: center;
                gap: .75rem;
            }
            #priorityFilterContainer { flex: none; }
            div:has(> #avatarFilterGroup) { flex: none; margin-left: auto; }
        }

        /* Progres di header */
        header.h-14 #boardProgressSummary { flex: none; margin-left: .25rem; }

        /* ── Avatar PIC: tidak bertumpuk, inisial terbaca penuh ── */
        #avatarFilterGroup { gap: 5px; flex-wrap: wrap; }
        #avatarFilterGroup > * { margin-left: 0 !important; }
    </style>
    <style id="sns-light-tune">
        /* ═══ TUNING LIGHT: teks lebih solid, latar & permukaan sedikit diredam ═══
           Hanya mode light (html:not(.dark)). Hapus blok ini untuk kembali ke versi sebelumnya. */
        html:not(.dark) {
            --sns-main-bg: #d3e3fa;
            --sns-header-bg: rgba(244, 248, 255, .95);
            --sns-toolbar-bg: rgba(238, 244, 254, .9);
            --sns-card-bg: #fafcff;
            --sns-modal-bg: #f8faff;
            --sns-input-bg: #eaf1fe;
            --sns-col-title: #16255c;
            --sns-sidebar-text: #2c3c72;
        }
        html:not(.dark) body {
            background-image: linear-gradient(135deg, #d0e2fa 0%, #e2edfc 45%, #c8def8 100%);
        }

        /* teks: lebih pekat + sedikit lebih tebal */
        html:not(.dark) .text-slate-400 { color: #5b6b9e; }
        html:not(.dark) .text-slate-500 { color: #46568a; }
        html:not(.dark) .text-slate-600 { color: #2c3c72; }
        html:not(.dark) .text-slate-700 { color: #182869; }
        html:not(.dark) .text-slate-800,
        html:not(.dark) .text-slate-900 { color: #0e1a4a; }
        html:not(.dark) .font-normal  { font-weight: 500; }
        html:not(.dark) .font-medium  { font-weight: 600; }
        html:not(.dark) :is(input, select, textarea)::placeholder { color: #6e7da8; }
        html:not(.dark) :is(input, select, textarea):not([type="checkbox"]):not([type="radio"]):not([type="range"]) {
            background-color: #eaf1fe;
            color: #0e1a4a;
        }
        html:not(.dark) :is(input, select, textarea):not([type="checkbox"]):not([type="radio"]):focus { background-color: #f7faff; }
        html:not(.dark) #appSidebar .block.text-\[10px\],
        html:not(.dark) #appSidebar [class*="text-\[10px\]"][class*="uppercase"] { color: #3a4a7a !important; }
        html:not(.dark) .sns-side-foot { color: #46568a; }
    </style>

    <style id="sns-um-light">
        /* ═══ MANAJEMEN USER — form mode light (gaya login). Mode gelap tidak tersentuh. ═══ */
        html:not(.dark) dialog.user-management-modal {
            background: #f8faff !important;
            color: #16255c !important;
            border: 1px solid #b4c3ea !important;
            border-radius: 20px !important;
            box-shadow: 0 30px 70px rgba(50, 90, 160, .35), 0 2px 8px rgba(50, 90, 160, .12) !important;
            padding: 0 !important;
            overflow: hidden;
        }
        html:not(.dark) dialog.user-management-modal::backdrop { background: rgba(22, 37, 92, .5); }

        /* header */
        html:not(.dark) dialog.user-management-modal > header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
            background: linear-gradient(160deg, #d6e9ff 0%, #c9e2ff 55%, #c0ecff 100%);
            border-bottom: 1px solid #b4c3ea;
        }
        html:not(.dark) dialog.user-management-modal > header h2 { margin: 0; font-size: 17px; font-weight: 800; color: #16255c; }
        html:not(.dark) dialog.user-management-modal > header small { color: #3a4a7a; font-weight: 600; }
        html:not(.dark) dialog.user-management-modal [data-user-close] {
            width: 32px; height: 32px;
            border-radius: 10px;
            border: 1px solid rgba(61, 85, 201, .28);
            background: rgba(255, 255, 255, .75);
            color: #1f3277;
            font-size: 20px; line-height: 1;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        html:not(.dark) dialog.user-management-modal [data-user-close]:hover { background: #fff; border-color: #3d55c9; }

        html:not(.dark) dialog.user-management-modal .user-management-body { padding: 18px 20px; }

        /* daftar user */
        html:not(.dark) dialog.user-management-modal #userManagementList {
            max-height: 240px;
            overflow: auto;
            padding: 6px;
            border: 1px solid #c9d5f2;
            border-radius: 14px;
            background: #fff;
        }
        html:not(.dark) dialog.user-management-modal #userManagementList > * {
            padding: 10px 12px;
            border: 1px solid #dbe5fa;
            border-radius: 10px;
            background: #f1f6ff;
            color: #16255c;
            transition: background .15s, border-color .15s;
        }
        html:not(.dark) dialog.user-management-modal #userManagementList > * + * { margin-top: 6px; }
        html:not(.dark) dialog.user-management-modal #userManagementList > *:hover { background: #e6eeff; border-color: #b4c3ea; }
        html:not(.dark) dialog.user-management-modal #userManagementList > :is(.active, .selected, [aria-current], [aria-selected="true"]) {
            background: #e0e8ff; border-color: #3d55c9;
        }
        html:not(.dark) dialog.user-management-modal #userManagementList :is(strong, b) { color: #16255c; font-weight: 800; }
        html:not(.dark) dialog.user-management-modal #userManagementList :is(small, span, p) { color: #3a4a7a; }
        html:not(.dark) dialog.user-management-modal #userManagementList button {
            padding: 4px 10px;
            border: 1px solid #b4c3ea;
            border-radius: 8px;
            background: #fff;
            color: #1f3277;
            font-weight: 700;
            cursor: pointer;
        }
        html:not(.dark) dialog.user-management-modal #userManagementList button:hover { border-color: #3d55c9; background: #f1f5ff; }
        html:not(.dark) dialog.user-management-modal #userManagementList button:is([class*="delete"], [class*="danger"], [class*="remove"], [data-user-delete]) {
            color: #be123c; border-color: #fecdd3; background: #fff1f2;
        }

        /* form */
        html:not(.dark) dialog.user-management-modal .user-management-form { display: grid; gap: 12px; margin-top: 16px; }
        html:not(.dark) dialog.user-management-modal .user-management-form label {
            display: grid;
            gap: 6px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .02em;
            color: #2c3c72;
        }
        html:not(.dark) dialog.user-management-modal .user-management-form :is(input, select):not([type="radio"]):not([type="checkbox"]) {
            width: 100%;
            box-sizing: border-box;
            height: 42px;
            padding: 0 12px;
            border: 2px solid #c9d5f2;
            border-radius: 12px;
            background-color: #eaf1fe;
            color: #0e1a4a;
            font: 600 14px/1 inherit;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        html:not(.dark) dialog.user-management-modal .user-management-form :is(input, select):not([type="radio"]):not([type="checkbox"]):hover { border-color: #aebcea; }
        html:not(.dark) dialog.user-management-modal .user-management-form :is(input, select):not([type="radio"]):not([type="checkbox"]):focus {
            background-color: #f7faff;
            border-color: #3d55c9;
            box-shadow: 0 0 0 4px rgba(61, 85, 201, .16);
        }
        html:not(.dark) dialog.user-management-modal .user-management-form input::placeholder { color: #6e7da8; font-weight: 500; }

        /* penempatan staff */
        html:not(.dark) dialog.user-management-modal .user-assignment-field {
            margin: 0;
            min-width: 0;
            padding: 12px 14px;
            border: 1px solid #c9d5f2;
            border-radius: 14px;
            background: #eef4ff;
        }
        html:not(.dark) dialog.user-management-modal .user-assignment-field legend {
            padding: 0 8px;
            font-size: 12px;
            font-weight: 800;
            color: #2f44ad;
        }
        html:not(.dark) dialog.user-management-modal .user-management-form label.user-radio {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0;
            color: #2c3c72;
            cursor: pointer;
        }
        html:not(.dark) dialog.user-management-modal :is(input[type="radio"], input[type="checkbox"]) {
            width: 16px; height: 16px; accent-color: #3d55c9;
        }
        html:not(.dark) dialog.user-management-modal .tl-checkbox-list:not(:empty) {
            margin-top: 8px;
            max-height: 150px;
            overflow: auto;
            padding: 8px 10px;
            border: 1px dashed #b4c3ea;
            border-radius: 12px;
            background: #f7faff;
        }
        html:not(.dark) dialog.user-management-modal .tl-checkbox-list label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0;
            color: #1f3277;
            cursor: pointer;
        }

        /* error */
        html:not(.dark) dialog.user-management-modal .user-form-error:not(:empty) {
            padding: 8px 12px;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            background: #fff1f2;
            color: #be123c;
            font-size: 13px;
            font-weight: 700;
        }

        /* tombol bawah */
        html:not(.dark) dialog.user-management-modal .user-management-form footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 6px;
        }
        html:not(.dark) dialog.user-management-modal .user-management-form footer button {
            padding: 10px 18px;
            border: 1px solid #b4c3ea;
            border-radius: 12px;
            background: #fff;
            color: #1f3277;
            font-weight: 700;
            cursor: pointer;
            transition: border-color .15s, background .15s, transform .12s;
        }
        html:not(.dark) dialog.user-management-modal .user-management-form footer button:hover { border-color: #3d55c9; background: #f1f5ff; }
        html:not(.dark) dialog.user-management-modal .user-management-form footer button:active { transform: translateY(1px); }
        html:not(.dark) dialog.user-management-modal .user-management-form footer .user-save-button {
            border: 0;
            background: linear-gradient(180deg, #4a63d6, #3d55c9);
            color: #fff;
            box-shadow: 0 8px 18px -8px rgba(61, 85, 201, .75), inset 0 1px 0 rgba(255, 255, 255, .22);
        }
        html:not(.dark) dialog.user-management-modal .user-management-form footer .user-save-button:hover { background: #2f44ad; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-brand-500 selection:text-white">
    {!! $workspace !!}
    <dialog id="boardConfirmDialog" class="board-modal board-confirm" aria-labelledby="boardConfirmTitle">
        <header><h2 id="boardConfirmTitle"></h2><button type="button" data-modal-close aria-label="Tutup">×</button></header>
        <div class="confirm-body"><p>Anda akan menghapus <strong data-confirm-name></strong>.</p><p class="confirm-impact" data-confirm-impact></p></div>
        <div class="confirm-actions"><button type="button" data-modal-cancel>Batal</button><button type="button" class="danger" data-modal-confirm>Hapus</button></div>
    </dialog>
    <dialog id="userManagementModal" class="board-modal user-management-modal" aria-labelledby="userManagementTitle">
        <header><div><h2 id="userManagementTitle">Manajemen User</h2><small>Kelola TL dan Staff</small></div><button type="button" data-user-close aria-label="Tutup">×</button></header>
        <div class="user-management-body">
            <div id="userManagementList" class="user-management-list"></div>
            <form id="userManagementForm" class="user-management-form">
                <input type="hidden" id="managedUserId">
                <label>Nama<input id="managedUserName" required maxlength="255"></label>
                <label>NIP<input id="managedUserNip" type="text" required inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="off"></label>
                <label>Role<select id="managedUserRole"><option value="tl">Team Leader</option><option value="it_staff">Staff</option></select></label>
                <label>Password<input id="managedUserPassword" type="password" minlength="8" placeholder="Minimal 8 karakter"></label>
                <fieldset id="managedAssignmentField" class="user-assignment-field">
                    <legend>Penempatan Staff</legend>
                    <label class="user-radio"><input type="radio" name="managedAssignment" value="atl" checked> Langsung ke ATL</label>
                    <label class="user-radio"><input type="radio" name="managedAssignment" value="tl"> Di bawah Team Lead</label>
                    <div id="managedTlField" class="tl-checkbox-list"></div>
                </fieldset>
                <div class="user-form-error field-error" id="userManagementError"></div>
                <footer><button type="button" data-user-cancel>Batal</button><button type="submit" class="primary user-save-button">Simpan User</button></footer>
            </form>
        </div>
    </dialog>
    <form id="laravel-logout" method="post" action="{{ route('logout') }}" hidden>@csrf</form>
    <script>
        const itflowData = @json($prototypeData);
        const teamMembers = itflowData.teamMembers;
        const boards = itflowData.boards;
        let subProjects = itflowData.subProjects;
        let tasks = itflowData.tasks;
        const CATEGORY_CONFIG = {
            maintenance: {key: 'maintenance', shortLabel: 'Maint/Support', fullLabel: 'Maintenance / Support', icon: 'wrench', badgeClass: 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/80', tiers: [
                {value:'low', label:'Low', desc:'Rutin / Santai', dotClass:'bg-sky-500', pillClass:'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300/80 dark:border-slate-700'},
                {value:'high', label:'High', desc:'Penting', dotClass:'bg-amber-500', pillClass:'bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border-amber-300/80 dark:border-amber-700'},
                {value:'critical', label:'Critical', desc:'Mendesak / Darurat', dotClass:'bg-rose-600', pillClass:'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-300/80 dark:border-rose-700'}]},
            development: {key: 'development', shortLabel: 'Development', fullLabel: 'Development', icon: 'code-2', badgeClass: 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80', tiers: [
                {value:'basic', label:'Basic', desc:'Minor UI / Tweak', dotClass:'bg-slate-500', pillClass:'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300/80 dark:border-slate-700'},
                {value:'great', label:'Great', desc:'Fitur Standar / API', dotClass:'bg-emerald-500', pillClass:'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-300/80 dark:border-emerald-700'},
                {value:'epic', label:'Epic', desc:'Modul Besar / Core', dotClass:'bg-purple-600', pillClass:'bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border-purple-300/80 dark:border-purple-700'}]}
        };
        let currentUserId = itflowData.userId;
        let currentRole = itflowData.role;
        let selectedBoardFilter = 'ALL', selectedSubProjectFilter = 'ALL';
        let expandedBoards = Object.fromEntries(Object.keys(boards).map(key => [key, true]));
        let selectedCategoryFilter = 'all', selectedPriorityFilter = 'all', selectedTimeFilter = 'all', sortOrder = 'desc';
        let selectedAssigneeFilter = null, onlyMyTasks = false;
        let activeTaskId = null, draftTask = null, hasUnsavedChanges = false, sortableInstances = [];
        {!! $prototypeScript !!}
    </script>
    <script src="{{ asset('itflow/board.js') }}" defer></script>
    {{-- Override logo & brand name from prototype to IT SNSac --}}
    <script>
    (function patchBrand() {
        const SNS_LOGO = '{{ asset('itflow/sns-logo.png') }}';
        const SNS_NAME = 'IT SNSac';

        function applyBrand() {
            /* Replace every element that contains only the brand icon + "ITFlow" text.
               The prototype renders two brand containers:
               1. Login overlay — selector wraps a gradient box + span>IT<span>Flow</span>
               2. Navbar top-left — same pattern but smaller                             */

            // Replace gradient icon boxes with the SNS logo image
            document.querySelectorAll(
                '.from-brand-600.to-indigo-600, [class*="from-brand-600"][class*="to-indigo-600"]'
            ).forEach(el => {
                // Only target the small square brand icon divs (not buttons / badges)
                if (el.tagName === 'DIV' && (el.classList.contains('rounded-2xl') || el.classList.contains('rounded-xl'))) {
                    el.innerHTML = '';
                    el.style.cssText = 'background:none!important;box-shadow:none!important;padding:0;display:flex;align-items:center;justify-content:center;';
                    const img = document.createElement('img');
                    img.src = SNS_LOGO;
                    img.alt = SNS_NAME;
                    img.style.cssText = 'width:100%;height:100%;object-fit:contain;';
                    el.appendChild(img);
                }
            });

            // Replace "IT<span>Flow</span>" text spans with "IT SNSac"
            document.querySelectorAll('span').forEach(el => {
                if (el.textContent.trim() === 'ITFlow' && el.querySelector('span')) {
                    el.textContent = SNS_NAME;
                }
            });

            // Also patch any loose text nodes that say ITFlow inside heading/p elements
            document.querySelectorAll('h1, h2, p, span, div').forEach(el => {
                if (el.children.length === 0 && el.textContent.includes('ITFlow')) {
                    el.textContent = el.textContent.replace(/ITFlow/g, SNS_NAME);
                }
            });

            // Update page title just in case prototype JS overwrites it
            document.title = document.title.replace(/ITFlow/gi, SNS_NAME);
        }

        // Run immediately after DOM is ready, then again after a tick (prototype may render async)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => { applyBrand(); setTimeout(applyBrand, 300); });
        } else {
            applyBrand();
            setTimeout(applyBrand, 300);
        }
    })();
    </script>
    <script>
    /* SNS Fluent — interaksi: aura kursor, reveal highlight, kartu masuk bertahap, bump counter, fade tema */
    (function snsFluent() {
        const reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
        const root = document.documentElement;

        // 1) Aura + grid titik mengikuti kursor (di-throttle dengan rAF)
        let px = 0, py = 0, tick = false;
        if (!reduce) {
            document.addEventListener('pointermove', e => {
                px = e.clientX; py = e.clientY;
                if (tick) return;
                tick = true;
                requestAnimationFrame(() => {
                    root.style.setProperty('--px', px + 'px');
                    root.style.setProperty('--py', py + 'px');
                    tick = false;
                });
            }, {passive: true});
        }

        // 2) Reveal highlight: posisi kursor relatif terhadap kartu / item sidebar
        const REVEAL = '.kanban-task-card, #navAllBoardsBtn, #sidebarProjectTree [onclick^="selectSidebarScope"], #appSidebar button[onclick="openNewBoardModal()"]';
        document.addEventListener('pointermove', e => {
            const el = e.target.closest && e.target.closest(REVEAL);
            if (!el) return;
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            el.style.setProperty('--my', (e.clientY - r.top) + 'px');
        }, {passive: true});

        // 3) Kartu baru muncul bertahap (kartu yang cuma dipindah/drag tidak ikut dianimasikan)
        const CARD = '.kanban-task-card';
        function collect(node, out) {
            if (node.nodeType !== 1) return;
            if (node.matches(CARD)) out.push(node);
            if (node.querySelectorAll) node.querySelectorAll(CARD).forEach(c => out.push(c));
        }
        function reveal(cards) {
            let i = 0;
            cards.forEach(card => {
                if (card.dataset.snsSeen) return;
                card.dataset.snsSeen = '1';
                if (reduce) return;
                card.style.setProperty('--i', Math.min(i++, 12));
                card.classList.add('sns-in');
                card.addEventListener('animationend', () => card.classList.remove('sns-in'), {once: true});
            });
        }
        const first = [];
        collect(document.body, first);
        reveal(first);

        // 4) Angka counter kolom "membal" saat berubah
        function bump(el) {
            const badge = el && el.closest && el.closest('[id^="count-"]');
            if (!badge) return;
            const v = badge.textContent.trim();
            if (badge.dataset.v === undefined) { badge.dataset.v = v; return; }
            if (badge.dataset.v === v) return;
            badge.dataset.v = v;
            if (reduce) return;
            badge.classList.remove('sns-bump');
            void badge.offsetWidth;
            badge.classList.add('sns-bump');
        }
        document.querySelectorAll('[id^="count-"]').forEach(b => b.dataset.v = b.textContent.trim());

        new MutationObserver(list => {
            const added = [];
            list.forEach(m => {
                m.addedNodes.forEach(n => collect(n, added));
                bump(m.target.nodeType === 1 ? m.target : m.target.parentElement);
            });
            if (added.length) reveal(added);
        }).observe(document.body, {childList: true, subtree: true, characterData: true});

        // 5) Transisi halus saat ganti light/dark
        let wasDark = root.classList.contains('dark'), fadeTimer;
        new MutationObserver(() => {
            const isDark = root.classList.contains('dark');
            if (isDark === wasDark) return;
            wasDark = isDark;
            root.classList.add('sns-theme-fade');
            clearTimeout(fadeTimer);
            fadeTimer = setTimeout(() => root.classList.remove('sns-theme-fade'), 500);
        }).observe(root, {attributes: true, attributeFilter: ['class']});
    })();
    </script>
    <script>
    /* SNS Chrome — bayangan header saat scroll + kartu jam WIB di dasar sidebar */
    (function snsChrome() {
        const root = document.documentElement;

        // Header menebalkan bayangan ketika ada area yang di-scroll
        const scrolled = new Set();
        document.addEventListener('scroll', e => {
            const t = e.target === document ? document.scrollingElement : e.target;
            if (!t) return;
            ((t.scrollTop || 0) > 4) ? scrolled.add(t) : scrolled.delete(t);
            root.classList.toggle('sns-scrolled', scrolled.size > 0);
        }, {capture: true, passive: true});

        // Kartu status + jam: hanya dipasang kalau sidebar berupa kolom flex
        function mountFooter() {
            const side = document.getElementById('appSidebar');
            if (!side || side.querySelector('.sns-side-foot')) return;
            const cs = getComputedStyle(side);
            if (cs.display !== 'flex' || cs.flexDirection !== 'column') return;

            const foot = document.createElement('div');
            foot.className = 'sns-side-foot';
            foot.innerHTML = '<span class="sns-dot" aria-hidden="true"></span><div><b data-sns-time>--.--</b><span data-sns-date></span></div>';
            side.appendChild(foot);

            const t = foot.querySelector('[data-sns-time]'), d = foot.querySelector('[data-sns-date]');
            const fT = new Intl.DateTimeFormat('id-ID', {hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta'});
            const fD = new Intl.DateTimeFormat('id-ID', {weekday: 'long', day: 'numeric', month: 'short', timeZone: 'Asia/Jakarta'});
            const tick = () => { const n = new Date(); t.textContent = fT.format(n).replace(':', '.') + ' WIB'; d.textContent = fD.format(n); };
            tick();
            setInterval(tick, 10000);
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mountFooter);
        else mountFooter();
    })();
    </script>
    <script>
    /* SNS MOVE — Rekap Excel → baris 1 toolbar; Progres/Critical → header (sebelah judul board).
       Elemen sama (id tetap), jadi logika role & render JS prototype tidak berubah.
       Badge role tetap di header. */
    (function snsMoveToToolbar() {
        function move() {
            const rekap    = document.getElementById('btnExportExcel');
            const progress = document.getElementById('boardProgressSummary');
            const row1     = document.getElementById('searchInput')?.parentElement?.parentElement;
            const headLeft = document.getElementById('activeScopeTitle')?.closest('header')?.firstElementChild;
            if (!rekap || !progress || !row1 || !headLeft) return false;
            if (rekap.dataset.snsMoved) return true;
            row1.appendChild(rekap);
            headLeft.appendChild(progress);
            rekap.dataset.snsMoved = '1';
            return true;
        }
        if (!move()) document.addEventListener('DOMContentLoaded', move);
    })();
    </script>
</body>
</html>
