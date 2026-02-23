<?php
// generate_keys.php
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];
$res = openssl_pkey_new($config);
if (!$res) {
    echo "Methods failed. Error: ".openssl_error_string();
    exit(1);
}
openssl_pkey_export($res, $privKey);
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey["key"];

file_put_contents('config/jwt/private.pem', $privKey);
file_put_contents('config/jwt/public.pem', $pubKey);
echo "Keys generated successfully.\n";
