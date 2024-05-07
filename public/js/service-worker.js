self.addEventListener('push', function(event) {
  const data = event.data.json();
  const image = 'https://cdn.glitch.com/614286c9-b4fc-4303-a6a9-a4cef0601b74%2Flogo.png?v=1605150951230';
  const options = {
    body: data.body,
    icon: image,
  }

  const promiseChain = self.registration.showNotification(data.message, options);
  event.waitUntil(promiseChain);
});
