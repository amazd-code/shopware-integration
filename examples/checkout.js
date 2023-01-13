import fetch from "node-fetch";

/**
 * Creates a checkout URL for a given product id.
 * Example of usage.
 * 1. Modify SHOP_URL, ACCESS_KEY and PRODUCT_IDS below to your shop's values.
 * 2. `node checkout.js`
 * 3. Open the URL logged in the console.
 */

const SHOP_URL = "http://localhost";

// https://shopware.stoplight.io/docs/store-api/8e1d78252fa6f-authentication-and-authorisation
const ACCESS_KEY = "SWSCNEV6QZA2UZRTOFH4MMJJSG";

// Can be taken from admin panel product page URL. http://localhost/admin#/sw/product/detail/c7bca22753c84d08b6178a50052b4146/base -> c7bca22753c84d08b6178a50052b4146
const PRODUCT_IDS = [
  "c7bca22753c84d08b6178a50052b4146",
  "3ac014f329884b57a2cce5a29f34779c",
];

(async () => {
  const headers = {
    accept: "application/json",
    "content-type": "application/json",
    "sw-access-key": ACCESS_KEY,
  };

  // Get context token
  const response = await fetch(`${SHOP_URL}/store-api/context`, {
    headers,
  }).then((res) => res.json());

  const contextToken = response.token;

  // Add products to cart
  await fetch(`${SHOP_URL}/store-api/checkout/cart/line-item`, {
    method: "POST",
    headers: {
      ...headers,
      "sw-context-token": contextToken,
    },
    body: JSON.stringify({
      items: PRODUCT_IDS.map((id) => ({
        type: "product",
        referencedId: id,
        quantity: 1,
      })),
    }),
  });

  console.log(
    `${SHOP_URL}/amazd-integration/checkout/${ACCESS_KEY}/${contextToken}`
  );
})();
