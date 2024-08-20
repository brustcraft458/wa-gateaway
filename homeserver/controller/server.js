import { urlGateaway, jwtToken } from "../environment/env.js";


async function postFetch(url, data) {
    let response = await fetch(`${urlGateaway}${url}`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Authorization": jwtToken },
        body: JSON.stringify(data),
    });

    let text = await response.text()
    return JSON.parse(text)
}

async function putFetch(url, data) {
    let response = await fetch(`${urlGateaway}${url}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json", "Authorization": jwtToken },
        body: JSON.stringify(data),
    });

    let text = await response.text();
    return JSON.parse(text);
}


async function getFetch(url) {
    let response = await fetch(`${urlGateaway}${url}`, {
        method: "GET",
        headers: { "Content-Type": "application/json", "Authorization": jwtToken }
    });
    
    let text = await response.text()
    return JSON.parse(text)
}

export {putFetch, getFetch}