export class HttpRequest {
    static post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
            .then(res => {
                // Vérification du type de contenu avant d'essayer de parser en JSON
                const contentType = res.headers.get('Content-Type');
                if (contentType && contentType.includes('application/json')) {
                    return res.json();
                } else {
                    throw new Error("La réponse n'est pas au format JSON");
                }
            });
    }

    static get(url) {
        return fetch(url)
            .then(res => res.json());
    }
}