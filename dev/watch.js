const fs = require("fs");
const { resolve, dirname } = require("path");
const { spawnSync } = require("child_process");

/**
 * For plugin development with Shopware running in a local docker container.
 * Watches changes of files and copies them into the container.
 * For example, to start: `node dev/watch df25069b216c` - where df25069b216c is the container id.
 */
const CONTAINER_ID = process.argv[2];

// Directory name of plugin inside the container. Depends on installation .zip.
const pluginDirectory = `ShopwareAmazdIntegration`;

let throttled = {};

fs.watch(
  resolve(__dirname, "../src"),
  {
    recursive: true,
    interval: 1000,
  },
  (eventType, filename) => {
    const filePath = resolve(__dirname, "../src", filename);
    if (fs.lstatSync(filePath).isDirectory()) return;

    if (throttled[filePath]) return;
    throttled[filePath] = true;
    setTimeout(() => {
      throttled[filePath] = false;
    }, 300);

    console.log(`Modified ${filename}`);

    const relativePath = dirname(filename).replace(/\\/g, "/").replace(".", "");
    const command = `docker cp "${filePath}" ${CONTAINER_ID}:/var/www/html/custom/plugins/${pluginDirectory}/src/${relativePath}`;

    spawnSync(command, {
      shell: true,
      stdio: "inherit",
      cwd: resolve(__dirname, ".."),
    });
  }
);
