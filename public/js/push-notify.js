function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        console.error("Service Worker isn't supported on this browser.");
        return;
    }
    return navigator.serviceWorker
        .register('/js/service-worker.js')
        .then(function (registration) {
            console.log('Service worker successfully registered.');
            return registration;
        })
        .catch(function (err) {
            console.error('Unable to register service worker.', err);
        });
}

async function subscribeUserToPush(vapidPublicKey) {
    const subscribeOptions = {
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(
            vapidPublicKey
        ),
    };
    
    const registration = await registerServiceWorker();
    const subscription = await registration.pushManager.subscribe(subscribeOptions);
    setTimeout( () => location.reload(), 500);
    return sendSubscriptionToBackEnd(subscription);
}

function sendSubscriptionToBackEnd(subscription) {
    fetch('/subscription/api', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(subscription),
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Bad status code from server.');
        }
    });
}

function askPermission() {
    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    }).then(function (permissionResult) {
        if (permissionResult !== 'granted') {
            throw new Error("We weren't granted permission.");
        }
    });
}

function notify(subId) {
    fetch('/subscription/'+subId+'/notify', {
        method: 'POST'
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

if (!('PushManager' in window)) {
    console.error("Push isn't supported on this browser.");
}