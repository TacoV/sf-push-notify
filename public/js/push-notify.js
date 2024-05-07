async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        console.error("Service Worker isn't supported on this browser.");
        return false;
    }
    try {
        const registration = await navigator.serviceWorker
            .register('/js/service-worker.js');
        console.log('Service worker successfully registered.');
        return registration;
    } catch (err) {
        console.error('Unable to register service worker.', err);
        return false;
    }
}

async function subscribeUserToPush(vapidPublicKey) {
    if (!('PushManager' in window)) {
        console.error("Push isn't supported on this browser.");
        return;
    }

    const permission = await askPermission();
    if ( !permission) {
        console.error('We have no permission to send Notifications - not subscribing');
        return;
    }

    const registration = await registerServiceWorker();
    if ( registration === false ) {
        console.error('We have no registered service worker - not subscribing');
        return;
    }
    
    const subscribeOptions = {
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(
            vapidPublicKey
        ),
    };
    const subscription = await registration.pushManager.subscribe(subscribeOptions);

    await sendSubscriptionToBackEnd(subscription);

    location.reload();
}

async function sendSubscriptionToBackEnd(subscription) {
    const response = await fetch('/subscription/api', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(subscription),
    });
    if (!response.ok) {
        throw new Error('Bad status code from server.');
    }
}

async function askPermission(){
    const permission = await new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    });
    return (permission === 'granted');
}

async function notifyUser(subId) {
    const response = await fetch(
        '/subscription/' + subId + '/notify',
        {
            method: 'POST'
        }
    );
    if (response.ok) {
        console.log('Send a push notification successfully');
    }
    else {
        response.json().then(function (data) {
            throw new Error('Failed to push notification:' + data.detail);
        });
    }
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

export { subscribeUserToPush, notifyUser };