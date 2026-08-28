<?php
/**
 * SRM Event Connect — animated About section.
 * Drop-in replacement for the supplied partial included by views/index.view.php.
 * Self-contained: PHP, scoped CSS and vanilla JS. No CDN or build step.
 * Keep the existing images/About/ files and their exact filenames.
 */
$about_team_groups = [
    [
        'label' => 'Faculty Guidance & Event Leadership',
        'tier' => 'leadership',
        'members' => [
            ['name' => 'Dr. Pushpalatha M', 'role' => 'Professor and Associate Chairperson', 'image' => 'images/About/Dr. Pushpalatha M & Professor and Associate Chairperson.jpeg'],
        ],
    ],
    [
        'label' => 'Chief Patron',
        'tier' => 'leadership',
        'members' => [
            ['name' => 'Dr. Niranjana G', 'role' => 'Professor and Head', 'image' => 'images/About/Dr. Niranjana G Professor and Head.jpeg'],
        ],
    ],
    [
        'label' => 'Mentors',
        'tier' => 'mentor',
        'members' => [
            ['name' => 'Dr. Usha G', 'role' => 'Professor', 'image' => 'images/About/Dr. Usha G & Professor.jpeg'],
            ['name' => 'Dr. Thamizhamuthu R', 'role' => 'Assistant Professor', 'image' => 'images/About/Dr. Thamizhamuthu R & Assistant Professor.jpeg'],
        ],
    ],
    [
        'label' => 'Innovation Team',
        'tier' => 'developer',
        'members' => [
            ['name' => 'Vishok Manikandan', 'role' => 'Developer', 'image' => 'images/About/Vishok Manikandan Student.png'],
            ['name' => 'Veeraa Vikash', 'role' => 'Developer', 'image' => 'images/About/Veeraa Vikash  Student.png'],
        ],
    ],
];

// Local closures avoid global function collisions if the partial is included twice.
$ec_escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$ec_portrait = static function ($member) use ($ec_escape) {
    $name = preg_replace('/^(?:(?:Dr|Prof|Professor)\.?\s+)+/i', '', trim($member['name']));
    $words = preg_split('/\s+/', $name);
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $letter = preg_replace('/[^a-zA-Z]/', '', $word);
        if ($letter !== '') $initials .= strtoupper($letter[0]);
    }
    // Resolve against the partial's original project root, not the current cwd.
    $root = __DIR__ . '/../../';
    $has_image = is_file($root . $member['image']);
    echo '<span class="ec-portrait" role="img" aria-label="' . $ec_escape($member['name']) . '">';
    echo '<span class="ec-portrait__initials" aria-hidden="true">' . $ec_escape($initials ?: '?') . '</span>';
    if ($has_image) {
        // Encode each segment separately so spaces and ampersands are safe in URLs.
        $url = implode('/', array_map('rawurlencode', explode('/', $member['image'])));
        echo '<img class="ec-portrait__image" src="' . $ec_escape($url) . '" alt="" aria-hidden="true" loading="lazy" decoding="async" width="320" height="320">';
    }
    echo '</span>';
};
$ec_tiers = [
    ['key' => 'leadership', 'index' => '01', 'title' => 'Leadership', 'subtitle' => 'The vision behind the platform.'],
    ['key' => 'mentor', 'index' => '02', 'title' => 'Mentorship', 'subtitle' => 'Guidance at every step.'],
    ['key' => 'developer', 'index' => '03', 'title' => 'Innovation team', 'subtitle' => 'Turning the vision into reality.'],
];
?>

<style>
/* Everything is scoped to #about.ec-about; no Tailwind or global .js dependency. */
#about.ec-about {
    --ec-ink: #eef3ff;
    --ec-muted: #a4b1ce;
    --ec-blue: #7a9dff;
    --ec-mint: #83f4d4;
    --ec-line: rgba(170, 193, 255, .16);
    --ec-surface: #070b18;
    position: relative;
    isolation: isolate;
    overflow: hidden;
    color: var(--ec-ink);
    background: var(--ec-surface);
    font-family: "Inter", "Segoe UI", Arial, sans-serif;
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    scroll-margin-top: 64px;
    color-scheme: dark;
}
#about.ec-about *, #about.ec-about *::before, #about.ec-about *::after { box-sizing: border-box; }
#about :where(h2, h3, h4, p) { margin: 0; }
#about.ec-about button, #about.ec-about a { -webkit-tap-highlight-color: transparent; }
#about.ec-about button { font: inherit; }
#about.ec-about a { color: inherit; text-decoration: none; }
#about.ec-about svg { display: block; }
#about.ec-about ::selection { color: #081327; background: #a6e4ff; }
#about.ec-about :focus-visible { outline: 2px solid var(--ec-mint); outline-offset: 6px; }
#about .ec-shell { position: relative; z-index: 2; width: min(1160px, calc(100% - 96px)); margin-inline: auto; }
#about .ec-sky, #about .ec-stars, #about .ec-grid, #about .ec-noise { position: absolute; inset: 0; pointer-events: none; }
#about .ec-sky { overflow: hidden; }
#about .ec-sky::before {
    content: ""; position: absolute; width: 1100px; height: 1100px; top: -510px; right: -150px;
    background: radial-gradient(ellipse, rgba(41, 85, 216, .28), transparent 66%);
    animation: ec-breathe 16s ease-in-out infinite alternate;
}
#about .ec-sky::after {
    content: ""; position: absolute; width: 950px; height: 1150px; bottom: -380px; left: -400px;
    background: radial-gradient(ellipse, rgba(30, 144, 130, .18), transparent 67%);
    animation: ec-breathe 21s ease-in-out -8s infinite alternate-reverse;
}
#about .ec-grid {
    background-image: linear-gradient(rgba(141, 168, 233, .045) 1px, transparent 1px), linear-gradient(90deg, rgba(141, 168, 233, .045) 1px, transparent 1px);
    background-size: 76px 76px;
    -webkit-mask-image: linear-gradient(#000, transparent 40%, rgba(0, 0, 0, .7));
    mask-image: linear-gradient(#000, transparent 40%, rgba(0, 0, 0, .7));
}
#about .ec-stars { width: 100%; height: 100%; opacity: .62; }
#about .ec-noise {
    opacity: .035;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.78' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Cpath fill='%23fff' filter='url(%23n)' d='M0 0h180v180H0z'/%3E%3C/svg%3E");
}
#about .ec-topline { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding-top: 36px; }
#about .ec-brand { display: flex; align-items: center; gap: 12px; }
#about .ec-brand__mark { display: grid; place-items: center; width: 33px; height: 33px; border: 1px solid rgba(161, 184, 255, .35); border-radius: 10px; background: rgba(144, 165, 233, .07); }
#about .ec-brand__text { font-size: 12px; letter-spacing: .1em; font-weight: 650; text-transform: uppercase; }
#about .ec-motion {
    display: none; align-items: center; gap: 9px; padding: 10px 14px; min-height: 42px;
    color: #bac7e4; border: 1px solid var(--ec-line); border-radius: 30px; background: rgba(12, 21, 41, .75);
    cursor: pointer; font-size: 11px; letter-spacing: .025em; transition: background .2s, border-color .2s;
}
#about.ec-ready .ec-motion { display: inline-flex; }
#about .ec-motion:hover { border-color: #668bb8; background: #17253f; }
#about .ec-motion__icon { width: 9px; height: 10px; border-left: 2px solid currentColor; border-right: 2px solid currentColor; }
#about.ec-paused .ec-motion__icon { width: 0; height: 0; border: 5px solid transparent; border-right: 0; border-left: 8px solid currentColor; }
#about .ec-hero { display: grid; grid-template-columns: 1.4fr .85fr; align-items: center; gap: 28px; padding: 104px 0 96px; }
#about .ec-eyebrow { display: flex; align-items: center; gap: 9px; color: #a5b8df; font-size: 10px; font-weight: 650; line-height: 1.7; letter-spacing: .18em; text-transform: uppercase; }
#about .ec-live-dot { flex: 0 0 6px; width: 6px; height: 6px; border-radius: 50%; background: var(--ec-mint); box-shadow: 0 0 16px rgba(131, 244, 212, .75); }
#about .ec-hero h2 { margin-top: 24px; font-size: clamp(42px, 5.8vw, 77px); font-weight: 600; line-height: 1.055; letter-spacing: -.067em; }
#about .ec-title-line { display: block; }
#about .ec-title-accent {
    color: #8cbbff;
    background: linear-gradient(105deg, #fff 0%, #b0c8ff 18%, #6d9aff 43%, #98f4e3 68%, #b0c8ff 87%, #fff 100%);
    background-size: 230% 100%; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
    animation: ec-spectrum 13s ease-in-out infinite alternate;
}
#about .ec-hero__description { margin-top: 26px; max-width: 410px; color: var(--ec-muted); font-size: 15px; line-height: 1.8; }
#about .ec-hero__foot { display: flex; align-items: center; gap: 28px; margin-top: 32px; }
#about .ec-explore { display: inline-flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 600; }
#about .ec-explore__arrow { display: grid; place-items: center; width: 36px; height: 36px; border: 1px solid var(--ec-line); border-radius: 50%; transition: transform .25s, background .25s; }
#about .ec-explore:hover .ec-explore__arrow { transform: translateY(3px); background: #1a3151; }
#about .ec-team-count { padding-left: 22px; border-left: 1px solid var(--ec-line); font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 10px; line-height: 1.7; color: #94a5c6; }
#about .ec-team-count strong { color: #e0e9fc; font-weight: 500; }

/* Orbital composition: abstract geometry, animated independently of the text. */
#about .ec-orbital { position: relative; aspect-ratio: 1; width: 100%; max-width: 420px; justify-self: end; }
#about .ec-orbital__glow { position: absolute; inset: 4%; border-radius: 50%; background: radial-gradient(circle, rgba(87, 147, 255, .17), rgba(54, 91, 221, .06) 35%, transparent 70%); animation: ec-orb-glow 8s ease-in-out infinite alternate; }
#about .ec-orbit { position: absolute; border-radius: 50%; border: 1px solid rgba(130, 163, 235, .22); }
#about .ec-orbit--one { inset: 3%; border-style: dashed; border-color: rgba(130, 163, 235, .17); animation: ec-spin 120s linear infinite; }
#about .ec-orbit--two { inset: 15%; transform: rotate(-25deg); border-color: rgba(145, 185, 255, .35); animation: ec-spin 40s linear infinite reverse; }
#about .ec-orbit--three { inset: 28%; border-color: rgba(122, 166, 255, .16); animation: ec-spin 24s linear infinite; }
#about .ec-orbit::before { content: ""; position: absolute; width: 7px; height: 7px; left: 50%; top: -4px; margin-left: -3px; border-radius: 50%; background: #90c5ff; box-shadow: 0 0 14px #5d94ff; }
#about .ec-orbit--two::before { width: 10px; height: 10px; top: -5px; background: var(--ec-mint); box-shadow: 0 0 22px #73d9bf; }
#about .ec-orbit--three::before { width: 4px; height: 4px; top: -2px; background: #e7efff; }
#about .ec-orbital__axis { position: absolute; left: 50%; top: 0; width: 1px; height: 100%; background: linear-gradient(transparent, rgba(143, 174, 238, .22), transparent); transform: rotate(45deg); }
#about .ec-orbital__axis--two { transform: rotate(-45deg); }
#about .ec-orbital__center { position: absolute; inset: 35%; display: grid; place-content: center; text-align: center; border: 1px solid rgba(166, 199, 255, .35); border-radius: 50%; background: radial-gradient(circle at 30% 15%, #1b3158, #0d1427 75%); box-shadow: 0 0 40px #527fff19, inset 0 0 25px #7da6ff0a; }
#about .ec-orbital__center span { font-size: clamp(24px, 3vw, 40px); font-weight: 550; letter-spacing: -.08em; }
#about .ec-orbital__center small { margin-top: 5px; font-size: 7px; letter-spacing: .16em; color: #94abc9; text-transform: uppercase; }
#about .ec-orbit-tag { position: absolute; z-index: 1; padding: 9px 13px; border: 1px solid rgba(124, 159, 219, .25); border-radius: 7px; background: #0b1428; box-shadow: 0 8px 35px #02061188; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 9px; letter-spacing: .04em; color: #c6d5f2; animation: ec-tag-float 7s ease-in-out infinite alternate; }
#about .ec-orbit-tag::before { content: ""; display: inline-block; width: 4px; height: 4px; margin-right: 7px; background: var(--ec-mint); border-radius: 50%; vertical-align: 2px; }
#about .ec-orbit-tag--one { top: 15%; left: 6%; }
#about .ec-orbit-tag--two { top: 48%; right: -2%; animation-delay: -2s; }
#about .ec-orbit-tag--three { bottom: 5%; left: 23%; animation-delay: -4s; }
#about .ec-orbital__caption { position: absolute; right: 3%; bottom: -20px; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; color: #6881ac; font-size: 8px; letter-spacing: .2em; text-transform: uppercase; }

/* A narrow animated light track joins the story to the people. */
#about .ec-divider { height: 1px; position: relative; background: var(--ec-line); }
#about .ec-divider::after { content: ""; position: absolute; top: -1px; left: 0; width: 140px; height: 2px; background: linear-gradient(90deg, transparent, #82d9ff, transparent); box-shadow: 0 0 16px #65c9ff66; animation: ec-travel 10s ease-in-out infinite; }
#about .ec-team { padding: 58px 0 70px; }
#about .ec-tier { --ec-accent: #9db8ff; --ec-accent-rgb: 132, 168, 255; margin-top: 58px; scroll-margin-top: 90px; }
#about .ec-tier:first-child { margin-top: 0; }
#about .ec-tier--mentor { --ec-accent: #c6b9ff; --ec-accent-rgb: 190, 164, 255; }
#about .ec-tier--developer { --ec-accent: #88efd2; --ec-accent-rgb: 102, 229, 195; }
#about .ec-tier__header { display: flex; align-items: center; gap: 18px; margin-bottom: 24px; }
#about .ec-tier__index { display: grid; place-items: center; flex: 0 0 32px; height: 32px; border: 1px solid rgba(var(--ec-accent-rgb), .25); color: var(--ec-accent); border-radius: 50%; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 10px; }
#about .ec-tier__header h3 { font-size: 18px; font-weight: 550; letter-spacing: -.035em; }
#about .ec-tier__rule { height: 1px; flex: 1; background: var(--ec-line); }
#about .ec-tier__subtitle { color: #8798ba; font-size: 11px; }
#about .ec-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px; }
#about .ec-card-wrap { min-width: 0; perspective: 1000px; }
#about .ec-card {
    --ec-x: 50%; --ec-y: 50%; --ec-rx: 0deg; --ec-ry: 0deg;
    position: relative; display: flex; align-items: center; gap: 28px; min-height: 208px; padding: 30px;
    height: 100%; border: 1px solid rgba(var(--ec-accent-rgb), .19); border-radius: 18px;
    background: linear-gradient(135deg, rgba(25, 35, 61, .84), rgba(11, 17, 33, .95));
    transform: rotateX(var(--ec-rx)) rotateY(var(--ec-ry));
    transition: transform .25s ease-out, border-color .35s, box-shadow .35s;
    box-shadow: inset 0 1px 0 rgba(191, 212, 255, .04), 0 10px 24px rgba(0, 0, 0, .08);
}
#about .ec-card::before { content: ""; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; opacity: 0; background: radial-gradient(350px circle at var(--ec-x) var(--ec-y), rgba(var(--ec-accent-rgb), .13), transparent 75%); transition: opacity .3s; }
#about .ec-card::after { content: ""; position: absolute; left: 24px; right: 24px; top: -1px; height: 1px; background: linear-gradient(90deg, transparent, rgba(var(--ec-accent-rgb), .68), transparent); opacity: .4; transition: opacity .3s; }
#about .ec-card:hover { border-color: rgba(var(--ec-accent-rgb), .48); box-shadow: 0 16px 55px rgba(0, 0, 0, .2), 0 0 35px rgba(var(--ec-accent-rgb), .055); }
#about .ec-card:hover::before, #about .ec-card:hover::after { opacity: 1; }
#about .ec-card__content { position: relative; z-index: 1; min-width: 0; }
#about .ec-card__label { color: var(--ec-accent); font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 10px; letter-spacing: .08em; line-height: 1.8; text-transform: uppercase; }
#about .ec-card h4 { margin-top: 10px; color: #f0f4ff; font-size: 22px; font-weight: 550; letter-spacing: -.035em; line-height: 1.18; overflow-wrap: anywhere; }
#about .ec-card__role { margin-top: 10px; color: #a4b0ca; font-size: 13px; line-height: 1.65; }
#about .ec-card__serial { position: absolute; top: 18px; right: 20px; color: #6f809f; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 9px; letter-spacing: .1em; }
#about .ec-portrait-frame { flex: 0 0 112px; width: 112px; height: 112px; padding: 7px; position: relative; border-radius: 50%; }
#about .ec-portrait-frame::before { content: ""; position: absolute; inset: 0; border: 1px solid rgba(var(--ec-accent-rgb), .22); border-radius: inherit; }
#about .ec-portrait-frame::after { content: ""; position: absolute; inset: 0; border: 1px solid transparent; border-top-color: rgba(var(--ec-accent-rgb), .85); border-left-color: rgba(var(--ec-accent-rgb), .2); border-radius: inherit; animation: ec-spin 18s linear infinite; }
#about .ec-portrait { position: relative; z-index: 1; display: block; height: 100%; width: 100%; border-radius: 50%; overflow: hidden; background: #17233e; }
#about .ec-portrait__initials { position: absolute; inset: 0; display: grid; place-items: center; color: var(--ec-accent); font-size: 31px; font-weight: 450; letter-spacing: -.065em; background: radial-gradient(circle at 35% 20%, rgba(var(--ec-accent-rgb), .13), transparent 70%); }
#about .ec-portrait__image { display: block; position: relative; width: 100%; height: 100%; object-fit: cover; object-position: center 20%; border-radius: inherit; transition: transform .7s cubic-bezier(.2, .75, .25, 1); }
#about .ec-card:hover .ec-portrait__image { transform: scale(1.045); }
#about .ec-tier--leadership .ec-card { min-height: 294px; padding: 42px 28px; gap: 26px; }
#about .ec-tier--leadership .ec-portrait-frame { flex-basis: 144px; width: 144px; height: 144px; padding: 9px; }
#about .ec-tier--leadership .ec-portrait__initials { font-size: 42px; }
#about .ec-tier--leadership .ec-card h4 { font-size: 25px; }
#about .ec-tier--developer .ec-card { background: linear-gradient(125deg, rgba(18, 38, 43, .72), rgba(11, 18, 32, .95)); }
#about .ec-tier--developer .ec-card__label::before { content: "</>"; display: inline-block; margin-right: 7px; color: #a8f7de; font-size: 10px; letter-spacing: -.08em; }
#about .ec-footer { display: flex; justify-content: space-between; align-items: center; gap: 28px; padding: 28px 0 34px; border-top: 1px solid var(--ec-line); }
#about .ec-footer__statement { font-size: 13px; font-weight: 500; letter-spacing: -.02em; color: #cad6ed; }
#about .ec-footer__statement span { color: var(--ec-mint); }
#about .ec-footer__meta { color: #8092b3; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 9px; line-height: 1.8; letter-spacing: .06em; text-align: right; text-transform: uppercase; }

/* Progressive enhancement: content remains visible when JavaScript is unavailable. */
#about .ec-reveal { opacity: 1; transform: none; }
#about.ec-ready .ec-reveal.ec-waiting { opacity: 0; transform: translateY(24px); }
#about.ec-ready .ec-reveal { transition: opacity .8s cubic-bezier(.2, .7, .2, 1), transform .8s cubic-bezier(.2, .7, .2, 1); transition-delay: var(--ec-delay, 0ms); }
#about.ec-paused *, #about.ec-paused *::before, #about.ec-paused *::after,
#about.ec-offscreen *, #about.ec-offscreen *::before, #about.ec-offscreen *::after,
#about.ec-hidden *, #about.ec-hidden *::before, #about.ec-hidden *::after { animation-play-state: paused !important; }
#about.ec-paused .ec-card, #about.ec-paused .ec-portrait__image { transform: none !important; }
#about.ec-paused .ec-reveal { transition: none !important; }
@keyframes ec-spin { to { transform: rotate(360deg); } }
@keyframes ec-breathe { to { transform: translate3d(-60px, 90px, 0) scale(1.15); } }
@keyframes ec-spectrum { to { background-position: 100% 50%; } }
@keyframes ec-orb-glow { to { transform: scale(1.15); opacity: .6; } }
@keyframes ec-tag-float { to { transform: translateY(-9px); } }
@keyframes ec-travel { 0% { left: 0; opacity: 0; } 10%, 85% { opacity: .8; } 100% { left: calc(100% - 140px); opacity: 0; } }
@media (min-width: 1450px) { #about .ec-hero { padding-top: 116px; padding-bottom: 108px; } }
@media (max-width: 1050px) {
    #about .ec-shell { width: calc(100% - 64px); }
    #about .ec-hero { gap: 12px; padding: 85px 0 80px; }
    #about .ec-card, #about .ec-tier--leadership .ec-card { gap: 20px; padding: 32px 22px; }
    #about .ec-portrait-frame { flex-basis: 92px; width: 92px; height: 92px; }
    #about .ec-tier--leadership .ec-portrait-frame { flex-basis: 110px; width: 110px; height: 110px; }
    #about .ec-card h4 { font-size: 20px; }
    #about .ec-tier--leadership .ec-card h4 { font-size: 22px; }
}
@media (max-width: 760px) {
    #about .ec-shell { width: calc(100% - 40px); }
    #about .ec-topline { padding-top: 24px; }
    #about .ec-brand__text { font-size: 10px; letter-spacing: .055em; }
    #about .ec-brand__mark { width: 30px; height: 30px; }
    #about .ec-motion { padding: 9px 12px; font-size: 10px; }
    #about .ec-hero { grid-template-columns: 1fr; padding: 64px 0 48px; gap: 24px; }
    #about .ec-hero h2 { font-size: clamp(41px, 8.4vw, 64px); letter-spacing: -.058em; }
    #about .ec-eyebrow { font-size: 8px; letter-spacing: .13em; max-width: 340px; }
    #about .ec-hero__description { font-size: 14px; margin-top: 22px; }
    #about .ec-hero__foot { margin-top: 24px; gap: 20px; }
    #about .ec-orbital { width: min(72%, 320px); justify-self: center; margin: 8px 0 24px; }
    #about .ec-orbital__center span { font-size: 28px; }
    #about .ec-orbital__center small { font-size: 6px; }
    #about .ec-orbit-tag { padding: 7px 9px; font-size: 8px; }
    #about .ec-team { padding: 40px 0 48px; }
    #about .ec-tier { margin-top: 38px; }
    #about .ec-tier__header { gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    #about .ec-tier__header h3 { font-size: 17px; }
    #about .ec-tier__subtitle { display: none; }
    #about .ec-cards { grid-template-columns: 1fr; gap: 16px; }
    #about .ec-card, #about .ec-tier--leadership .ec-card { min-height: 200px; padding: 28px 24px; gap: 24px; }
    #about .ec-portrait-frame, #about .ec-tier--leadership .ec-portrait-frame { flex-basis: 110px; width: 110px; height: 110px; padding: 7px; }
    #about .ec-card h4, #about .ec-tier--leadership .ec-card h4 { font-size: 23px; }
    #about .ec-portrait__initials, #about .ec-tier--leadership .ec-portrait__initials { font-size: 34px; }
    #about .ec-footer { align-items: flex-start; flex-direction: column; gap: 12px; }
    #about .ec-footer__meta { text-align: left; font-size: 8px; }
}
@media (max-width: 380px) {
    #about .ec-shell { width: calc(100% - 32px); }
    #about .ec-hero h2 { font-size: 36px; }
    #about .ec-brand { gap: 8px; }
    #about .ec-brand__text { max-width: 100px; line-height: 1.5; }
    #about .ec-card, #about .ec-tier--leadership .ec-card { padding: 28px 18px; gap: 18px; }
    #about .ec-portrait-frame, #about .ec-tier--leadership .ec-portrait-frame { flex-basis: 84px; width: 84px; height: 84px; padding: 6px; }
    #about .ec-card h4, #about .ec-tier--leadership .ec-card h4 { font-size: 21px; }
    #about .ec-card__label { font-size: 9px; }
    #about .ec-card__role { font-size: 12px; }
}
@media (prefers-reduced-motion: reduce) {
    #about.ec-about *, #about.ec-about *::before, #about.ec-about *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; }
    #about.ec-about .ec-reveal, #about.ec-about .ec-card, #about.ec-about .ec-portrait__image { opacity: 1 !important; transform: none !important; }
}
@media (forced-colors: active) {
    #about .ec-sky, #about .ec-grid, #about .ec-noise, #about .ec-orbital { display: none; }
    #about .ec-hero { grid-template-columns: 1fr; }
    #about .ec-title-accent { background: none; -webkit-text-fill-color: CanvasText; }
    #about .ec-card, #about .ec-motion { border: 1px solid CanvasText; }
}
@media print {
    #about.ec-about { color: #111; background: #fff; }
    #about .ec-sky, #about .ec-grid, #about .ec-noise, #about .ec-orbital, #about .ec-motion, #about .ec-hero__foot, #about .ec-divider { display: none !important; }
    #about .ec-shell { width: 100%; }
    #about .ec-hero { display: block; padding: 25px 0; }
    #about .ec-hero h2 { font-size: 32px; }
    #about .ec-title-accent { background: none; -webkit-text-fill-color: #111; }
    #about .ec-card, #about .ec-tier--leadership .ec-card { background: #fff; min-height: 170px; break-inside: avoid; border-color: #ccc; box-shadow: none; }
    #about.ec-about h3, #about.ec-about h4, #about.ec-about p, #about .ec-eyebrow, #about .ec-card__label, #about .ec-footer__statement { color: #333; }
    #about.ec-about .ec-reveal { opacity: 1 !important; transform: none !important; }
}
</style>

<section id="about" class="ec-about" aria-labelledby="ec-about-title">
    <div class="ec-sky" aria-hidden="true"><canvas class="ec-stars"></canvas></div>
    <div class="ec-grid" aria-hidden="true"></div>
    <div class="ec-noise" aria-hidden="true"></div>
    <div class="ec-shell">
        <div class="ec-topline">
            <div class="ec-brand">
                <span class="ec-brand__mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 7h14M5 12h10M5 17h14" stroke="#b5ceff" stroke-width="1.6" stroke-linecap="round"/><path d="m17 10 3 2-3 2" stroke="#85efd2" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="ec-brand__text">SRM Event Connect</span>
            </div>
            <button class="ec-motion" type="button" aria-pressed="false" aria-label="Pause decorative animations">
                <span class="ec-motion__icon" aria-hidden="true"></span><span class="ec-motion__text">Pause motion</span>
            </button>
        </div>

        <header class="ec-hero">
            <div class="ec-hero__copy">
                <p class="ec-eyebrow ec-reveal"><span class="ec-live-dot" aria-hidden="true"></span>Sustainable leadership &amp; administrative innovation</p>
                <h2 id="ec-about-title" class="ec-reveal" style="--ec-delay: 90ms">
                    <span class="ec-title-line">Innovate Events.</span>
                    <span class="ec-title-line ec-title-accent">Simplify Administration.</span>
                    <span class="ec-title-line">Lead Sustainably.</span>
                </h2>
                <p class="ec-hero__description ec-reveal" style="--ec-delay: 170ms">Empowering seamless event management through technology. Meet the people who make it possible.</p>
                <div class="ec-hero__foot ec-reveal" style="--ec-delay: 230ms">
                    <a class="ec-explore" href="#ec-leadership">Meet the team <span class="ec-explore__arrow" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 4v15m-6-6 6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>
                    <span class="ec-team-count"><strong>06 PEOPLE</strong><br>ONE SHARED VISION</span>
                </div>
            </div>
            <div class="ec-orbital ec-reveal" style="--ec-delay: 180ms" aria-hidden="true">
                <div class="ec-orbital__glow"></div>
                <div class="ec-orbital__axis"></div><div class="ec-orbital__axis ec-orbital__axis--two"></div>
                <div class="ec-orbit ec-orbit--one"></div><div class="ec-orbit ec-orbit--two"></div><div class="ec-orbit ec-orbit--three"></div>
                <div class="ec-orbital__center"><span>ec.</span><small>Event Connect</small></div>
                <span class="ec-orbit-tag ec-orbit-tag--one">Leadership</span>
                <span class="ec-orbit-tag ec-orbit-tag--two">Innovation</span>
                <span class="ec-orbit-tag ec-orbit-tag--three">Mentorship</span>
                <span class="ec-orbital__caption">Ideas. People. Impact.</span>
            </div>
        </header>

        <div class="ec-divider" aria-hidden="true"></div>
        <div class="ec-team">
            <?php $ec_serial = 0; foreach ($ec_tiers as $ec_tier): ?>
                <section id="ec-<?= $ec_escape($ec_tier['key']) ?>" class="ec-tier ec-tier--<?= $ec_escape($ec_tier['key']) ?>" aria-labelledby="ec-heading-<?= $ec_escape($ec_tier['key']) ?>">
                    <div class="ec-tier__header ec-reveal">
                        <span class="ec-tier__index" aria-hidden="true"><?= $ec_escape($ec_tier['index']) ?></span>
                        <h3 id="ec-heading-<?= $ec_escape($ec_tier['key']) ?>"><?= $ec_escape($ec_tier['title']) ?></h3>
                        <span class="ec-tier__rule" aria-hidden="true"></span>
                        <p class="ec-tier__subtitle"><?= $ec_escape($ec_tier['subtitle']) ?></p>
                    </div>
                    <div class="ec-cards">
                        <?php $ec_position = 0; foreach ($about_team_groups as $ec_group): ?>
                            <?php if ($ec_group['tier'] !== $ec_tier['key']) continue; ?>
                            <?php foreach ($ec_group['members'] as $ec_member): $ec_serial++; ?>
                                <div class="ec-card-wrap ec-reveal" style="--ec-delay: <?= (int) ($ec_position++ * 90) ?>ms">
                                    <article class="ec-card" aria-label="<?= $ec_escape($ec_member['name']) ?>">
                                        <span class="ec-card__serial" aria-hidden="true"><?= str_pad((string) $ec_serial, 2, '0', STR_PAD_LEFT) ?></span>
                                        <div class="ec-portrait-frame"><?php $ec_portrait($ec_member); ?></div>
                                        <div class="ec-card__content">
                                            <p class="ec-card__label"><?= $ec_escape($ec_group['label']) ?></p>
                                            <h4><?= $ec_escape($ec_member['name']) ?></h4>
                                            <p class="ec-card__role"><?= $ec_escape($ec_member['role']) ?></p>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
        <footer class="ec-footer ec-reveal">
            <p class="ec-footer__statement">Shared vision. <span>Connected by purpose.</span></p>
            <p class="ec-footer__meta">Event Excellence Team<br>SRM Event Connect</p>
        </footer>
    </div>
</section>

<script>
(function () {
    'use strict';
    // previousElementSibling selects this partial, not an unrelated #about.
    var script = document.currentScript;
    var root = script && script.previousElementSibling;
    if (!root || !root.matches('.ec-about') || root.dataset.ecInitialized) return;
    root.dataset.ecInitialized = 'true';

    var motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    var pointerQuery = window.matchMedia('(hover: hover) and (pointer: fine)');
    var reduced = motionQuery.matches;
    var userPaused = false;
    var inView = true;
    var button = root.querySelector('.ec-motion');
    var buttonText = root.querySelector('.ec-motion__text');
    var reveals = Array.prototype.slice.call(root.querySelectorAll('.ec-reveal'));
    var canvas = root.querySelector('.ec-stars');
    var ctx = null;
    try { ctx = canvas && canvas.getContext('2d'); } catch (error) { /* CSS background remains available. */ }
    var frame = 0, lastTime = 0, logicalWidth = 0, logicalHeight = 0, particles = [];
    var pointerX = -1000, pointerY = -1000, pointerInside = false;
    var activeCard = null, pendingPointer = null, pointerFrame = 0;
    var revealObserver = null, sectionObserver = null, resizeObserver = null;
    var cleanups = [];

    function listen(target, type, handler, options) {
        target.addEventListener(type, handler, options || false);
        cleanups.push(function () { target.removeEventListener(type, handler, options || false); });
    }
    function stopped() { return reduced || userPaused; }
    function canAnimate() { return !stopped() && inView && !document.hidden && root.isConnected; }
    function revealAll() {
        reveals.forEach(function (element) { element.classList.remove('ec-waiting'); });
        if (revealObserver) revealObserver.disconnect();
    }
    function resetCard() {
        if (!activeCard) return;
        activeCard.style.removeProperty('--ec-rx'); activeCard.style.removeProperty('--ec-ry');
        activeCard = null;
    }
    function syncMotion() {
        root.classList.toggle('ec-paused', stopped());
        root.classList.toggle('ec-hidden', document.hidden);
        root.classList.toggle('ec-offscreen', !inView);
        button.setAttribute('aria-pressed', String(stopped()));
        button.setAttribute('aria-label', reduced ? 'Reduced motion follows your device setting' : (userPaused ? 'Resume decorative animations' : 'Pause decorative animations'));
        buttonText.textContent = reduced ? 'Reduced motion' : (userPaused ? 'Resume motion' : 'Pause motion');
        button.disabled = reduced;
        if (stopped()) { revealAll(); resetCard(); }
        if (frame) cancelAnimationFrame(frame);
        frame = 0; lastTime = 0;
        if (ctx) {
            if (canAnimate()) frame = requestAnimationFrame(draw);
            else drawStatic();
        }
    }
    function resize() {
        if (!ctx) return;
        var width = root.clientWidth, height = root.clientHeight;
        if (width === logicalWidth && height === logicalHeight) return;
        logicalWidth = width; logicalHeight = height;
        // Bound total bitmap memory even when text zoom makes the section very tall.
        var dpr = Math.min(window.devicePixelRatio || 1, 1.5, Math.sqrt(4500000 / Math.max(1, width * height)));
        canvas.width = Math.max(1, Math.round(width * dpr));
        canvas.height = Math.max(1, Math.round(height * dpr));
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        var count = Math.min(65, Math.max(24, Math.round(width * height / 34000)));
        particles = Array.from({ length: count }, function () {
            return { x: Math.random() * width, y: Math.random() * height, vx: (Math.random() - .5) * 5, vy: -3 - Math.random() * 5, r: .65 + Math.random() * .85, a: .2 + Math.random() * .45 };
        });
        drawStatic();
    }
    function paint(delta) {
        ctx.clearRect(0, 0, logicalWidth, logicalHeight);
        particles.forEach(function (point) {
            if (delta) {
                point.x = (point.x + point.vx * delta + logicalWidth) % logicalWidth;
                point.y = (point.y + point.vy * delta + logicalHeight) % logicalHeight;
            }
            ctx.beginPath(); ctx.arc(point.x, point.y, point.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(157,190,255,' + point.a + ')'; ctx.fill();
            if (pointerInside && !stopped()) {
                var dx = point.x - pointerX, dy = point.y - pointerY;
                var distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < 150) {
                    ctx.beginPath(); ctx.moveTo(point.x, point.y); ctx.lineTo(pointerX, pointerY);
                    ctx.strokeStyle = 'rgba(143,194,255,' + (.16 * (1 - distance / 150)) + ')';
                    ctx.lineWidth = .6; ctx.stroke();
                }
            }
        });
    }
    function drawStatic() { if (ctx && logicalWidth) paint(0); }
    function draw(time) {
        frame = 0;
        if (!root.isConnected) { destroy(); return; }
        if (!canAnimate()) return;
        // 30fps ambient field, time-based motion; card interaction has its own rAF.
        if (!lastTime || time - lastTime >= 32) {
            paint(lastTime ? Math.min((time - lastTime) / 1000, .08) : 0);
            lastTime = time;
        }
        frame = requestAnimationFrame(draw);
    }
    function updatePointer() {
        pointerFrame = 0;
        if (!pendingPointer || !canAnimate() || !pointerQuery.matches) { resetCard(); return; }
        var event = pendingPointer;
        var bounds = root.getBoundingClientRect();
        pointerX = event.clientX - bounds.left; pointerY = event.clientY - bounds.top;
        var card = event.target.closest('.ec-card');
        if (card !== activeCard) { resetCard(); activeCard = card; }
        if (!card) return;
        var rect = card.getBoundingClientRect();
        var x = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
        var y = Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height));
        card.style.setProperty('--ec-x', (x * 100) + '%'); card.style.setProperty('--ec-y', (y * 100) + '%');
        card.style.setProperty('--ec-rx', ((.5 - y) * 5) + 'deg'); card.style.setProperty('--ec-ry', ((x - .5) * 5) + 'deg');
    }
    function destroy() {
        if (frame) cancelAnimationFrame(frame);
        if (pointerFrame) cancelAnimationFrame(pointerFrame);
        if (revealObserver) revealObserver.disconnect();
        if (sectionObserver) sectionObserver.disconnect();
        if (resizeObserver) resizeObserver.disconnect();
        cleanups.forEach(function (cleanup) { cleanup(); });
        cleanups = [];
    }

    // Enable reveals only after a working observer has been constructed.
    if (!reduced && typeof IntersectionObserver !== 'undefined') {
        revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { entry.target.classList.remove('ec-waiting'); revealObserver.unobserve(entry.target); }
            });
        }, { threshold: .07, rootMargin: '0px 0px -18px 0px' });
        reveals.forEach(function (element) { element.classList.add('ec-waiting'); revealObserver.observe(element); });
    }
    root.classList.add('ec-ready');
    if (typeof IntersectionObserver !== 'undefined') {
        sectionObserver = new IntersectionObserver(function (entries) { inView = entries[0].isIntersecting; syncMotion(); }, { threshold: 0 });
        sectionObserver.observe(root);
    }
    listen(button, 'click', function () { userPaused = !userPaused; syncMotion(); });
    listen(document, 'visibilitychange', syncMotion);
    var onMotionChange = function () { reduced = motionQuery.matches; syncMotion(); };
    if (motionQuery.addEventListener) listen(motionQuery, 'change', onMotionChange);
    else { motionQuery.addListener(onMotionChange); cleanups.push(function () { motionQuery.removeListener(onMotionChange); }); }
    listen(root, 'pointermove', function (event) {
        if (!canAnimate() || !pointerQuery.matches) return;
        pointerInside = true; pendingPointer = event;
        if (!pointerFrame) pointerFrame = requestAnimationFrame(updatePointer);
    }, { passive: true });
    listen(root, 'pointerleave', function () { pointerInside = false; pendingPointer = null; resetCard(); });
    listen(window, 'scroll', function () { pointerInside = false; }, { passive: true });
    // Native anchor still works without JS; only enhancement is smooth scrolling.
    listen(root.querySelector('.ec-explore'), 'click', function (event) {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        var target = root.querySelector('#ec-leadership');
        if (target && !stopped()) { event.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
    root.querySelectorAll('.ec-portrait__image').forEach(function (image) {
        function fallback() { image.hidden = true; image.style.display = 'none'; }
        listen(image, 'error', fallback);
        if (image.complete && !image.naturalWidth) fallback();
    });
    if (typeof ResizeObserver !== 'undefined') { resizeObserver = new ResizeObserver(resize); resizeObserver.observe(root); }
    else listen(window, 'resize', resize, { passive: true });
    resize(); syncMotion();
})();
</script>
