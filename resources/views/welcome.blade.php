<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sport.md') }} – intră pe teren</title>
    <meta name="description" content="Meciuri de fotbal, baschet și tenis în orașele Moldovei. Găsești jocul, îți ocupi locul, vii la teren.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Libre+Franklin:wght@400;500;600;700&family=DotGothic16&display=swap" rel="stylesheet">
    <style>
        :root {
            --night: #0A1430;
            --night-2: #12224D;
            --night-3: #1B2F63;
            --line: #EEF2FF;
            --soft: #A3B3DA;
            --led: #FFB524;
            --ball: #DCEB4B;
            --ink: #0A1430;
            --grass: #22744A;
            --wood: #B97A40;
            --hard: #2C4F9E;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font: 400 1.0625rem/1.6 'Libre Franklin', system-ui, sans-serif; letter-spacing: 0;
            background: var(--night); color: var(--line);
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility;
        }
        a { color: inherit; }
        button { font: inherit; color: inherit; }
        :focus-visible { outline: 3px solid var(--ball); outline-offset: 3px; }
        .wrap { width: min(1160px, 100% - 32px); margin-inline: auto; }
        .display { font-family: 'Big Shoulders Display', sans-serif; font-weight: 800; line-height: .92; letter-spacing: -.02em; }
        .led-font { font-family: 'DotGothic16', monospace; }

        .btn {
            display: inline-block; padding: 14px 24px; border-radius: 4px;
            font: 600 1rem/1 'Libre Franklin', sans-serif; text-decoration: none; cursor: pointer;
            border: 2px solid var(--line); background: transparent;
        }
        .btn-ball { background: var(--ball); border-color: var(--ball); color: var(--ink); }
        .btn-ball:hover { background: #EAF76E; border-color: #EAF76E; }
        .btn-line:hover { background: var(--line); color: var(--ink); }
        .btn[disabled] { opacity: .45; cursor: not-allowed; }

        /* Header + pager */
        .top { position: fixed; inset: 0 0 auto; z-index: 30; transition: background .3s; }
        .top.solid { background: rgba(10, 20, 48, .88); backdrop-filter: blur(10px); }
        .top .wrap { display: flex; align-items: center; justify-content: space-between; height: 70px; gap: 16px; }
        .brand { font-size: 1.9rem; text-decoration: none; }
        .brand b { color: var(--ball); }
        .top nav { display: flex; align-items: center; gap: 26px; }
        .top nav a:not(.btn) { text-decoration: none; color: var(--soft); font-weight: 500; }
        .top nav a:not(.btn):hover { color: var(--line); }
        .top .btn { padding: 10px 18px; }

        .pager { position: fixed; right: 18px; top: 50%; transform: translateY(-50%); z-index: 30; display: flex; flex-direction: column; gap: 12px; }
        .pager a { width: 11px; height: 11px; border-radius: 50%; border: 2px solid var(--line); transition: background .2s, transform .2s; }
        .pager a[aria-current="true"] { background: var(--ball); border-color: var(--ball); transform: scale(1.25); }
        .sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

        /* 1. Opening scene + sport orbit.
           Default (no GSAP / reduced motion): hero, then a static grid of sport cards.
           .is-orbit (added by JS): one pinned stage where the cards orbit the ball. */
        .scene { position: relative; background: radial-gradient(ellipse at 50% 120%, var(--night-3), var(--night) 70%); }
        .stage { position: relative; overflow: hidden; }
        .hero-zone { position: relative; height: 100svh; }
        #ball { position: absolute; inset: 0; z-index: 5; will-change: transform; }
        #ball canvas { display: block; width: 100%; height: 100%; touch-action: pan-y; cursor: grab; }
        #ball canvas:active { cursor: grabbing; }
        .beam {
            position: absolute; top: -10%; width: 60vw; height: 140%; pointer-events: none;
            background: linear-gradient(180deg, rgba(255, 244, 214, .22), transparent 70%);
            clip-path: polygon(46% 0, 54% 0, 100% 100%, 0 100%);
            opacity: .45;
        }
        .beam.l { left: -18vw; transform: rotate(-24deg); }
        .beam.r { right: -18vw; transform: rotate(24deg); }
        .intro-copy { position: absolute; left: 0; right: 0; bottom: 8vh; z-index: 6; text-align: center; }

        .orbit-head { text-align: center; padding: 40px 16px 24px; }
        .orbit-head h2 { font-size: clamp(2.6rem, 6vw, 4.4rem); }
        .orbit-head p { color: var(--soft); margin-top: 6px; }
        .orbit { --cw: 260px; --ch: 400px; display: flex; flex-wrap: wrap; gap: 18px; justify-content: center; padding: 0 16px 96px; }
        .orbit .scard { flex: 0 0 var(--cw); height: var(--ch); }
        .orbit-dots { display: none; }

        .is-orbit .stage { height: 100svh; }
        .is-orbit .hero-zone { position: absolute; inset: 0; height: auto; }
        .is-orbit .orbit-head { position: absolute; left: 0; right: 0; top: 78px; padding: 0 16px; z-index: 30; visibility: hidden; }
        .is-orbit .orbit { --cw: clamp(176px, 19vw, 240px); --ch: clamp(270px, 40vh, 360px); position: absolute; inset: 0; padding: 0; display: block; pointer-events: none; }
        .is-orbit .orbit .scard {
            position: absolute; left: 50%; top: 50%; width: var(--cw); height: var(--ch);
            margin: calc(var(--ch) / -2) 0 0 calc(var(--cw) / -2);
            visibility: hidden; pointer-events: auto; will-change: transform, opacity;
            transition: box-shadow .3s;
        }
        .is-orbit .orbit .scard:hover { transform: none; }
        .is-orbit .orbit .scard:not(.is-front) { cursor: pointer; }
        .is-orbit .orbit .scard:not(.is-front) * { pointer-events: none; }
        .is-orbit .orbit .scard.is-front { box-shadow: 0 0 0 3px var(--c), 0 30px 80px -10px color-mix(in srgb, var(--c) 45%, transparent); }
        .is-orbit .scard h3 { font-size: 2.2rem; }
        .is-orbit .scard .big { font-size: clamp(3.4rem, 6vw, 5rem); }
        .is-orbit .orbit-dots {
            position: absolute; left: 50%; bottom: 3vh; transform: translateX(-50%); z-index: 30;
            display: flex; gap: 8px; visibility: hidden;
        }
        .orbit-dots button { width: 9px; height: 9px; padding: 0; border-radius: 50%; border: 2px solid var(--soft); background: none; cursor: pointer; transition: all .2s; }
        .orbit-dots button[aria-current="true"] { width: 26px; border-radius: 6px; border-color: var(--c, var(--ball)); background: var(--c, var(--ball)); }
        .intro-copy h1 { font-size: clamp(3.4rem, 10vw, 8rem); letter-spacing: -.03em; text-shadow: 0 4px 30px rgba(10, 20, 48, .8); }
        .intro-copy p { color: var(--soft); font-size: 1.15rem; line-height: 1.55; max-width: 32em; margin: 16px auto 0; padding: 0 16px; }
        .hint { display: inline-block; margin-top: 26px; color: var(--soft); font-size: .92rem; text-decoration: none; }
        .hint::after { content: ''; display: block; width: 2px; height: 34px; margin: 8px auto 0; background: var(--ball); animation: drip 1.6s ease-in-out infinite; transform-origin: top; }
        @keyframes drip { 0% { transform: scaleY(0); } 50% { transform: scaleY(1); } 100% { transform: scaleY(1); opacity: 0; } }
        /* Match search: text / date / time + submit, filter pills below (component: x-match-search) */
        :root { --glass: rgba(18, 34, 77, .55); --glass-line: rgba(238, 242, 255, .14); }
        [x-cloak] { display: none !important; }
        .msearch { position: absolute; top: 13vh; left: 50%; z-index: 6; translate: -50% 0; width: min(820px, 100% - 32px); }
        .msearch-bar {
            display: grid; grid-template-columns: minmax(0, 1fr) auto auto auto; align-items: stretch;
            padding: 6px; border-radius: 18px;
            background: var(--glass); border: 1px solid var(--glass-line);
            backdrop-filter: blur(18px) saturate(140%); -webkit-backdrop-filter: blur(18px) saturate(140%);
            box-shadow: 0 24px 60px -24px rgba(0, 0, 0, .75), inset 0 1px 0 rgba(255, 255, 255, .06);
            transition: border-color .25s, box-shadow .25s;
        }
        .msearch-bar:focus-within { border-color: rgba(238, 242, 255, .3); box-shadow: 0 0 0 4px rgba(238, 242, 255, .06), 0 24px 60px -24px rgba(0, 0, 0, .75); }
        .msearch-field {
            display: flex; flex-direction: column; justify-content: center; gap: 2px;
            padding: 8px 18px; border-radius: 12px; cursor: text; transition: background .2s;
        }
        .msearch-field:hover { background: rgba(255, 255, 255, .04); }
        .msearch-field + .msearch-field, datalist + .msearch-field { border-left: 1px solid var(--glass-line); border-radius: 0 12px 12px 0; }
        .msearch-q { flex-direction: row; align-items: center; gap: 12px; }
        .msearch-label { font-size: .72rem; font-weight: 500; color: var(--soft); letter-spacing: .01em; }
        .msearch-field input {
            width: 100%; min-width: 0; background: none; border: 0; outline: none; color: var(--line);
            font: 400 1rem/1.3 'Libre Franklin', sans-serif; letter-spacing: 0; color-scheme: dark;
        }
        .msearch-q input { font-size: 1.05rem; }
        .msearch-field input::placeholder { color: var(--soft); opacity: .85; }
        .msearch-field input::-webkit-calendar-picker-indicator { opacity: .55; cursor: pointer; }
        .msearch-date input { width: 140px; }
        .msearch-time input { width: 100px; }
        .msearch-icon { width: 18px; height: 18px; flex: none; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
        .msearch-q .msearch-icon { color: var(--soft); }
        .msearch-kbd {
            font: 500 .7rem/1 'Libre Franklin', sans-serif; color: var(--soft); white-space: nowrap;
            padding: 5px 7px; border-radius: 6px; border: 1px solid var(--glass-line);
        }
        .msearch-go {
            display: inline-flex; align-items: center; gap: 8px; margin-left: 6px; padding: 0 24px;
            border-radius: 12px; border: 1px solid rgba(238, 242, 255, .18); cursor: pointer;
            background: #05091A; color: var(--line); font: 600 .98rem/1 'Libre Franklin', sans-serif;
            transition: background .2s, border-color .2s, transform .15s;
        }
        .msearch-go .msearch-icon { color: var(--ball); }
        .msearch-go:hover { background: #0B1636; border-color: rgba(238, 242, 255, .35); }
        .msearch-go:active { transform: scale(.98); }
        .msearch-pills { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 12px; padding: 0 4px; }
        .msearch-pill {
            padding: 7px 14px; border-radius: 999px; cursor: pointer;
            font: 500 .86rem/1 'Libre Franklin', sans-serif; color: #D6DEF3;
            background: rgba(10, 20, 48, .45); border: 1px solid var(--glass-line);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            transition: background .2s, border-color .2s, color .2s;
        }
        .msearch-pill:hover { border-color: rgba(238, 242, 255, .35); color: var(--line); }
        .msearch-pill[aria-pressed="true"] { background: rgba(220, 235, 75, .14); border-color: var(--ball); color: var(--ball); }
        .msearch-live { margin-left: auto; display: inline-flex; align-items: center; gap: 7px; font-size: .82rem; color: var(--soft); white-space: nowrap; font-variant-numeric: tabular-nums; }
        .msearch-live i { width: 7px; height: 7px; border-radius: 50%; background: #5BE08F; box-shadow: 0 0 8px #5BE08F; }
        @media (max-width: 760px) {
            .msearch { top: 84px; }
            .msearch-bar { grid-template-columns: 1fr 1fr; gap: 4px; }
            .msearch-q { grid-column: 1 / -1; }
            .msearch-field + .msearch-field, datalist + .msearch-field { border-left: 0; border-radius: 12px; }
            .msearch-date input, .msearch-time input { width: 100%; }
            .msearch-go { grid-column: 1 / -1; margin: 4px 0 0; justify-content: center; padding: 14px; }
            .msearch-kbd, .msearch-live { display: none; }
        }

        /* 2. Sport scoreboards */
        .sport { position: relative; min-height: 100svh; display: flex; align-items: center; padding: 110px 0 80px; overflow: hidden; }
        .sport::before { content: ''; position: absolute; inset: 0; }
        .sport::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, var(--night) 0%, rgba(10, 20, 48, .38) 22%, rgba(10, 20, 48, .38) 78%, var(--night) 100%); }
        .sport.fotbal::before { background: repeating-linear-gradient(90deg, #22744A 0 90px, #297F53 90px 180px); }
        .sport.baschet::before { background: repeating-linear-gradient(90deg, #B97A40 0 28px, #C4874B 28px 56px, #AA6D37 56px 84px); }
        .sport.tenis::before {
            background:
                linear-gradient(var(--line), var(--line)) 50% 0 / 4px 100% no-repeat,
                linear-gradient(var(--line), var(--line)) 0 30% / 100% 4px no-repeat,
                linear-gradient(var(--line), var(--line)) 0 70% / 100% 4px no-repeat,
                var(--hard);
            opacity: .9;
        }
        .sport .wrap { position: relative; z-index: 1; display: grid; grid-template-columns: .85fr 1.15fr; gap: 64px; align-items: center; }
        .sport h2 { font-size: clamp(4rem, 11vw, 9rem); }
        .sport .copy p { color: #D6DEF3; font-size: 1.15rem; margin: 18px 0 28px; max-width: 26em; }
        .sport .count { color: var(--accent); font-weight: 700; }

        /* Per-sport accent: headline, button, LED digits all follow the sport */
        .sport.fotbal { --accent: #5BE08F; }
        .sport.baschet { --accent: #FF8A3D; }
        .sport.tenis { --accent: #DCEB4B; }
        .sport h2 { color: var(--accent); text-shadow: 0 6px 40px color-mix(in srgb, var(--accent) 45%, transparent); }
        .sport .btn-ball { background: var(--accent); border-color: var(--accent); }
        .sport .board {
            color: var(--accent);
            border-color: color-mix(in srgb, var(--accent) 35%, #2A3557);
            text-shadow: 0 0 6px color-mix(in srgb, var(--accent) 70%, transparent), 0 0 18px color-mix(in srgb, var(--accent) 35%, transparent);
        }

        /* Sport cards (orbit around the ball) */
        .scard {
            --c: #fff;
            position: relative;
            border-radius: 18px; overflow: hidden; isolation: isolate;
            display: flex; flex-direction: column; padding: 20px;
            background: var(--surface);
            transition: transform .25s ease;
        }
        .scard:hover { transform: translateY(-6px); }
        .scard::after {
            content: ''; position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(180deg, rgba(10, 20, 48, .1) 30%, rgba(10, 20, 48, .92) 100%);
        }
        .scard h3 { font-size: 2.8rem; text-transform: none; }
        .scard .big { margin-top: auto; font: 900 6.5rem/.8 'Big Shoulders Display'; color: var(--c); }
        .scard .big-l { font-weight: 600; margin: 6px 0 16px; }
        .faces { display: flex; align-items: center; margin-bottom: 14px; }
        .faces span {
            width: 38px; height: 38px; border-radius: 50%; margin-right: -10px;
            display: grid; place-items: center; font-weight: 700; font-size: .8rem;
            border: 2px solid var(--night); background: var(--bg, #33467A);
        }
        .faces span.me { background: var(--c); color: var(--ink); }
        .faces .more { background: var(--night); color: var(--line); }
        .faces em { font-style: normal; margin-left: 20px; color: #D6DEF3; font-size: .95rem; }
        .scard button {
            width: 100%; padding: 13px; border-radius: 8px; cursor: pointer; font-weight: 700;
            background: rgba(10, 20, 48, .7); border: 2px solid var(--c); color: var(--line);
        }
        .scard button[aria-pressed="true"] { background: var(--c); color: var(--ink); }
        .scard a { color: var(--c); font-weight: 600; margin-top: 10px; text-align: center; text-decoration: none; }
        .scard a:hover { text-decoration: underline; }

        .board {
            background: #05091A; border: 3px solid #2A3557; border-radius: 10px;
            box-shadow: 0 30px 80px -20px rgba(0, 0, 0, .8), inset 0 0 0 2px #000;
            color: var(--led); padding: 22px 26px;
            text-shadow: 0 0 6px rgba(255, 181, 36, .7), 0 0 18px rgba(255, 181, 36, .35);
            background-image: radial-gradient(rgba(255, 255, 255, .05) 1px, transparent 1.2px); background-size: 5px 5px;
        }
        .board.goal { animation: goal 1.2s steps(2) 3; }
        @keyframes goal { 50% { background-color: #3A2600; } }
        .b-head { display: flex; justify-content: space-between; color: #8EA2D6; text-shadow: none; font-size: .95rem; margin-bottom: 14px; }
        .b-live::before { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #FF4D4D; margin-right: 8px; box-shadow: 0 0 8px #FF4D4D; animation: blink 1s steps(2) infinite; }
        @keyframes blink { 50% { opacity: .2; } }
        .b-score { display: grid; grid-template-columns: 1fr auto auto auto 1fr; align-items: center; gap: 14px; }
        .b-team { font-size: clamp(1rem, 2vw, 1.35rem); color: var(--line); text-shadow: 0 0 6px rgba(238, 242, 255, .4); }
        .b-team.r { text-align: right; }
        .b-num { font-size: clamp(3.4rem, 8vw, 5.6rem); line-height: 1; min-width: 1.2ch; text-align: center; }
        .b-sep { font-size: 2.6rem; opacity: .6; }
        .b-foot { display: flex; justify-content: space-between; gap: 16px; margin-top: 16px; padding-top: 14px; border-top: 2px dashed #2A3557; font-size: 1.05rem; }
        .b-foot small { color: #8EA2D6; text-shadow: none; font-size: .85rem; display: block; }
        .b-clock { font-size: 2.4rem; color: #FF6A3D; text-shadow: 0 0 8px rgba(255, 106, 61, .7); }
        .b-event { color: var(--line); text-shadow: none; min-height: 1.6em; }

        .t-grid { width: 100%; border-collapse: collapse; font-size: clamp(1.1rem, 2.6vw, 1.7rem); }
        .t-grid th { color: #8EA2D6; text-shadow: none; font: 500 .85rem 'Libre Franklin'; text-align: center; padding-bottom: 8px; }
        .t-grid th:first-child { text-align: left; }
        .t-grid td { text-align: center; padding: 8px 6px; border-top: 2px dashed #2A3557; }
        .t-grid td.name { text-align: left; color: var(--line); text-shadow: none; font-family: 'Libre Franklin'; font-weight: 600; font-size: 1.05rem; }
        .t-grid td.pts { color: var(--ball); text-shadow: 0 0 8px rgba(220, 235, 75, .6); }
        .serve { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--ball); margin-left: 8px; visibility: hidden; box-shadow: 0 0 8px var(--ball); }
        .serve.on { visibility: visible; }

        /* 3. Match finder */
        .finder { padding: 120px 0 100px; background: var(--night); }
        .finder h2, .strip-sec h2, .join h2 { font-size: clamp(2.8rem, 6vw, 4.6rem); }
        .finder .lede { color: var(--soft); margin: 12px 0 36px; max-width: 34em; }
        .filters { display: grid; grid-template-columns: 220px 1fr; gap: 18px 28px; align-items: center; margin-bottom: 28px; }
        .filters label, .filters .lab { color: var(--soft); font-weight: 500; }
        .filters select {
            font: 600 1rem 'Libre Franklin'; color: var(--line); background: var(--night-2);
            border: 2px solid var(--night-3); border-radius: 4px; padding: 10px 12px; width: 100%;
        }
        .chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip {
            padding: 8px 14px; border-radius: 999px; cursor: pointer;
            background: var(--night-2); border: 2px solid var(--night-3); font-weight: 500;
        }
        .chip:hover { border-color: var(--soft); }
        .chip[aria-pressed="true"] { background: var(--ball); border-color: var(--ball); color: var(--ink); }
        .result-count { color: var(--soft); margin-bottom: 10px; }
        .result-count b { color: var(--line); }
        .games { width: 100%; border-collapse: collapse; }
        .games th { text-align: left; font-weight: 500; color: var(--soft); padding: 10px 12px; border-bottom: 2px solid var(--night-3); }
        .games td { padding: 14px 12px; border-bottom: 1px solid var(--night-3); }
        .games tr.row { cursor: pointer; }
        .games tr.row:hover td, .games tr.row[aria-expanded="true"] td { background: var(--night-2); }
        .games .time { font: 800 1.5rem 'Big Shoulders Display'; }
        .tag { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; }
        .tag::before { content: ''; width: 10px; height: 10px; border-radius: 50%; background: var(--c); }
        .dots { display: flex; flex-wrap: wrap; gap: 4px; max-width: 130px; }
        .dots i { width: 10px; height: 10px; border-radius: 50%; background: var(--line); }
        .dots i.o { background: transparent; box-shadow: inset 0 0 0 2px var(--ball); }
        .detail td { background: var(--night-2); padding: 0 12px 22px; }
        .detail-inner { display: grid; grid-template-columns: repeat(3, 1fr) auto; gap: 24px; align-items: end; padding-top: 6px; }
        .detail dt { color: var(--soft); font-size: .88rem; }
        .detail dd { font-weight: 600; }
        .stepper { display: inline-flex; align-items: center; border: 2px solid var(--night-3); border-radius: 4px; margin-top: 4px; }
        .stepper button { width: 36px; height: 36px; background: none; border: 0; cursor: pointer; font-size: 1.2rem; }
        .stepper output { min-width: 28px; text-align: center; font-weight: 600; }
        .book { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .book .total { color: var(--soft); }
        .book .total b { color: var(--line); }
        .empty { padding: 40px 12px; color: var(--soft); }

        /* 4. Endless card strip */
        .strip-sec { padding: 100px 0 60px; overflow: hidden; }
        .strip-sec .wrap { margin-bottom: 36px; }
        .strip-sec .wrap p { color: var(--soft); margin-top: 10px; }
        .strip { display: flex; width: max-content; gap: 16px; animation: slide var(--d, 60s) linear infinite; padding: 8px 0; }
        .strip.rev { animation-direction: reverse; }
        .strip:hover { animation-play-state: paused; }
        @keyframes slide { to { transform: translateX(-50%); } }
        .card {
            width: 250px; flex: none; padding: 18px; border-radius: 10px; text-decoration: none;
            background: var(--night-2); border-top: 6px solid var(--c);
            transition: transform .2s;
        }
        .card:hover { transform: translateY(-4px) rotate(-1deg); }
        .card .t { font: 800 2rem/1 'Big Shoulders Display'; }
        .card .v { margin: 6px 0 12px; font-weight: 600; }
        .card .m { color: var(--soft); font-size: .92rem; }
        .card .m b { color: var(--ball); }

        /* 5. Join */
        .join { padding: 120px 0 0; text-align: center; background: radial-gradient(ellipse at 50% 100%, var(--night-3), var(--night) 65%); }
        .join p { color: var(--soft); font-size: 1.15rem; margin: 16px auto 32px; max-width: 30em; }
        .join .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        footer { margin-top: 120px; padding: 26px 0; border-top: 1px solid var(--night-3); color: var(--soft); font-size: .92rem; }
        footer .wrap { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }

        @media (max-width: 920px) {
            .sport .wrap { grid-template-columns: 1fr; gap: 36px; }
            .filters { grid-template-columns: 1fr; gap: 8px; }
            .filters .lab, .filters label { margin-top: 10px; }
            .detail-inner { grid-template-columns: 1fr 1fr; }
            .book { grid-column: 1 / -1; align-items: stretch; }
            .top nav a:not(.btn), .pager { display: none; }
            .games .hide-sm { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .strip { animation: none; }
            .strip-sec .track { overflow-x: auto; }
            .hint::after, .b-live::before, .board.goal { animation: none; }
        }
    </style>
</head>
<body>

@php
    $loginUrl = Route::has('login') ? route('login') : '#intra';
    $registerUrl = Route::has('register') ? route('register') : '#intra';
@endphp

<header class="top" id="top">
    <div class="wrap">
        <a href="#start" class="brand display">Sport<b>.md</b></a>
        <nav>
            <a href="#fotbal">Scoruri</a>
            <a href="#cauta">Caută meci</a>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-line">Contul meu</a>
            @else
                <a href="{{ $loginUrl }}" class="btn btn-line">Intră în cont</a>
            @endauth
        </nav>
    </div>
</header>

<nav class="pager" aria-label="Secțiuni">
    <a href="#start"><span class="sr">Început</span></a>
    <a href="#fotbal"><span class="sr">Fotbal</span></a>
    <a href="#baschet"><span class="sr">Baschet</span></a>
    <a href="#tenis"><span class="sr">Tenis</span></a>
    <a href="#cauta"><span class="sr">Caută meci</span></a>
    <a href="#intra"><span class="sr">Intră în cont</span></a>
</nav>

<main>
    {{-- 1. Opening scene: ball hero that turns into the sport orbit on scroll --}}
    <section class="scene" id="start" data-section aria-labelledby="orbit-title">
        <div class="stage">
            <div class="hero-zone">
                <div class="beam l"></div>
                <div class="beam r"></div>
                <div id="ball" aria-hidden="true"></div>
                <x-match-search />
                <div class="intro-copy">
                    <h1 class="display">Intră pe teren.</h1>
                    <p>Meciuri de fotbal, baschet și tenis în orașele Moldovei. Găsești jocul, îți ocupi locul și vii să joci.</p>
                    <span class="hint">Derulează ca să alegi sportul</span>
                </div>
            </div>

            <div class="orbit-head">
                <h2 class="display" id="orbit-title">Alege-ți sportul</h2>
                <p>Derulează ca să rotești sporturile. Apasă pe un card ca să-l aduci în față.</p>
            </div>
            <div class="orbit" id="orbit"></div>
            <div class="orbit-dots" id="orbit-dots" aria-label="Sporturi"></div>
        </div>
    </section>

    {{-- 2. Scoreboards --}}
    <section class="sport fotbal" id="fotbal" data-section>
        <div class="wrap">
            <div class="copy">
                <h2 class="display">Fotbal</h2>
                <p>Minifotbal 5×5 și 7×7 pe terenuri sintetice. Diseară sunt <span class="count">14 meciuri</span> în Chișinău.</p>
                <a href="#cauta" class="btn btn-ball" data-pick="fotbal">Vezi meciurile de fotbal</a>
            </div>
            <div class="board led-font" id="b-fotbal" aria-live="off">
                <div class="b-head"><span class="b-live">Live, Botanica</span><span>Liga de cartier</span></div>
                <div class="b-score">
                    <span class="b-team">BOTANICA</span>
                    <span class="b-num" data-k="h">2</span>
                    <span class="b-sep">:</span>
                    <span class="b-num" data-k="a">1</span>
                    <span class="b-team r">RÂȘCANI</span>
                </div>
                <div class="b-foot">
                    <span class="b-event" data-k="ev">Gol în minutul 54, Botanica</span>
                    <span class="b-clock" data-k="min">67'</span>
                </div>
            </div>
        </div>
    </section>

    <section class="sport baschet" id="baschet" data-section>
        <div class="wrap">
            <div class="copy">
                <h2 class="display">Baschet</h2>
                <p>3×3 în parcuri și 5×5 în săli. Diseară sunt <span class="count">9 meciuri</span> cu locuri libere.</p>
                <a href="#cauta" class="btn btn-ball" data-pick="baschet">Vezi meciurile de baschet</a>
            </div>
            <div class="board led-font" id="b-baschet">
                <div class="b-head"><span class="b-live">Live, Valea Morilor</span><span data-k="q">Sfertul 3</span></div>
                <div class="b-score">
                    <span class="b-team">GAZDE</span>
                    <span class="b-num" data-k="h">68</span>
                    <span class="b-sep">:</span>
                    <span class="b-num" data-k="g">64</span>
                    <span class="b-team r">OASPEȚI</span>
                </div>
                <div class="b-foot">
                    <span><small>Timp</small><span class="b-clock" data-k="clock">07:12</span></span>
                    <span style="text-align:right"><small>Posesie</small><span class="b-clock" data-k="shot">24</span></span>
                </div>
            </div>
        </div>
    </section>

    <section class="sport tenis" id="tenis" data-section>
        <div class="wrap">
            <div class="copy">
                <h2 class="display">Tenis</h2>
                <p>Simplu sau dublu, pe terenuri cu rezervare la oră. Diseară sunt <span class="count">6 terenuri</span> libere.</p>
                <a href="#cauta" class="btn btn-ball" data-pick="tenis">Vezi meciurile de tenis</a>
            </div>
            <div class="board led-font" id="b-tenis">
                <div class="b-head"><span class="b-live">Live, Râșcani</span><span>Meci amical</span></div>
                <table class="t-grid">
                    <thead><tr><th>Jucător</th><th>Set 1</th><th>Set 2</th><th>Set 3</th><th>Puncte</th></tr></thead>
                    <tbody>
                        <tr><td class="name">A. Rusu<span class="serve" data-k="s0"></span></td><td data-k="s0-0"></td><td data-k="s0-1"></td><td data-k="s0-2"></td><td class="pts" data-k="p0"></td></tr>
                        <tr><td class="name">M. Ciobanu<span class="serve" data-k="s1"></span></td><td data-k="s1-0"></td><td data-k="s1-1"></td><td data-k="s1-2"></td><td class="pts" data-k="p1"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- 3. Match finder --}}
    <section class="finder" id="cauta" data-section>
        <div class="wrap">
            <h2 class="display">Caută un meci</h2>
            <p class="lede">Alege orașul, ziua și ora. Apasă pe un meci ca să vezi detaliile și câte locuri vrei să ocupi.</p>

            <div class="filters">
                <label for="f-city">Oraș</label>
                <select id="f-city">
                    <option value="chisinau">Chișinău</option>
                    <option value="balti">Bălți</option>
                    <option value="cahul">Cahul</option>
                    <option value="orhei">Orhei</option>
                    <option value="ungheni">Ungheni</option>
                </select>
                <span class="lab">Ziua</span>
                <div class="chips" id="f-day"></div>
                <span class="lab">Ora</span>
                <div class="chips" id="f-time">
                    <button class="chip" data-v="all" aria-pressed="true">Toată ziua</button>
                    <button class="chip" data-v="morning" aria-pressed="false">Dimineața, 7–12</button>
                    <button class="chip" data-v="day" aria-pressed="false">După-amiaza, 12–18</button>
                    <button class="chip" data-v="evening" aria-pressed="false">Seara, 18–23</button>
                </div>
                <span class="lab">Sport</span>
                <div class="chips" id="f-sport">
                    <button class="chip" data-v="all" aria-pressed="true">Toate</button>
                    <button class="chip" data-v="fotbal" aria-pressed="false">Fotbal</button>
                    <button class="chip" data-v="baschet" aria-pressed="false">Baschet</button>
                    <button class="chip" data-v="tenis" aria-pressed="false">Tenis</button>
                </div>
            </div>

            <p class="result-count" id="count" aria-live="polite"></p>
            <table class="games">
                <thead>
                    <tr><th>Ora</th><th>Sport</th><th>Teren</th><th class="hide-sm">Nivel</th><th>Locuri</th><th class="hide-sm">Preț</th></tr>
                </thead>
                <tbody id="rows"></tbody>
            </table>
        </div>
    </section>

    {{-- 4. Endless strip --}}
    <section class="strip-sec" aria-labelledby="strip-title">
        <div class="wrap">
            <h2 class="display" id="strip-title">Urmează în Chișinău</h2>
            <p>Meciuri din următoarele zile. Oprește cursorul pe un card ca să-l citești.</p>
        </div>
        <div class="track"><div class="strip" id="strip-a" style="--d:70s"></div></div>
        <div class="track"><div class="strip rev" id="strip-b" style="--d:85s"></div></div>
    </section>

    {{-- 5. Join --}}
    <section class="join" id="intra" data-section>
        <div class="wrap">
            <h2 class="display">Locul tău e liber.</h2>
            <p>Cu un cont îți ocupi locul, primești ora și adresa, și vezi toate meciurile tale într-un singur panou.</p>
            <div class="actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-ball">Deschide panoul</a>
                @else
                    <a href="{{ $registerUrl }}" class="btn btn-ball">Creează cont gratuit</a>
                    <a href="{{ $loginUrl }}" class="btn btn-line">Am deja cont</a>
                @endauth
            </div>
        </div>
        <footer>
            <div class="wrap">
                <span>Sport.md, Moldova</span>
                <span>Proiect de licență, {{ date('Y') }}</span>
            </div>
        </footer>
    </section>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

{{-- 3D ball. Kept in its own module so a failed CDN import can't break the rest of the page.
     Reads window.sportScene.angle (set by the orbit timeline) so the ball spins with the cards. --}}
<script type="module">
    import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.169.0/build/three.module.min.js';

    const host = document.getElementById('ball');
    const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const shared = (window.sportScene ??= { angle: 0, cards: 8 });

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
    host.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.z = 6;
    const ball = new THREE.Group();
    scene.add(ball);

    // Wireframe: 14 meridians + 8 parallels on a unit sphere
    const pts = [], SEG = 72;
    const ring = (fn) => { for (let s = 0; s < SEG; s++) pts.push(...fn(s / SEG * Math.PI * 2), ...fn((s + 1) / SEG * Math.PI * 2)); };
    for (let i = 0; i < 14; i++) {
        const a = i / 14 * Math.PI;
        ring((t) => [Math.cos(t) * Math.cos(a), Math.sin(t), Math.cos(t) * Math.sin(a)]);
    }
    for (let j = 1; j < 9; j++) {
        const lat = -Math.PI / 2 + j / 9 * Math.PI, y = Math.sin(lat), r = Math.cos(lat);
        ring((t) => [r * Math.cos(t), y, r * Math.sin(t)]);
    }
    const wire = new THREE.BufferGeometry();
    wire.setAttribute('position', new THREE.Float32BufferAttribute(pts, 3));
    ball.add(new THREE.LineSegments(wire, new THREE.LineBasicMaterial({ color: 0xEEF2FF, transparent: true, opacity: .5 })));

    // Tennis seam as a real tube so it reads thicker than 1px WebGL lines
    const seam = [];
    for (let k = 0; k < 240; k++) {
        const t = k / 240 * Math.PI * 2;
        const v = new THREE.Vector3(.6 * Math.cos(t) + .4 * Math.cos(3 * t), .92 * Math.sin(2 * t), .6 * Math.sin(t) - .4 * Math.sin(3 * t));
        seam.push(v.normalize().multiplyScalar(1.01));
    }
    ball.add(new THREE.Mesh(
        new THREE.TubeGeometry(new THREE.CatmullRomCurve3(seam, true), 480, .022, 8, true),
        new THREE.MeshBasicMaterial({ color: 0xDCEB4B })
    ));

    // Camera flashes in the stands: points whose brightness decays after a random pop
    const FL = 90, flashPos = [], flashCol = new Float32Array(FL * 3);
    for (let i = 0; i < FL; i++) flashPos.push((Math.random() - .5) * 16, Math.random() * 5 - 1.5, -6 - Math.random() * 4);
    const flashGeo = new THREE.BufferGeometry();
    flashGeo.setAttribute('position', new THREE.Float32BufferAttribute(flashPos, 3));
    flashGeo.setAttribute('color', new THREE.BufferAttribute(flashCol, 3));
    scene.add(new THREE.Points(flashGeo, new THREE.PointsMaterial({ size: .09, vertexColors: true, transparent: true, blending: THREE.AdditiveBlending })));

    // Drag to spin
    let rotX = -.35, rotY = 0, vx = 0, vy = .005, drag = null;
    renderer.domElement.addEventListener('pointerdown', (e) => { drag = { x: e.clientX, y: e.clientY }; });
    addEventListener('pointermove', (e) => {
        if (!drag) return;
        vy = (e.clientX - drag.x) * .004; vx = (e.clientY - drag.y) * .004;
        drag = { x: e.clientX, y: e.clientY };
    });
    addEventListener('pointerup', () => { drag = null; });

    const resize = () => {
        const w = host.clientWidth, h = host.clientHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        // Ball radius ≈ 22% of the shorter side, converted from px to world units at z = 0
        const worldH = 2 * camera.position.z * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
        ball.scale.setScalar(.22 * Math.min(w, h) / h * worldH);
    };
    addEventListener('resize', resize);
    resize();

    const frame = () => {
        if (!drag) { vy += (.005 - vy) * .04; vx *= .92; }
        rotY += vy;
        rotX = THREE.MathUtils.clamp(rotX + vx, -1.2, 1.2);
        ball.rotation.set(rotX, rotY + shared.angle * (Math.PI * 2 / shared.cards) * .6, 0);

        for (let i = 0; i < FL; i++) {
            const c = flashCol[i * 3] * .9;
            const v = !still && Math.random() < .004 ? 1 : c;
            flashCol[i * 3] = v; flashCol[i * 3 + 1] = v * .96; flashCol[i * 3 + 2] = v * .86;
        }
        flashGeo.attributes.color.needsUpdate = true;
        renderer.render(scene, camera);
    };

    if (still) {
        frame();
        addEventListener('resize', frame);
    } else {
        let visible = true;
        new IntersectionObserver(([e]) => { visible = e.isIntersecting; }).observe(host);
        renderer.setAnimationLoop(() => { if (visible && !document.hidden) frame(); });
    }
</script>

<script>
(() => {
    const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const LOGIN = @json($registerUrl);
    const $ = (s, el = document) => el.querySelector(s);
    const header = $('#top');

    addEventListener('scroll', () => header.classList.toggle('solid', scrollY > innerHeight * .5), { passive: true });

    /* ---------- Sport carousel ---------- */
    const lines = (c) => `linear-gradient(${c},${c}) 50% 0/3px 100% no-repeat, linear-gradient(${c},${c}) 0 50%/100% 3px no-repeat`;
    const CAROUSEL = [
        { name: 'Fotbal', games: 24, fans: 66, c: '#5BE08F', to: '#fotbal', surface: 'repeating-linear-gradient(90deg,#22744A 0 50px,#2A8455 50px 100px)' },
        { name: 'Baschet', games: 17, fans: 41, c: '#FF8A3D', to: '#baschet', surface: 'repeating-linear-gradient(90deg,#B97A40 0 18px,#C4874B 18px 36px,#AA6D37 36px 54px)' },
        { name: 'Tenis', games: 12, fans: 23, c: '#DCEB4B', to: '#tenis', surface: `${lines('rgba(255,255,255,.7)')}, #2C4F9E` },
        { name: 'Volei', games: 9, fans: 49, c: '#FFD86B', to: '#cauta', surface: 'radial-gradient(rgba(255,255,255,.18) 1px, transparent 1.5px) 0 0/6px 6px, #B8925A' },
        { name: 'Handbal', games: 6, fans: 18, c: '#9EA8FF', to: '#cauta', surface: `${lines('rgba(255,255,255,.35)')}, #3B3F9E` },
        { name: 'Alergare', games: 15, fans: 72, c: '#FF7A6B', to: '#cauta', surface: 'repeating-linear-gradient(0deg,#B5432F 0 56px,rgba(255,255,255,.55) 56px 59px)' },
        { name: 'Tenis de masă', games: 8, fans: 14, c: '#7FE3F0', to: '#cauta', surface: `linear-gradient(rgba(255,255,255,.75),rgba(255,255,255,.75)) 50% 0/3px 100% no-repeat, #1E5E7A` },
        { name: 'Padel', games: 5, fans: 11, c: '#6BF2CF', to: '#cauta', surface: `${lines('rgba(255,255,255,.45)')}, #1F7F7A` },
    ];
    const INITIALS = ['IP', 'CM', 'VR', 'AL', 'DS', 'EC', 'MB', 'NT'];
    const FACE_BG = ['#3D5BA9', '#7A4BA0', '#A0524B', '#2F7D6D', '#8A6A2E'];
    const faces = (s, me) => {
        const shown = INITIALS.slice(0, 3).map((t, i) => `<span style="--bg:${FACE_BG[(i + s.fans) % 5]}">${t}</span>`);
        if (me) shown.unshift('<span class="me">Tu</span>');
        const total = s.fans + (me ? 1 : 0);
        return `${shown.join('')}<span class="more">+${total - 3 - (me ? 1 : 0)}</span><em>${total} interesați</em>`;
    };
    const orbitEl = $('#orbit'), dotsEl = $('#orbit-dots'), N = CAROUSEL.length;
    orbitEl.innerHTML = CAROUSEL.map((s, i) => `
        <article class="scard" data-i="${i}" style="--surface:${s.surface};--c:${s.c}" aria-label="${s.name}">
            <h3 class="display">${s.name}</h3>
            <div class="big">${s.games}</div>
            <div class="big-l">meciuri săptămâna asta</div>
            <div class="faces" data-faces="${i}">${faces(s, false)}</div>
            <button type="button" aria-pressed="false" data-like="${i}">Mă interesează</button>
            <a href="${s.to}">${s.to === '#cauta' ? 'Caută meciuri' : 'Vezi scorul live'}</a>
        </article>`).join('');
    dotsEl.innerHTML = CAROUSEL.map((s, i) => `<button type="button" data-go="${i}" style="--c:${s.c}" aria-label="${s.name}"></button>`).join('');
    const orbitCards = [...orbitEl.children];

    orbitEl.addEventListener('click', (e) => {
        const b = e.target.closest('[data-like]'); if (!b) return;
        const on = b.getAttribute('aria-pressed') !== 'true', i = +b.dataset.like;
        b.setAttribute('aria-pressed', on);
        b.textContent = on ? 'Te anunțăm' : 'Mă interesează';
        orbitEl.querySelector(`[data-faces="${i}"]`).innerHTML = faces(CAROUSEL[i], on);
    });

    /* ---------- Scroll scene: ball shrinks, cards orbit it (GSAP + ScrollTrigger) ---------- */
    const shared = (window.sportScene ??= { angle: 0, cards: N });
    shared.cards = N;

    if (window.gsap && window.ScrollTrigger && !still) {
        gsap.registerPlugin(ScrollTrigger);
        const scene = $('#start'), stage = $('.stage', scene), ballEl = $('#ball');
        scene.classList.add('is-orbit');

        // Timeline units: 1 = hero out + ball shrink, 0.6 = cards fly out, then 1 unit per card turn.
        const INTRO = 1, SPREAD = .6, START = INTRO + SPREAD, TOTAL = START + (N - 1);
        const orbit = { angle: 0, spread: 0 };
        let front = -1;

        const layout = () => {
            const w = stage.clientWidth, h = stage.clientHeight;
            const R = Math.min(w * .4, 560), tilt = h * .11, cy = h * .07;
            const a = orbit.angle;
            shared.angle = a;
            // The ball canvas covers the stage; once cards are out, let clicks reach them.
            ballEl.style.pointerEvents = orbit.spread > 0 ? 'none' : '';

            orbitCards.forEach((card, i) => {
                // Staggered fly-out: later cards leave the ball a little after earlier ones
                const s = gsap.utils.clamp(0, 1, orbit.spread * 1.5 - (i / N) * .5);
                const theta = (i - a) / N * Math.PI * 2;
                const depth = Math.cos(theta);              // 1 = front, -1 = back
                const near = (depth + 1) / 2;
                gsap.set(card, {
                    x: Math.sin(theta) * R * s,
                    y: cy + depth * tilt * s,
                    rotationY: -Math.sin(theta) * 32,
                    scale: (.55 + .45 * near) * (.35 + .65 * s),
                    autoAlpha: s * (.3 + .7 * near),
                    zIndex: depth > 0 ? 10 + Math.round(depth * 10) : 1,   // ball canvas sits at z 5
                    transformPerspective: 900,
                });
            });

            const f = ((Math.round(a) % N) + N) % N;
            if (f !== front) {
                front = f;
                orbitCards.forEach((c, i) => c.classList.toggle('is-front', i === f));
                dotsEl.querySelectorAll('button').forEach((d, i) => d.setAttribute('aria-current', i === f));
            }
        };

        const tl = gsap.timeline({
            defaults: { ease: 'none' },
            onUpdate: layout,
            scrollTrigger: {
                trigger: scene,
                start: 'top top',
                end: () => '+=' + innerHeight * (1.2 + (N - 1) * .55),
                pin: stage,
                scrub: .7,
                invalidateOnRefresh: true,
                snap: {
                    snapTo: [0, ...orbitCards.map((_, k) => (START + k) / TOTAL)],
                    duration: { min: .2, max: .6 },
                    delay: .08,
                    ease: 'power1.inOut',
                },
            },
        });

        tl.to('.intro-copy', { autoAlpha: 0, y: -90, duration: .6 }, 0)
          .to('.msearch', { autoAlpha: 0, y: -50, duration: .45 }, 0)
          .to('#ball', { scale: .5, yPercent: -16, duration: INTRO, ease: 'power2.inOut' }, 0)
          .to('.beam', { opacity: .9, duration: INTRO }, 0)
          .fromTo('.orbit-head', { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: .5 }, INTRO * .7)
          .fromTo('.orbit-dots', { autoAlpha: 0 }, { autoAlpha: 1, duration: .4 }, INTRO * .8)
          .to(orbit, { spread: 1, duration: SPREAD, ease: 'power2.out' }, INTRO * .75)
          .to(orbit, { angle: N - 1, duration: N - 1 }, START);
        layout();
        addEventListener('resize', layout);

        // Bring a given card to the front by scrolling to its point on the timeline
        const goTo = (k) => {
            const st = tl.scrollTrigger;
            scrollTo({ top: st.start + (START + k) / TOTAL * (st.end - st.start), behavior: 'smooth' });
        };
        orbitEl.addEventListener('click', (e) => {
            const card = e.target.closest('.scard');
            if (card && !card.classList.contains('is-front')) { e.preventDefault(); goTo(+card.dataset.i); }
        }, true);
        orbitEl.addEventListener('focusin', (e) => {
            const card = e.target.closest('.scard');
            if (card && !card.classList.contains('is-front')) goTo(+card.dataset.i);
        });
        dotsEl.addEventListener('click', (e) => { const d = e.target.closest('[data-go]'); if (d) goTo(+d.dataset.go); });
    }

    /* ---------- 2. Live scoreboards (demo data) ---------- */
    const ticker = (el, ms, step) => {
        let id = null;
        new IntersectionObserver(([e]) => {
            if (e.isIntersecting && !id) id = setInterval(step, ms);
            if (!e.isIntersecting && id) { clearInterval(id); id = null; }
        }).observe(el);
        step();
    };
    const k = (board, key) => board.querySelector(`[data-k="${key}"]`);
    const pad = (n) => String(n).padStart(2, '0');

    const fb = $('#b-fotbal'), fs = { min: 66, h: 2, a: 1 };
    ticker(fb, 1400, () => {
        fs.min++;
        if (fs.min > 90) { fs.min = 1; fs.h = 0; fs.a = 0; k(fb, 'ev').textContent = 'Începe un meci nou'; }
        if (Math.random() < .07) {
            const home = Math.random() < .5;
            home ? fs.h++ : fs.a++;
            k(fb, 'ev').textContent = `GOL! Minutul ${fs.min}, ${home ? 'Botanica' : 'Râșcani'}`;
            fb.classList.remove('goal'); void fb.offsetWidth; fb.classList.add('goal');
        }
        k(fb, 'h').textContent = fs.h; k(fb, 'a').textContent = fs.a; k(fb, 'min').textContent = fs.min + "'";
    });

    const bb = $('#b-baschet'), bs = { q: 3, clock: 432, shot: 24, h: 68, g: 64 };
    ticker(bb, 1000, () => {
        bs.clock--; bs.shot--;
        if (Math.random() < .16) {
            const pts = Math.random() < .25 ? 3 : 2;
            Math.random() < .5 ? bs.h += pts : bs.g += pts;
            bs.shot = 24;
        }
        if (bs.shot <= 0) bs.shot = 24;
        if (bs.clock <= 0) { bs.q++; bs.clock = 600; if (bs.q > 4) Object.assign(bs, { q: 1, h: 0, g: 0 }); }
        k(bb, 'h').textContent = bs.h; k(bb, 'g').textContent = bs.g;
        k(bb, 'clock').textContent = `${pad(Math.floor(bs.clock / 60))}:${pad(bs.clock % 60)}`;
        k(bb, 'shot').textContent = pad(bs.shot);
        k(bb, 'q').textContent = `Sfertul ${bs.q}`;
    });

    const tb = $('#b-tenis'), ts = { sets: [[6, 4], [3, 2]], pts: [2, 1], srv: 0 };
    const LABEL = ['0', '15', '30', '40'];
    const tennisPoint = () => {
        const w = Math.random() < .5 ? 0 : 1, l = 1 - w;
        ts.pts[w]++;
        if (ts.pts[w] >= 4 && ts.pts[w] - ts.pts[l] >= 2) {
            const set = ts.sets[ts.sets.length - 1];
            set[w]++; ts.pts = [0, 0]; ts.srv = 1 - ts.srv;
            if (set[w] >= 6 && set[w] - set[l] >= 2) ts.sets.length === 3 ? ts.sets = [[0, 0]] : ts.sets.push([0, 0]);
        }
    };
    const tennisRender = () => {
        const [a, b] = ts.pts, deuce = a >= 3 && b >= 3;
        const label = (me, op) => deuce ? (me > op ? 'AD' : '40') : LABEL[me];
        for (let pl = 0; pl < 2; pl++) {
            for (let s = 0; s < 3; s++) k(tb, `s${pl}-${s}`).textContent = ts.sets[s] ? ts.sets[s][pl] : '';
            k(tb, `p${pl}`).textContent = label(ts.pts[pl], ts.pts[1 - pl]);
            k(tb, `s${pl}`).classList.toggle('on', ts.srv === pl);
        }
    };
    ticker(tb, 1800, () => { tennisPoint(); tennisRender(); });

    /* ---------- 3. Match finder (generated demo data) ---------- */
    const SPORTS = {
        fotbal: { name: 'Fotbal', c: '#3FBF7A', sizes: [10, 12, 14], price: [50, 80], dur: 90, bring: 'Ghete pentru sintetic, tricou deschis și unul închis' },
        baschet: { name: 'Baschet', c: '#FF8A3D', sizes: [6, 10], price: [30, 50], dur: 60, bring: 'Încălțăminte de sală, apă' },
        tenis: { name: 'Tenis', c: '#DCEB4B', sizes: [2, 4], price: [100, 160], dur: 60, bring: 'Rachetă (se poate închiria pe loc)' },
    };
    const VENUES = {
        chisinau: { fotbal: ['Teren sintetic Botanica', 'Arena Ciocana', 'Zimbru, terenul 2'], baschet: ['Parcul Valea Morilor', 'Sala USM', 'Terenul din Buiucani'], tenis: ['Terenurile din Râșcani', 'Tenis Club Centru', 'Complexul Dinamo'] },
        balti: { fotbal: ['Stadionul Olimpia, sintetic', 'Teren Pămînteni'], baschet: ['Sala USB', 'Parcul Central'], tenis: ['Tenis Club Bălți'] },
        cahul: { fotbal: ['Teren sintetic Centru', 'Arena Lac'], baschet: ['Sala Sporturilor Cahul'], tenis: ['Terenurile de la lac'] },
        orhei: { fotbal: ['Orhei Arena', 'Teren școala nr. 3'], baschet: ['Parcul Ivancea'], tenis: ['Tenis Orhei'] },
        ungheni: { fotbal: ['Teren sintetic Ungheni', 'Stadionul Zimbru Ungheni'], baschet: ['Sala Sporturilor'], tenis: ['Terenul de la parc'] },
    };
    const LEVELS = ['Începător', 'Mediu', 'Avansat'];
    const ORGS = ['Ion P.', 'Cristina M.', 'Victor R.', 'Ana L.', 'Dan S.', 'Elena C.', 'Mihai B.'];

    const seeded = (str) => {
        let h = 1779033703 ^ str.length;
        for (let i = 0; i < str.length; i++) h = Math.imul(h ^ str.charCodeAt(i), 3432918353), h = h << 13 | h >>> 19;
        return () => { h = Math.imul(h ^ h >>> 16, 2246822507); h = Math.imul(h ^ h >>> 13, 3266489909); return ((h ^= h >>> 16) >>> 0) / 4294967296; };
    };
    const pick = (rnd, arr) => arr[Math.floor(rnd() * arr.length)];

    const gamesFor = (city, dayIdx) => {
        const rnd = seeded(city + dayIdx), out = [];
        for (const sport of Object.keys(SPORTS)) {
            const s = SPORTS[sport], n = 3 + Math.floor(rnd() * 4);
            for (let i = 0; i < n; i++) {
                const total = pick(rnd, s.sizes);
                out.push({
                    sport, total,
                    hour: 7 + Math.floor(rnd() * 15), min: rnd() < .5 ? 0 : 30,
                    venue: pick(rnd, VENUES[city][sport]),
                    level: pick(rnd, LEVELS), org: pick(rnd, ORGS),
                    taken: Math.min(total, Math.floor(rnd() * (total + 1))),
                    price: Math.round((s.price[0] + rnd() * (s.price[1] - s.price[0])) / 5) * 5,
                });
            }
        }
        return out.sort((a, b) => a.hour * 60 + a.min - (b.hour * 60 + b.min));
    };

    const RANGES = { all: [0, 24], morning: [7, 12], day: [12, 18], evening: [18, 23] };
    const state = { city: 'chisinau', day: 0, time: 'all', sport: 'all', open: null };
    const DAYS = ['dum.', 'lun.', 'mar.', 'mie.', 'joi', 'vin.', 'sâm.'];

    const dayChips = $('#f-day');
    for (let i = 0; i < 7; i++) {
        const d = new Date(); d.setDate(d.getDate() + i);
        const label = i === 0 ? 'Azi' : i === 1 ? 'Mâine' : `${DAYS[d.getDay()]} ${d.getDate()}`;
        dayChips.insertAdjacentHTML('beforeend', `<button class="chip" data-v="${i}" aria-pressed="${i === 0}">${label}</button>`);
    }

    const bindChips = (el, key) => el.addEventListener('click', (ev) => {
        const b = ev.target.closest('.chip'); if (!b) return;
        setChip(el, b.dataset.v);
        state[key] = key === 'day' ? +b.dataset.v : b.dataset.v;
        state.open = null; render();
    });
    const setChip = (el, v) => el.querySelectorAll('.chip').forEach((c) => c.setAttribute('aria-pressed', c.dataset.v === String(v)));
    bindChips(dayChips, 'day'); bindChips($('#f-time'), 'time'); bindChips($('#f-sport'), 'sport');
    $('#f-city').addEventListener('change', (e) => { state.city = e.target.value; state.open = null; render(); });

    document.querySelectorAll('[data-pick]').forEach((a) => a.addEventListener('click', () => {
        state.sport = a.dataset.pick; state.open = null; setChip($('#f-sport'), state.sport); render();
    }));

    const dots = (g) => '<span class="dots">' + Array.from({ length: g.total }, (_, i) => `<i class="${i < g.taken ? '' : 'o'}"></i>`).join('') + '</span>';
    const fmt = (g) => `${pad(g.hour)}:${pad(g.min)}`;
    let current = [];

    const render = () => {
        const [from, to] = RANGES[state.time];
        current = gamesFor(state.city, state.day).filter((g) =>
            g.hour >= from && g.hour < to && (state.sport === 'all' || g.sport === state.sport));
        const free = current.reduce((s, g) => s + g.total - g.taken, 0);
        $('#count').innerHTML = current.length
            ? `<b>${current.length} meciuri</b>, ${free} locuri libere`
            : '';

        $('#rows').innerHTML = current.length ? current.map((g, i) => {
            const s = SPORTS[g.sport], left = g.total - g.taken, open = state.open === i;
            const row = `<tr class="row" tabindex="0" data-i="${i}" aria-expanded="${open}">
                <td class="time">${fmt(g)}</td>
                <td><span class="tag" style="--c:${s.c}">${s.name}</span></td>
                <td>${g.venue}</td>
                <td class="hide-sm">${g.level}</td>
                <td>${dots(g)}</td>
                <td class="hide-sm">${g.price} lei</td></tr>`;
            if (!open) return row;
            return row + `<tr class="detail"><td colspan="6"><div class="detail-inner">
                <dl><dt>Durată</dt><dd>${s.dur} min</dd></dl>
                <dl><dt>Organizator</dt><dd>${g.org}</dd></dl>
                <dl><dt>Ce iei cu tine</dt><dd>${s.bring}</dd></dl>
                <div class="book">
                    ${left ? `<span>Câte persoane vin cu tine?
                        <span class="stepper"><button data-step="-1" aria-label="Mai puțini">−</button><output id="qty">1</output><button data-step="1" aria-label="Mai mulți">+</button></span></span>
                        <span class="total">Total: <b id="sum">${g.price} lei</b></span>
                        <a class="btn btn-ball" id="take" href="${LOGIN}">Ocupă 1 loc</a>`
                    : `<span class="total">Meciul e complet. Alege altă oră.</span>`}
                </div></div></td></tr>`;
        }).join('') : `<tr><td colspan="6" class="empty">Niciun meci în intervalul ăsta. Încearcă altă oră sau altă zi.</td></tr>`;
    };

    $('#rows').addEventListener('click', (e) => {
        const step = e.target.closest('[data-step]');
        if (step) {
            const g = current[state.open], q = $('#qty');
            const n = Math.min(g.total - g.taken, Math.max(1, +q.value + +step.dataset.step));
            q.value = n;
            $('#sum').textContent = `${n * g.price} lei`;
            $('#take').textContent = `Ocupă ${n} ${n === 1 ? 'loc' : 'locuri'}`;
            return;
        }
        const row = e.target.closest('tr.row');
        if (!row) return;
        state.open = state.open === +row.dataset.i ? null : +row.dataset.i;
        render();
    });
    $('#rows').addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('tr.row')) {
            e.preventDefault();
            const i = +e.target.dataset.i;
            state.open = state.open === i ? null : i; render();
            $(`tr.row[data-i="${i}"]`).focus();
        }
    });
    render();

    /* ---------- 4. Endless strip ---------- */
    const cards = (dayIdx) => gamesFor('chisinau', dayIdx).filter((g) => g.taken < g.total).slice(0, 10).map((g) => {
        const s = SPORTS[g.sport], left = g.total - g.taken;
        return `<a class="card" href="#cauta" style="--c:${s.c}">
            <div class="t">${fmt(g)}</div><div class="v">${g.venue}</div>
            <div class="m">${s.name}, ${g.level.toLowerCase()}. <b>${left} ${left === 1 ? 'loc liber' : 'locuri libere'}</b></div></a>`;
    }).join('');
    // Content is doubled so the -50% translate loops seamlessly.
    const a = cards(1), b = cards(2);
    $('#strip-a').innerHTML = a + a;
    $('#strip-b').innerHTML = b + b;

    /* ---------- Pager ---------- */
    const links = document.querySelectorAll('.pager a');
    const io = new IntersectionObserver((es) => es.forEach((e) => {
        if (e.isIntersecting) links.forEach((l) => l.setAttribute('aria-current', l.hash === '#' + e.target.id));
    }), { rootMargin: '-45% 0px -45% 0px' });
    document.querySelectorAll('[data-section]').forEach((s) => io.observe(s));
})();
</script>
</body>
</html>
