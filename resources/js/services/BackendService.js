import axios from "axios";
import { authToken } from './authToken';

const BackendService = axios.create({
    baseURL: process.env.VUE_APP_URL_API || '/api',
    headers: {
        "Content-type": "application/json",
        "Accept": "application/json",
    }
});

BackendService.interceptors.request.use((config)=> {
    try{
        const token = localStorage.getItem('auth_token');
        if (token){
            config.headers.Authorization = "Bearer "+token
        }else{
            delete config.headers.Authorization;
        }
    } catch (e){
        console.warn('read auth_token failed', e);
        delete config.headers.Authorization;
    }
    return config;
});

BackendService.interceptors.response.use(
    (res)=>res,
    (err)=> {
        const status = err?.response?.status;
        if(status === 401) {
            authToken.clear();
            window.location.href = '/login';
        }
        return Promise.reject(err);
    }
);

export default BackendService;