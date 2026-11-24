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
         POLISH LAYER — efek tipis & animasi ringan
         Ditaruh setelah tema di atas, jadi hanya menimpa/menambah.
    ================================================================= --}}
    <style id="sns-polish">
        :root {
            --sns-ease: cubic-bezier(.2, .8, .2, 1);
            --sns-card-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 1px 3px rgba(15, 23, 42, .04), inset 0 1px 0 rgba(255, 255, 255, .9);
            --sns-card-shadow-hover: 0 12px 24px -10px rgba(37, 99, 235, .25), 0 3px 6px -2px rgba(15, 23, 42, .06), inset 0 1px 0 rgba(255, 255, 255, .9);
        }
        html.dark {
            --sns-card-shadow: 0 1px 3px rgba(0, 0, 0, .3), inset 0 1px 0 rgba(255, 255, 255, .04);
            --sns-card-shadow-hover: 0 12px 24px -8px rgba(0, 0, 0, .55), 0 0 0 1px rgba(59, 130, 246, .25), inset 0 1px 0 rgba(255, 255, 255, .06);
        }

        /* Atmosfer latar: cahaya biru tipis di pojok */
        #mainAppContainer,
        .flex-1.flex.flex-col.min-w-0 {
            background-image:
                radial-gradient(820px 340px at 92% -90px, rgba(59, 130, 246, .07), transparent 70%),
                radial-gradient(640px 320px at 0% 105%, rgba(99, 102, 241, .05), transparent 70%);
            background-repeat: no-repeat;
        }
        html.dark #mainAppContainer,
        html.dark .flex-1.flex.flex-col.min-w-0 {
            background-image:
                radial-gradient(820px 340px at 92% -90px, rgba(59, 130, 246, .11), transparent 70%),
                radial-gradient(640px 320px at 0% 105%, rgba(99, 102, 241, .08), transparent 70%);
        }

        /* Header: garis bawah dengan glow biru sangat tipis */
        header.h-14 {
            box-shadow: 0 1px 0 var(--sns-header-border), 0 8px 20px -14px rgba(37, 99, 235, .28) !important;
        }

        /* Sidebar */
        #navAllBoardsBtn svg:first-child { transition: transform .2s var(--sns-ease); }
        #navAllBoardsBtn:hover svg:first-child { transform: translateX(2px) scale(1.06); }
        #sidebarProjectTree [onclick^="selectSidebarScope"]:not([class*="bg-brand-600"]):hover {
            transform: translateX(2px);
        }
        #appSidebar button[onclick="openNewBoardModal()"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px -4px rgba(79, 70, 229, .35) !important;
        }
        #appSidebar button[onclick="openNewBoardModal()"]:active { transform: translateY(0) scale(.98); }

        /* Kolom: garis aksen status di atas + hover halus */
        main .grid > div[class*="rounded-2xl"] {
            position: relative;
            transition: border-color .2s ease, box-shadow .2s ease !important;
        }
        main .grid > div[class*="rounded-2xl"]:hover {
            border-color: var(--sns-card-border-hover) !important;
            box-shadow: 0 10px 26px -16px rgba(15, 23, 42, .22) !important;
        }
        main .grid > div[class*="rounded-2xl"]::before {
            content: "";
            position: absolute;
            top: -1px; left: 18px; right: 18px;
            height: 2px;
            border-radius: 0 0 3px 3px;
            background: #94a3b8;
            opacity: .85;
            pointer-events: none;
        }
        div:has(> div > #col-in_progress)::before { background: linear-gradient(90deg, #60a5fa, #3b82f6) !important; }
        div:has(> div > #col-done)::before        { background: linear-gradient(90deg, #34d399, #10b981) !important; }

        /* Titik status "In Progress" berdenyut pelan */
        div:has(> div > #col-in_progress) span.w-2\.5 { position: relative; }
        div:has(> div > #col-in_progress) span.w-2\.5::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: #3b82f6;
            animation: sns-ping 2.4s ease-out infinite;
            pointer-events: none;
        }
        @keyframes sns-ping {
            0%        { transform: scale(1);   opacity: .55; }
            70%, 100% { transform: scale(2.8); opacity: 0; }
        }

        [id^="count-"] { font-variant-numeric: tabular-nums; transition: background-color .2s ease, color .2s ease; }

        /* Kartu tugas: aksen kiri muncul saat hover + muncul bertahap */
        .kanban-task-card { position: relative; }
        .kanban-task-card::before {
            content: "";
            position: absolute;
            left: -1px; top: 12px; bottom: 12px;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: linear-gradient(180deg, #60a5fa, #2563eb);
            opacity: 0;
            transform: scaleY(.3);
            transition: transform .24s var(--sns-ease), opacity .18s ease;
            pointer-events: none;
        }
        .kanban-task-card:hover::before { opacity: 1; transform: scaleY(1); }
        .sortable-ghost .kanban-task-card::before,
        .sortable-drag .kanban-task-card::before { opacity: 0; }

        @keyframes sns-rise { from { opacity: 0; transform: translateY(8px); } }
        .kanban-task-card.sns-in {
            animation: sns-rise .38s var(--sns-ease) backwards;
            animation-delay: calc(var(--i, 0) * 35ms);
        }

        /* Modal: fade + pop singkat saat dibuka */
        @keyframes sns-fade { from { opacity: 0; } }
        @keyframes sns-pop  { from { opacity: 0; transform: translateY(10px) scale(.97); } }
        [class*="fixed"][class*="inset-0"][class*="bg-black"] { animation: sns-fade .18s ease backwards; }
        [class*="bg-white"][class*="rounded-2xl"][class*="shadow-2xl"],
        html.dark [class*="bg-slate-900"][class*="rounded-2xl"][class*="shadow-2xl"] {
            animation: sns-pop .24s var(--sns-ease) backwards;
        }
        dialog.board-modal[open] { animation: sns-pop .22s var(--sns-ease) backwards; }
        dialog.board-modal[open]::backdrop { animation: sns-fade .2s ease backwards; }

        @media (prefers-reduced-motion: reduce) {
            .kanban-task-card.sns-in,
            [class*="fixed"][class*="inset-0"][class*="bg-black"],
            [class*="bg-white"][class*="rounded-2xl"][class*="shadow-2xl"],
            html.dark [class*="bg-slate-900"][class*="rounded-2xl"][class*="shadow-2xl"],
            dialog.board-modal[open],
            dialog.board-modal[open]::backdrop,
            div:has(> div > #col-in_progress) span.w-2\.5::after { animation: none !important; }
            .kanban-task-card, .kanban-task-card::before { transition: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-brand-500 selection:text-white">
    {!! $workspace !!}
    <dialog id="boardConfirmDialog" class="board-modal board-confirm" aria-labelledby="boardConfirmTitle">
        <header><h2 id="boardConfirmTitle"></h2><button type="button" data-modal-close aria-label="Tutup">×</button></header>
        <div class="confirm-body"><p>Anda akan menghapus <strong data-confirm-name></strong>.</p><p class="confirm-impact" data-confirm-impact></p></div>
        <div class="confirm-actions"><button type="button" data-modal-cancel>Batal</button><button type="button" class="danger" data-modal-confirm>Hapus</button></div>
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
    {{-- Animasi muncul bertahap untuk kartu baru (kartu yang cuma dipindah/drag tidak ikut dianimasikan) --}}
    <script>
    (function cardEntrance() {
        if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const SEL = '.kanban-task-card';

        function collect(node, out) {
            if (node.nodeType !== 1) return;
            if (node.matches(SEL)) out.push(node);
            node.querySelectorAll && node.querySelectorAll(SEL).forEach(c => out.push(c));
        }

        function animate(cards) {
            let i = 0;
            cards.forEach(card => {
                if (card.dataset.snsSeen) return;
                card.dataset.snsSeen = '1';
                card.style.setProperty('--i', Math.min(i++, 10));
                card.classList.add('sns-in');
                card.addEventListener('animationend', () => card.classList.remove('sns-in'), {once: true});
            });
        }

        const first = [];
        collect(document.body, first);
        animate(first);

        new MutationObserver(mutations => {
            const added = [];
            mutations.forEach(m => m.addedNodes.forEach(n => collect(n, added)));
            if (added.length) animate(added);
        }).observe(document.body, {childList: true, subtree: true});
    })();
    </script>
</body>
</html>