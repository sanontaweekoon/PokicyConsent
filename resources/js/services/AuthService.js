import BackendService from './BackendService';
import {
    authToken
} from "./authToken";

export const AuthService = {
    beginMicrosoftLogin() {
        window.location.href = '/login/microsoft';
    },
    completeLoginFromCallback() {
        const params = new URLSearchParams(window.location.search);
        const token = params.get('token');

        if (!token) {
            return false;
        }

        authToken.set(token);

        const url = new URL(window.location.href);
        url.searchParams.delete('token');
        history.replaceState({}, '', url.pathname);

        return true;
    },
    isAuthenticated() {
        return !!authToken.get();
    },
    async logout() {
        try {
            await BackendService.post('/api/logout');
        } catch {}
        authToken.clear();
        window.location.href = '/login';
    },
    logoutWithBeacon() {
        try {
            const token = authToken.get();
            const data = new FormData();
            data.append('token', token);
            navigator.sendBeacon('/api/logout-beacon', data);
        } catch {}
        authToken.clear();
    },
};
