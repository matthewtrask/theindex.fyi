import { create, get } from '@github/webauthn-json';

async function fetchJson(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        ...options,
    });
    if (!res.ok) {
        const body = await res.text();
        let msg;
        try {
            msg = JSON.parse(body).message ?? body;
        } catch {
            msg = body;
        }
        throw new Error(msg || `HTTP ${res.status}`);
    }
    const text = await res.text();
    return text ? JSON.parse(text) : null;
}

window.registerPasskey = async function (name) {
    const { options } = await fetchJson('/user/passkeys/options');
    const credential = await create({ publicKey: options });
    await fetchJson('/user/passkeys', {
        method: 'POST',
        body: JSON.stringify({ name, credential }),
    });
};

window.loginWithPasskey = async function () {
    const { options } = await fetchJson('/passkeys/login/options');
    const credential = await get({ publicKey: options });
    const data = await fetchJson('/passkeys/login', {
        method: 'POST',
        body: JSON.stringify({ credential }),
    });
    window.location.href = data?.redirect ?? '/admin/stats';
};
