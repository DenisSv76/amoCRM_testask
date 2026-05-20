// track.js — вставлять на любую страницу
(function(){
    const endpoint = (function(){
        // извлекаем базовый URL из адреса самого скрипта
        const s = document.currentScript.src;
        return s.replace(/\/track\.js(\?.*)?$/, '/api/track');
    })();

    function getDeviceType(ua) {
        ua = (ua || '').toLowerCase();
        if (/mobile|iphone|android/.test(ua)) return 'mobile';
        if (/tablet|ipad/.test(ua)) return 'tablet';
        return 'desktop';
    }

    async function fetchIpInfo() {
        try {
            const res = await fetch('https://ipapi.co/json/');
            if (!res.ok) return null;
            return await res.json();
        } catch (e) {
            return null;
        }
    }

    async function sendVisit() {
        const ua = navigator.userAgent || '';
        const device = getDeviceType(ua);
        const ipInfo = await fetchIpInfo();
        const payload = {
            url: location.href,
            referrer: document.referrer || null,
            ua: ua,
            device: device,
            screen_w: screen.width || null,
            screen_h: screen.height || null,
            ts: new Date().toISOString(),
            ip: ipInfo ? ipInfo.ip : null,
            city: ipInfo ? (ipInfo.city || ipInfo.region || null) : null,
            country: ipInfo ? ipInfo.country_name : null
        };

        const body = JSON.stringify(payload);
        if (navigator.sendBeacon) {
            navigator.sendBeacon(endpoint, body);
        } else {
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body,
                keepalive: true
            }).catch(()=>{});
        }
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        sendVisit();
    } else {
        window.addEventListener('DOMContentLoaded', sendVisit, { once: true });
    }
})();
