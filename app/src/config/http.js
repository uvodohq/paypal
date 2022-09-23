import axios from 'axios'

const url = new URL(window.location.href);

// const token = url.searchParams.get("token");

// const API_URL_PARAM = url.searchParams.get("api_url");
// const API_URL = validURL(API_URL_PARAM) ? API_URL_PARAM : '/plugins/'

const token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiMSJ9.kAimCHtTlGqxJp9O-z7a069IyKAk8yT-o0rSsUOU3Wk";
const API_URL = "http://uvodo.test/admin/api";

console.log(token);
console.log(API_URL);

const http = axios.create({
    baseURL: API_URL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
})

function authRequestInterceptor(config) {
    if (config.headers && config.url) {
        const isAbsoluteUrl = config.url.startsWith('http')
        const isCrossOrigin = !config.url.startsWith(API_URL)

        // Do not send auth token for cross-origin requests
        if (isAbsoluteUrl && isCrossOrigin) return config

        if (token) {
            config.headers.authorization = `Bearer ${token}`
        }
    }

    return config
}

function validURL(str) {
    let pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
        '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
    return !!pattern.test(str);
}

http.interceptors.request.use(authRequestInterceptor)
http.interceptors.response.use((response) => response.data)

export default http;
