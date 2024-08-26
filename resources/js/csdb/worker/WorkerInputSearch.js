/**
 * DEPRECATED
 * Tidak dipakai lagi kalau cuma fetch saja
 */
onmessage = async function(event) {
  let response = await fetch(event.data.route.url);
  // this.postMessage({response: response});
  if (response.ok){
    postMessage(await response.json());
  } else postMessage({error:true});
}