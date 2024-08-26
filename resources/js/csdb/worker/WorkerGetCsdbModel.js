/**
 * DEPRECATED. Tidak dipakai kalau proses cuma fetch doang
 */
onmessage = async function(event) {
  let response = await fetch(event.data.route.url);
  if (response.ok){
    postMessage(await response.json());
  }
}