openssl ecparam -genkey -name prime256v1 -out vapid_keys.pem

: > .vapid_keys
echo 'VAPID_PUBLIC_KEY='`openssl ec -in vapid_keys.pem -pubout -outform DER|tail -c 65|base64|tr -d '=\n' |tr '/+' '_-'` >> .vapid_keys
echo 'VAPID_PRIVATE_KEY='`openssl ec -in vapid_keys.pem -outform DER|tail -c +8|head -c 32|base64|tr -d '=' |tr '/+' '_-'` >> .vapid_keys

rm vapid_keys.pem

echo 'Keys config generated in .vapid_keys. Copy this to your .env.local'